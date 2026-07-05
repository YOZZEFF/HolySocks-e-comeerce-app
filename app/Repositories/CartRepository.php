<?php

namespace App\Repositories;
use App\Models\Cart;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
public function getForUser(int $userId) : ?Cart
{

return  Cart::where('user_id', $userId)
               ->with('cartItems.variant', 'cartItems.product')
               ->first();

}
public function delete(Cart $cart) : void

{
        $cart->delete();
}
}
