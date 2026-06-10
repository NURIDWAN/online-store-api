<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => OrderResource::collection(
                Order::with('items.product')->latest()->get()
            )
        ]);
    }

    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {

            $order = DB::transaction(function () use ($validated) {

                // Lock product row to prevent concurrent stock updates
                $product = Product::where('id', $validated['product_id'])
                    ->lockForUpdate()
                    ->first();

                // Check stock availability
                if ($product->stock < $validated['quantity']) {
                    abort(422, 'Insufficient stock');
                }

                // Reduce product stock
                $product->decrement('stock', $validated['quantity']);

                // Create order record
                $order = Order::create([
                    'total' => $product->price * $validated['quantity'],
                ]);

                // Create order item record
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'price' => $product->price,
                ]);

                // Load order items relation
                return $order->load('items');
            });

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => new OrderResource(
                    $order->load('items.product')
                )
            ], 201);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
