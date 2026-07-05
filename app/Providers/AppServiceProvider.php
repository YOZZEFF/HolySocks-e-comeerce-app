<?php

namespace App\Providers;

use App\Models\Review;
use App\Observers\ReviewObserver;
use App\Repositories\CartRepository;
use App\Repositories\CouponRepository;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class , OrderRepository::class);
        $this->app->bind(CartRepositoryInterface::class,   CartRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Review::observe(ReviewObserver::class);

        //  add rate limiter for sending 1 email per 1 second maximum.

        RateLimiter::for('mailtrap', function () {

                return Limit::perMinute(6)->by('mailtrap-global');
        });

        //   add rate limiter for 5 requests per minute for login and register
        RateLimiter::for('auth', function ($request) {

                return Limit::perMinute(3)->by($request->ip());
            });

        //  for public routes products , categories , reviews
        RateLimiter::for('api', function ($request) {

                return Limit::perMinute(60)->by($request->ip());
            });

         //  for  newsletter and reviews
        RateLimiter::for('sensitive', function ($request) {

                return Limit::perMinute(10)->by($request->ip() . '|' . optional($request->user())->id);
            });

        // for payment webhook
        RateLimiter::for('webhook', function ($request) {

                return Limit::perMinute(20)->by($request->ip());
            });
            //  for admin routes
        RateLimiter::for('admin', function ($request) {

                return Limit::perMinute(100)->by($request->user()?->id ?? $request->ip());
            });

        Schema::defaultStringLength(191);
    }
}
