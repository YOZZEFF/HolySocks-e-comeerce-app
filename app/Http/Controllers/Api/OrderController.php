<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Cart;
use App\Models\Order;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\OrderService;
use Illuminate\Http\Request;



class OrderController extends Controller
{

 public function __construct(
        private OrderService              $orderService,
        private OrderRepositoryInterface  $orderRepository,
    ) {}
    public function store( Request $request) {

    $request->validate([
            'payment_method' => 'required|in:cash_on_delivery,credit_card',
        ]);

        try {
            $order = $this->orderService->placeOrder(
                userId:        auth()->id(),
                paymentMethod: $request->payment_method,
                couponCode:    $request->code,
                addressId:      auth()->user()->address()->where('is_default', true)->first()?->id,
            );

            $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
            $cart->load(['cartItems.product.primaryImage', 'cartItems.variant']);
            $cartTotal = $cart->cartItems->sum(fn ($i) => (float) $i->price * (int) $i->quantity);

            return response()->json([
                'status'  => true,
                'message' => 'Order placed successfully',
                'data'    => [
                    'order_id' => $order->id,
                    'cart'     => $cart,
                    'total'    => $cartTotal,
                ],
            ], 201);

        } catch (\RuntimeException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function index(){

    $orders = $this->orderRepository->getForUser(auth()->id());

    return response()->json([
                        'status' => true ,
                        'message' => 'Orders retrieved successfully',
                        'data' => $orders,
                ]);

    }

    public function show(Order $order){

            //    if user is customer and order belongs to another user
        if(auth()->user()->hasRole('customer') && $order->user_id !== auth()->id()){

                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ],403);
            }

            $order->load('orderItems.product.primaryImage', 'address');

            return response()->json([
                'status' => true,
                'message' => 'Order retrieved successfully',
                'data' => $order,
               ],200);


    }

    public function Adminindex(Request $request){


        $orders = $this->orderRepository->getForAdmin($request->status);

        return response()->json([
                        'status' => true ,
                        'message' => 'Orders retrieved successfully',
                        'data' => $orders,
                       ]);
    }

    public function AdminUpdateStatus(Order $order , Request $request){


        $request->validate([

        'status' => 'required|in:' . implode(',', [

                    Order::STATUS_PENDING,
                    Order::STATUS_CONFIRMED,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_SHIPPED,
                    Order::STATUS_DELIVERED,
                    Order::STATUS_CANCELLED,

]),
 ]);

        $order = $this->orderService->updateStatus($order, $request->status);


        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully',
            'data' =>  $order, ],200);
    }
}


