<?php

namespace App\Repositories\Interfaces;
use App\Models\Cart;
interface CartRepositoryInterface
{

public function getForUser(int $userId) : ?Cart ;
public function delete(Cart $cart) : void ;


}
