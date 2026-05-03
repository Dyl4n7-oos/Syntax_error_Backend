<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    // GET /api/cart - Get all cart items for logged in user
    public function index(Request $request)
    {
        $cartItems = Cart::where('user_ID', $request->user()->user_ID)
            ->with('product')
            ->get();
            
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        $shipping = $subtotal > 2500 ? 0 : 200;
        $total = $subtotal + $shipping;
        
        return response()->json([
            'success' => true,
            'items' => $cartItems,
            'summary' => [
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'item_count' => $cartItems->sum('quantity')
            ]
        ]);
    }
    
    // GET /api/cart/count - Get total items in cart
    public function count(Request $request)
    {
        $count = Cart::where('user_ID', $request->user()->user_ID)->sum('quantity');
        
        return response()->json(['count' => $count]);
    }
    
    // GET /api/cart/summary - Get cart summary for checkout
    public function summary(Request $request)
    {
        $cartItems = Cart::where('user_ID', $request->user()->user_ID)
            ->with('product')
            ->get();
            
        $subtotal = $cartItems->sum(function($item) {
            return $item->quantity * $item->product->price;
        });
        
        $shipping = $subtotal > 2500 ? 0 : 200;
        
        return response()->json([
            'success' => true,
            'items' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping
        ]);
    }
    
    // POST /api/cart/add - Add item to cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:product,product_ID',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string'
        ]);
        
        $product = Product::find($request->product_id);
        
        // Check stock
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available'
            ], 400);
        }
        
        // Find existing cart item
        $cartItem = Cart::where('user_ID', $request->user()->user_ID)
            ->where('product_ID', $request->product_id)
            ->where('size_selected', $request->size ?? null)
            ->first();
            
        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cartItem = Cart::create([
                'user_ID' => $request->user()->user_ID,
                'product_ID' => $request->product_id,
                'quantity' => $request->quantity,
                'size_selected' => $request->size,
            ]);
        }
        
        $cartCount = Cart::where('user_ID', $request->user()->user_ID)->sum('quantity');
        
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_item_id' => $cartItem->cart_item_ID,
            'cart_count' => $cartCount
        ]);
    }
    
    // PUT /api/cart/update/{id} - Update quantity
    public function update(Request $request, $id)
    {
        $request->validate(['change' => 'required|in:-1,1']);
        
        $cartItem = Cart::where('cart_item_ID', $id)
            ->where('user_ID', $request->user()->user_ID)
            ->firstOrFail();
            
        $newQuantity = $cartItem->quantity + $request->change;
        
        if ($newQuantity < 1) {
            $cartItem->delete();
        } else {
            // Check stock
            $product = Product::find($cartItem->product_ID);
            if ($newQuantity > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock'
                ], 400);
            }
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        }
        
        $cartCount = Cart::where('user_ID', $request->user()->user_ID)->sum('quantity');
        
        return response()->json([
            'success' => true,
            'cart_count' => $cartCount
        ]);
    }
    
    // DELETE /api/cart/remove/{id} - Remove item from cart
    public function remove(Request $request, $id)
    {
        $cartItem = Cart::where('cart_item_ID', $id)
            ->where('user_ID', $request->user()->user_ID)
            ->firstOrFail();
            
        $cartItem->delete();
        
        $cartCount = Cart::where('user_ID', $request->user()->user_ID)->sum('quantity');
        
        return response()->json([
            'success' => true,
            'cart_count' => $cartCount
        ]);
    }
}