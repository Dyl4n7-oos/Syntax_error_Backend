<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JsonController extends Controller
{
    // GET /api/products/json/export - Export products to JSON file (PHP producing JSON)
    public function exportProducts()
    {
        $products = Product::select('product_ID', 'name', 'price', 'category', 'stock', 'description')
            ->get();
        
        $json = json_encode($products, JSON_PRETTY_PRINT);
        
        $filename = 'exports/products_' . date('Y-m-d_H-i-s') . '.json';
        Storage::put($filename, $json);
        
        return response()->json([
            'success' => true,
            'message' => 'Products exported successfully',
            'file' => $filename,
            'count' => $products->count(),
            'data' => json_decode($json)
        ]);
    }
    
    // GET /api/products/json/consume - Get products as JSON (for jQuery AJAX consumption)
    public function getProductsJson()
    {
        $products = Product::select('product_ID', 'name', 'price', 'category', 'stock')
            ->where('is_active', true)
            ->get();
        
        return response()->json($products);
    }
}