<x-mail::message>

Thank you for your order!
It has been successfully received.
{{ $order->id }}
{{ number_format($order->total_price, 2) }}
{{ $order->status }}
<x-mail::table>
@foreach($order->orderItems as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ $item->price }} |
@endforeach
</x-mail::table>
We will contact you to confirm shipping.
<x-mail::button :url="config('app.url')">
Shopping Continue
</x-mail::button>
Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
