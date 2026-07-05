<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request  , Product $product){

      $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000',
      ]);

      $user = $request->user();

      $hasPurchased = $user->orders()->where('status' ,'delivered')
                                        ->whereHas('orderItems' ,function($query) use ($product){

                                        $query->where('product_id',$product->id);

                                        })
                                        ->exists();

                 if(!$hasPurchased){

                 return response()->json([
                    'message' => 'You cannot rate the product until it has been received.',


                 ], 403);
                 }

                 $existingReviews = Review::where('user_id' , $user->id)
                                            ->where('product_id' , $product->id)
                                            ->exists();

            if($existingReviews){
                    return response()->json([

                    'message' => 'You have rated this product before.',

                    ],403);
            }

            $review  = Review::create([

                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_approved' => false ,
            ]);

            return response()->json([
                'message' => 'your rating have been submitted and is being proceesed',
                'review' => $review,
            ]);


    }

    public function index(Request $request , Product $product){


    $user = $request->user();

    $isAdmin = $user && $user->hasRole('admin');

    $reviews = Review::where('product_id' , $product->id)
                        ->when(!$isAdmin , function ($query){

                        $query->where('is_approved' , true);
                        })
                        ->with('user:id,name')
                        ->latest()
                        ->paginate(10);

                            return response()->json($reviews);

    }


    public function approve(Review $review){

    $review->update([
        'is_approved' => true,
    ]);

    return response()->json([
        'message' => 'Review has been approved',
        'review' => $review,
    ]);
    }

    public function reject(Review $review){

    $review->update([
        'is_approved' => false,
    ]);

    return response()->json([
        'message' => 'Review has been rejected',
        'review' => $review,
    ]);
    }

    public function adminIndex(){


    $reviews = Review::with(['user:id,name' ,'product:id,name'])
                            ->latest()
                            ->paginate(10);

    return response()->json($reviews);
    }

}
