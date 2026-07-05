<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscriptions,email',
        ]);

        NewsletterSubscription::create([
            'email' => $request->email,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Successfully subscribed to newsletter',
        ], 201);
    }
}
