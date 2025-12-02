<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Get user's orders (or all orders for admin)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $query = Order::with(['items.product', 'user']);

            // If not admin, only show user's orders
            if ($user->role !== 'ADMIN') {
                $query->where('user_id', $user->id);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new order from cart
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'shipping_address' => 'required|string',
                'shipping_phone' => 'required|string',
                'payment_method' => 'required|in:cod,bank_transfer,credit_card',
                'notes' => 'nullable|string',
            ]);

            $user = $request->user();

            // Get cart with items
            $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Check stock for all items
                foreach ($cart->items as $item) {
                    if ($item->product->stock_quantity < $item->quantity) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Insufficient stock for {$item->product->name}",
                        ], 400);
                    }
                }

                // Calculate totals
                $subtotal = $cart->items->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                $shipping_fee = 30000; // 30,000 VND shipping fee
                $total = $subtotal + $shipping_fee;

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'status' => 'PENDING',
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shipping_fee,
                    'total' => $total,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'PENDING',
                    'shipping_address' => $validated['shipping_address'],
                    'shipping_phone' => $validated['shipping_phone'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Create order items and update stock
                foreach ($cart->items as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'subtotal' => $cartItem->price * $cartItem->quantity,
                    ]);

                    // Decrease stock
                    $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
                }

                // Clear cart
                $cart->items()->delete();

                DB::commit();

                // Load order with items
                $order->load('items.product');

                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => $order,
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Request $request, string $id)
    {
        try {
            $user = $request->user();

            $query = Order::with(['items.product.category', 'items.product.brand', 'user']);

            // If not admin, only show user's own orders
            if ($user->role !== 'ADMIN') {
                $query->where('user_id', $user->id);
            }

            $order = $query->where('id', $id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $order,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }
    }

    /**
     * Update order status (Admin only)
     */
    public function updateStatus(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $order = Order::with('items.product')->findOrFail($id);

            $oldStatus = $order->status;
            $newStatus = strtoupper($validated['status']);

            // Update order status
            $order->status = $newStatus;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, string $id)
    {
        try {
            $user = $request->user();

            $order = Order::with('items.product')
                ->where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            // Only allow cancelling pending or paid orders
            if (!in_array($order->status, ['PENDING', 'PAID'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel order in current status',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Restore stock
                foreach ($order->items as $item) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }

                // Update order status
                $order->status = 'CANCELLED';
                $order->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully',
                    'data' => $order,
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
