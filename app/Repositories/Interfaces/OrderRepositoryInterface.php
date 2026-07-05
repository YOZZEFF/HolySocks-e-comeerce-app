<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\Paginator;
use App\Models\Order;
interface OrderRepositoryInterface
{

public function getForUser(int $userId) : Paginator ;
public function getForAdmin(?string $status = null ) : Paginator ;
public function createOrder(array $data) : Order ;
public function createOrderItem(array $data) : void ;
public function createPayment(array $data) : void ;
public function updateOrderStatus(Order $order , string $status) : Order ;
public function decrementVariantStock(int $variantId, int $quantity): void ;



}
