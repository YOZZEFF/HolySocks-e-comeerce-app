<x-mail::message>
Hello! We would like to inform you of an update to your order status.

your order number: {{ $order->id }}
order Status: {{ $order->status }}

@if($order->status === 'shipped')
<x-mail::panel>
Your order has been shipped! It will arrive soon. 🚚
</x-mail::panel>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
