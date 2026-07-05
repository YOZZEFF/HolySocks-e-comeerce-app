<?php

namespace App\Repositories;
use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;

class CouponRepository implements CouponRepositoryInterface
{
    public function findValid(string $code , float $subTotal) : ?Coupon
    {

     return  Coupon::where('code',$code)
                          ->where('expiry_date','>=',now())
                          ->where('is_active',true)
                          ->whereColumn('usage_limit','>','usage_count')
                          ->where('min_order','<=',$subTotal)
                          ->first();

    }
    public function incrementUsage(Coupon $coupon) : void
    {

                 $coupon->increment('usage_count');


    }
}
