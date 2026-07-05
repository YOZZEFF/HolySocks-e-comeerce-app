<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use DB;

class DashboardController extends Controller
{
    public function stats(){


    $totalRevenue = Order::where('status' ,Order::STATUS_DELIVERED)
                                ->sum('total');

    $totalOrders = Order::count();

    $totalUsers  = User::role('customer')->count();

    $totalProducts = Product::count();

    $recentOrders = Order::with('user' , 'orderItems')
                            ->latest()
                            ->take(5)
                            ->get();

    $monthlySales = DB::table('orders')
                            ->selectRaw('MONTH(created_at) as month , YEAR(created_at) as year , SUM(total) as revenue , COUNT(*) as  orders_count')
                            ->where('created_at' , '>=' , now()->subMonths(6))
                            ->where('status' , Order::STATUS_DELIVERED)
                            ->groupBy('month' , 'year')
                            ->orderBy('year')
                            ->orderBy('month')
                            ->get();


            return response()->json([
                'status' => true,
                'message' => 'stats retrieved successfully',
                'data' =>[

                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'total_users' => $totalUsers,
                'total_products' => $totalProducts,
                'recent_orders' => $recentOrders,
                'monthly_sales' => $monthlySales

                ],

            ]);

    }
}
