<?php

namespace App\Repositories;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class OrderRepository implements OrderRepositoryInterface
{

public function getForUser(int $userId) : Paginator
{


        return  Order::where('user_id', $userId)
                        ->with(['orderItems.product.primaryImage'])
                        ->latest()
                        ->simplePaginate(5);

}

public function getForAdmin(?string $status = null ) : Paginator
{

    return  Order::with(['orderItems.product.primaryImage' , 'user' , 'address'])
                        ->when($status ,  fn($q) =>

                         $q->where('status',$status)

                         )
                         ->latest()
                         ->simplePaginate(5);


}

public function createOrder(array $data) : Order
{
        return Order::create($data);
}

public function createOrderItem(array $data) : void
{
     OrderItem::create($data);
}

public function createPayment(array $data) : void
{
        Payment::create($data);
}

public function updateOrderStatus(Order $order, string $status): Order

{

        $order->update(['status' => $status,]);

        return $order;

}
public function decrementVariantStock(int $variantId, int $quantity): void
{
    // App\Models\Variant or whatever your model is
    ProductVariant::where('id', $variantId)->decrement('stock', $quantity);
}

}
