<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
         private OrderRepositoryInterface  $orderRepository,
        private CartRepositoryInterface   $cartRepository,
        private CouponRepositoryInterface $couponRepository,
    )
    {}

    public function placeOrder(int $userId , string $paymentMethod , ?string $couponCode , ?int $addressId) : Order

    {
        $cart = $this->cartRepository->getForUser($userId);

        if (! $cart || $cart->cartItems->isEmpty()) {
            throw new \RuntimeException('Cart is empty');
        }

        $subTotal = $cart->cartItems->sum(fn ($i) => $i->price * $i->quantity);

         $settings = Cache::rememberForever('settings', function () {

                return Setting::pluck('value', 'key');
        });
        $freeThreshold = (float) ($settings['shipping_free_threshold'] ?? 500);
        $shippingFee = (float) ($settings['shipping_fee'] ?? 50);
        $shippingCost = $subTotal >= $freeThreshold ? 0 : $shippingFee;

        $coupon = $couponCode
        ? $this->couponRepository->findValid( $couponCode  ,   $subTotal) : null ;

        $this->validateStock($cart->cartItems);

           $order = DB::transaction(function () use ($cart, $coupon, $userId, $addressId , $paymentMethod,  $subTotal, $shippingCost) {
            $discount = $this->calculateDiscount($coupon, $subTotal);
            $total = $subTotal - $discount + $shippingCost;

            if ($coupon) {
                $this->couponRepository->incrementUsage($coupon);
            }


             $order = $this->orderRepository->createOrder([
                'user_id'        => $userId,
                'address_id'     => $addressId,
                'status'         => 'pending',
                'sub_total'      => $subTotal,
                'discount'       => $discount,
                'shipping_cost'  => $shippingCost,
                'total'          => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'coupon_id'      => $coupon?->id,
            ]);

             $this->orderRepository->createPayment([
                'order_id' => $order->id,
                'method'   => $paymentMethod,
                'status'   => 'pending',
                'amount'   => $total,
            ]);

            foreach ($cart->cartItems as $cartItem) {
                $this->orderRepository->createOrderItem([
                    'order_id'     => $order->id,
                    'product_id'   => $cartItem->product_id,
                    'variant_id'   => $cartItem->variant_id,
                    'quantity'     => $cartItem->quantity,
                    'price'        => $cartItem->price,
                    'product_name' => $cartItem->product->name,
                ]);

                    $this->orderRepository->decrementVariantStock($cartItem->variant_id, $cartItem->quantity);
            }

            $this->cartRepository->delete($cart);

            return $order;
        });

        $order->load('orderItems.product', 'user');
        Mail::to($order->user->email)->queue(new OrderConfirmationMail($order));

        return $order;

    }

     public function updateStatus(Order $order, string $status): Order
    {
        $order = $this->orderRepository->updateOrderStatus($order, $status);

        Mail::to($order->user->email)->queue(new OrderStatusUpdatedMail($order));

        return $order->load(['orderItems.product.primaryImage', 'user', 'address']);
    }

      private function validateStock($cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            if ($cartItem->variant->stock < $cartItem->quantity) {
                throw new \RuntimeException('Not enough stock');
            }
        }
    }

     private function calculateDiscount(?Coupon $coupon, float $subTotal): float
    {
        if (! $coupon) {
            return 0;
        }

        return $coupon->type === 'percentage'
            ? $subTotal * $coupon->value / 100
            : $coupon->value;
    }


}
