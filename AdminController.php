<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    public function stats()
    {
        try {
            $totalProducts = DB::table('product')->count();
            $totalOrders = DB::table('order')->count();
            $totalCustomers = DB::table('customer')->count();
            $totalRevenue = DB::table('order')->sum('total_price');
            $recentOrders = DB::table('order')
                ->leftJoin('customer', 'order.user_ID', '=', 'customer.user_ID')
                ->select('order.*', 'customer.name as customer_name')
                ->orderBy('order.order_date', 'desc')
                ->limit(5)
                ->get();
            
            return response()->json([
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_customers' => $totalCustomers,
                'total_revenue' => floatval($totalRevenue),
                'recent_orders' => $recentOrders
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function products()
    {
        try {
            $products = DB::table('product')->paginate(20);
            return response()->json(['products' => $products]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function orders()
    {
        try {
            $orders = DB::table('order')
                ->leftJoin('customer', 'order.user_ID', '=', 'customer.user_ID')
                ->select('order.*', 'customer.name as customer_name')
                ->orderBy('order.order_date', 'desc')
                ->paginate(20);
            
            return response()->json(['orders' => $orders]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function users()
    {
        try {
            $users = DB::table('customer')->get();
            foreach ($users as $user) {
                $user->orders_count = DB::table('order')->where('user_ID', $user->user_ID)->count();
            }
            return response()->json(['users' => $users]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateOrderStatus(Request $request, $id)
    {
        try {
            DB::table('order')->where('order_ID', $id)->update(['order_status' => $request->status]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function deleteUser($id)
    {
        try {
            DB::table('customer')->where('user_ID', $id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function deleteProduct($id)
    {
        try {
            DB::table('product')->where('product_ID', $id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function storeProduct(Request $request)
    {
        try {
            $id = DB::table('product')->insertGetId([
                'name' => $request->name,
                'price' => $request->price,
                'category' => $request->category,
                'stock' => $request->stock,
                'description' => $request->description,
                'is_active' => 1
            ]);
            return response()->json(['success' => true, 'product_id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateProduct(Request $request, $id)
    {
        try {
            DB::table('product')->where('product_ID', $id)->update([
                'name' => $request->name,
                'price' => $request->price,
                'stock' => $request->stock,
                'category' => $request->category,
                'description' => $request->description
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}