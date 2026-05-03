<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    // POST /api/orders - Create new order
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'payment_method' => 'required|string'
        ]);
        
        $cartItems = Cart::where('user_ID', $request->user()->user_ID)->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }
        
        $total = $cartItems->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        $shipping = $total > 2500 ? 0 : 200;
        $grandTotal = $total + $shipping;
        
        // Create order
        $order = Order::create([
            'user_ID' => $request->user()->user_ID,
            'order_status' => 'Pending',
            'total_price' => $grandTotal,
            'order_description' => "Payment Method: {$request->payment_method}",
            'shipping_address' => $request->address
        ]);
        
        // Create order items
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_ID' => $order->order_ID,
                'product_ID' => $item->product_ID,
                'quantity' => $item->quantity,
                'unit_price' => $item->product->price,
                'size_selected' => $item->size_selected
            ]);
            
            // Update product stock
            $product = Product::find($item->product_ID);
            $product->stock -= $item->quantity;
            $product->save();
        }
        
        // Clear cart
        Cart::where('user_ID', $request->user()->user_ID)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_id' => $order->order_ID
        ]);
    }
    
    // GET /api/orders/history - Get user's order history
    public function history(Request $request)
    {
        $orders = Order::where('user_ID', $request->user()->user_ID)
            ->with(['items.product'])
            ->orderBy('order_date', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
    
    // GET /api/orders/{id}/status - Get real-time order status
    public function status($id, Request $request)
    {
        $order = Order::where('order_ID', $id)
            ->where('user_ID', $request->user()->user_ID)
            ->firstOrFail();
            
        return response()->json([
            'success' => true,
            'status' => $order->order_status,
            'order_date' => $order->order_date,
            'total' => $order->total_price
        ]);
    }
}