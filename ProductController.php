<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // GET /api/products
    public function index(Request $request)
    {
        try {
            $query = DB::table('product')->where('is_active', 1);
            
            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }
            
            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            
            $perPage = $request->get('per_page', 12);
            $products = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // GET /api/products/{id}
    public function show($id)
    {
        try {
            $product = DB::table('product')->where('product_ID', $id)->first();
            
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // GET /api/products/category/{category}
    public function byCategory($category)
    {
        try {
            $products = DB::table('product')
                ->where('category', $category)
                ->where('is_active', 1)
                ->limit(8)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // POST /api/products/{id}/image
    public function uploadImage(Request $request, $id)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            $product = DB::table('product')->where('product_ID', $id)->first();
            
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }
            
            // Delete old image if exists
            if ($product->image_path) {
                $oldPath = public_path(str_replace('/syntax-error/public', '', $product->image_path));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $id . '.' . $image->getClientOriginalExtension();
                
                // Create directory if not exists
                if (!file_exists(public_path('storage/products'))) {
                    mkdir(public_path('storage/products'), 0777, true);
                }
                
                $image->move(public_path('storage/products'), $filename);
                $imagePath = '/syntax-error/public/storage/products/' . $filename;
                
                DB::table('product')->where('product_ID', $id)->update(['image_path' => $imagePath]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'image_path' => $imagePath
                ]);
            }
            
            return response()->json(['success' => false, 'message' => 'No image uploaded'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // DELETE /api/products/{id}/image
    public function deleteImage($id)
    {
        try {
            $product = DB::table('product')->where('product_ID', $id)->first();
            
            if ($product && $product->image_path) {
                $oldPath = public_path(str_replace('/syntax-error/public', '', $product->image_path));
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                DB::table('product')->where('product_ID', $id)->update(['image_path' => null]);
                
                return response()->json(['success' => true, 'message' => 'Image deleted']);
            }
            
            return response()->json(['success' => false, 'message' => 'No image to delete'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}