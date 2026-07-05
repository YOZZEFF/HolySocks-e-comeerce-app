<?php

namespace App\Repositories\Interfaces;
use App\Models\Coupon;
interface CouponRepositoryInterface
{
    public function findValid(string $code , float $subTotal) : ?Coupon ;
    public function incrementUsage(Coupon $coupon) : void ;



}
