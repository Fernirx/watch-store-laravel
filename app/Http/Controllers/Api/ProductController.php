<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;

        // Only admin can create, update, delete
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('role:admin')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        try {
            $query = Product::with(['category', 'brand'])->where('is_active', true);

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by brand
            if ($request->has('brand_id')) {
                $query->where('brand_id', $request->brand_id);
            }

            // Filter by price range
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Filter by gender
            if ($request->has('gender')) {
                $query->where('gender', $request->gender);
            }

            // Filter by featured
            if ($request->has('is_featured')) {
                $query->where('is_featured', $request->is_featured);
            }

            // Search by name
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate
            $perPage = $request->get('per_page', 12);
            $products = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'required|exists:brands,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'sku' => 'required|string|unique:products,sku',
                'stock_quantity' => 'required|integer|min:0',
                'images' => 'nullable|array',
                'images.*' => 'image|max:2048',
                'specifications' => 'nullable|array',
                'case_material' => 'nullable|string|max:100',
                'strap_material' => 'nullable|string|max:100',
                'movement_type' => 'nullable|string|max:100',
                'water_resistance' => 'nullable|string|max:100',
                'dial_color' => 'nullable|string|max:100',
                'case_diameter' => 'nullable|string|max:100',
                'gender' => 'nullable|in:male,female,unisex',
                'is_featured' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Upload images to Cloudinary if provided
            if ($request->hasFile('images')) {
                $uploadedImages = $this->cloudinaryService->uploadMultiple($request->file('images'), 'watch-store/products');
                $validated['images'] = $uploadedImages;
            }

            $product = Product::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'brand']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $product = Product::with(['category', 'brand'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $product,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'category_id' => 'exists:categories,id',
                'brand_id' => 'exists:brands,id',
                'name' => 'string|max:255',
                'description' => 'nullable|string',
                'price' => 'numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'sku' => 'string|unique:products,sku,' . $id,
                'stock_quantity' => 'integer|min:0',
                'images' => 'nullable|array',
                'images.*' => 'image|max:2048',
                'specifications' => 'nullable|array',
                'case_material' => 'nullable|string|max:100',
                'strap_material' => 'nullable|string|max:100',
                'movement_type' => 'nullable|string|max:100',
                'water_resistance' => 'nullable|string|max:100',
                'dial_color' => 'nullable|string|max:100',
                'case_diameter' => 'nullable|string|max:100',
                'gender' => 'nullable|in:male,female,unisex',
                'is_featured' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Upload new images if provided
            if ($request->hasFile('images')) {
                // Delete old images from Cloudinary
                if ($product->images && is_array($product->images)) {
                    foreach ($product->images as $image) {
                        if (isset($image['public_id'])) {
                            $this->cloudinaryService->delete($image['public_id']);
                        }
                    }
                }

                // Upload new images
                $uploadedImages = $this->cloudinaryService->uploadMultiple($request->file('images'), 'watch-store/products');
                $validated['images'] = $uploadedImages;
            }

            $product->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'brand']),
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
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Delete images from Cloudinary if exist
            if ($product->images && is_array($product->images)) {
                foreach ($product->images as $image) {
                    if (isset($image['public_id'])) {
                        $this->cloudinaryService->delete($image['public_id']);
                    }
                }
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
