<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d'),

            'orders' => $this->whenLoaded('orders', fn() => $this->orders->map(fn($order) => [
                'id'         => $order->id,
                'total'      => $order->total,
                'status'     => $order->status,
                'created_at' => $order->created_at->format('Y-m-d'),
            ])),
        ];
    }
}
