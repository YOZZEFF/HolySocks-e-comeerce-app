<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        //
        $this->updateProductRating($review);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {

        $this->updateProductRating($review);


    }

    private function updateProductRating(Review $review){

    $product = $review->product;

     $avg = $product->reviews()->where('is_approved' , true)->avg('rating');

     $count = $product->reviews()->where('is_approved' , true)->count();

     $product->update([
        'avg_rating' => $avg,
        'rating_count' => $count,
     ]);

    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
       $this->updateProductRating($review);
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }
}
