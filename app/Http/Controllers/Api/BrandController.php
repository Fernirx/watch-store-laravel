<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;

        // Only admin can create, update, delete
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('role:admin')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        try {
            $brands = Brand::where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => $brands,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brands',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|max:2048',
                'country' => 'nullable|string|max:100',
                'website' => 'nullable|url|max:255',
                'is_active' => 'boolean',
            ]);

            // Upload logo to Cloudinary if provided
            if ($request->hasFile('logo')) {
                $uploadResult = $this->cloudinaryService->upload($request->file('logo'), 'watch-store/brands');
                $validated['logo_url'] = $uploadResult['url'];
                $validated['logo_public_id'] = $uploadResult['public_id'];
            }

            $brand = Brand::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully',
                'data' => $brand,
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
                'message' => 'Failed to create brand',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $brand = Brand::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $brand,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
            ], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $brand = Brand::findOrFail($id);

            $validated = $request->validate([
                'name' => 'string|max:100',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|max:2048',
                'country' => 'nullable|string|max:100',
                'website' => 'nullable|url|max:255',
                'is_active' => 'boolean',
            ]);

            // Upload new logo if provided
            if ($request->hasFile('logo')) {
                // Delete old logo from Cloudinary
                if ($brand->logo_public_id) {
                    $this->cloudinaryService->delete($brand->logo_public_id);
                }

                // Upload new logo
                $uploadResult = $this->cloudinaryService->upload($request->file('logo'), 'watch-store/brands');
                $validated['logo_url'] = $uploadResult['url'];
                $validated['logo_public_id'] = $uploadResult['public_id'];
            }

            $brand->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully',
                'data' => $brand,
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
                'message' => 'Failed to update brand',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $brand = Brand::findOrFail($id);

            // Delete logo from Cloudinary if exists
            if ($brand->logo_public_id) {
                $this->cloudinaryService->delete($brand->logo_public_id);
            }

            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete brand',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
