<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Order;

class CouponController extends Controller
{
    public function validate( Request $request){

    $request->validate([
        'code' => 'required|string',
        'sub_total' => 'required|numeric|min:0',

    ]);


    $coupon  = Coupon::where('code', $request->code)->first();

    if(!$coupon){

    return response()->json([
        'status' => false,
        'message' => 'Coupon not found',
    ],422);
    }
    if(!$coupon->is_active){

    return response()->json([
        'status' => false,
        'message' => 'Coupon is not active',
    ],404);
    }
    if ($coupon->expiry_date && $coupon->expiry_date->isPast()) {

    return response()->json([
        'status' => false,
        'message' => 'Coupon is expired',
    ],404);

    }

    if($coupon->usage_limit !== null  &&  $coupon->usage_count  >= $coupon->usage_limit){

    return response()->json([
        'status' => false,
        'message' => 'Coupon usage limit reached',

    ],404);
    }

    if ($coupon->min_order > $request->sub_total){

    return response()->json([
        'status' => false,
        'message' => 'Coupon is not valid for this order',
    ],404);
    }

    $discount = $coupon->type == 'percentage'

    ? ($request->sub_total * $coupon->value) / 100
    : $coupon->value;

    return response()->json([
        'status' => true,
        'message' => 'Coupon applied successfully',
        'discount' => $discount,
    ],200);
}


     public function store( Request $request){

     $request->validate([
        'code' => 'required|string',
        'type' => 'required|in:fixed,percentage',
        'value' => 'required|numeric|min:0',
        'min_order' => 'required|numeric|min:0',
        'usage_limit' => 'nullable|numeric|min:0',
        'expiry_date' => 'nullable|date',
        'is_active' => 'required|boolean',

     ]);

     $coupon = Coupon::create($request->only([
    'code',
    'type',
    'value',
    'min_order',
    'usage_limit',
    'expiry_date',
    'is_active',
]));



     return response()->json([
        'status' => true,
        'message' => 'Coupon created successfully',
        'coupon' => $coupon,
     ],201);



     }

     public function index(){

     $coupons = Coupon::paginate(5);

     if($coupons ->isEmpty()){
         return response()->json([
            'status' => false,
            'message' => 'No coupons found',
         ]);
     }

     return response()->json([
        'status' => true,
        'message' => 'Coupons fetched successfully',
        'coupons' => $coupons,
     ],200);
     }

     public function destroy( Coupon $coupon){

     $coupon->delete();

     return response()->json([
        'status' => true,
        'message' => 'Coupon deleted successfully',
     ],200);




     }
}
