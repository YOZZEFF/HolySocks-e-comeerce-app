<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
class PaymentController extends Controller
{

    private string $baseUrl ;
    private string $apiKey  ;
    private string $integrationId ;
    private string $iframeId ;


    public function __construct()
    {
        $this->baseUrl = config('services.paymob.base_url');
        $this->apiKey = config('services.paymob.api_key');
        $this->integrationId = config('services.paymob.integration_id');
        $this->iframeId = config('services.paymob.iframe_id');
    }

    // send API KEY to Paymob and get token
    private function getAuthToken() : ?string {

    $response = Http::post("{$this->baseUrl}/auth/tokens",[

                'api_key' => $this->apiKey,
    ]);

        return $response->successful() ? $response->json('token') : null ;
    }
    // end send API KEY to Paymob and get token

    // Create an order on Paymob and return the order ID.
    private function registeOrder(string $authToken , int $amountCents , array $items = []) : ?string{

    $response = Http::post("{$this->baseUrl}/ecommerce/orders",[

                    'auth_token' => $authToken,
                    'delivery_needed' => false,
                    'amount_cents' => $amountCents,
                    'currency' => 'EGP',
                    'items' => $items,


    ]);

    return $response->successful() ? $response->json('id') : null ;

    }

    // get payment key from Paymob

    private function getPaymentKey(string $authToken  , int $orderId , int $amountCents , array $billingData = []): ?string {

    $response = Http::post("{$this->baseUrl}/acceptance/payment_keys",[

         'auth_token' => $authToken,
         'amount_cents' => $amountCents,
         'expiration' => 3600,
         'order_id' => $orderId,
         'billing_data' => $billingData,
         'currency' => 'EGP',
         'integration_id' => (int) $this->integrationId,

    ]);

    return $response->successful() ? $response->json('token') : null ;

    }


    // initiate payment ->
    //  takes an order id
    // converts it into a complete payment process
    // Returns the payment Url to user
    public function initiatePayment(Request $request){


    $request->validate([
        'order_id' => 'required|exists:orders,id',
    ]);

     $order = Order::with(['orderItems.product' , 'user' , 'address'])
                        ->findOrFail($request->order_id);


    $authToken = $this->getAuthToken();
    if(!$authToken){

    return response()->json([
        'message' => 'Paymob authentcation failed'
    ],502);

    }
    //  transfer the price to cents

    $amountCents = (int) ($order->total * 100) ;

    $items = $order->orderItems->map(fn($item) => [

        'name' => $item->product_name,
        'amount_cents' => (int) ($item->price *100),
        'description' =>$item->product->description,
        'quantity' => (int) $item->quantity,

    ])->toArray();


    $orderId = $this->registeOrder($authToken , $amountCents ,$items);

    if(!$orderId){

    return response()->json(['message' => 'Order registration failed'],502);
    }


    $billingData =[

      'first_name' => $order->user->name,
      'last_name' => 'NA',
      'email' => $order->user->email,
      'phone_number' => $order->user->phone,
       'apartment'       => 'NA',
        'floor'           => 'NA',
        'street'          =>  $order->address->street,
        'building'        => 'NA',
        'shipping_method' => $order->payment_method,
        'postal_code'     => 'NA',
        'city'            => $order->address->city,
        'country'         => $order->address->country,
        'state'           => 'NA',

    ];


    $paymentKey = $this->getPaymentKey($authToken , $orderId , $amountCents , $billingData);

    if(!$paymentKey){

        return  response()->json(['message' => 'Payment key request failed'],502 );
    }
    // it returns response to frontend  with ( paymentkey & iframe url)

    return response()->json([
        'payment_key' => $paymentKey,
        'iframe_url' => "https://accept.paymob.com/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentKey}",
    ]);

    }

    // its verfied that data was sent by paymob
    // it computes HMAC itself
    // and compare it securely
    private function verifyHmac( array $data , ?string $receivedHmac) : bool {

    if(!$receivedHmac) return false ;

    $fields = [
        'amount_cents', 'created_at', 'currency', 'error_occured',
        'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
        'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
        'is_voided', 'order.id', 'owner', 'pending',
        'source_data.pan', 'source_data.sub_type', 'source_data.type',
        'success',
    ];

    $hmacString = '' ;
    foreach($fields as $field) {

    $hmacString .=data_get($data , $field , ' ');
    }

    $computedHmac = hash_hmac('sha512' , $hmacString , config('services.paymob.hmac_secret') );

    return hash_equals($computedHmac , $receivedHmac);


    }

    public function webhook(Request $request){

    $hmac = $request->query('hmac');
    $isValid = $this->verifyHmac($request->all(), $hmac);

    if(!$isValid){

    return response()->json(['message' => 'HMAC verification failed'],403);
    }

    $success = $request->input('success');
    $orderId = $request->input('order.id');

    if($success == true || $success == 'true'){

    Order::where('id', $orderId)->update(['payment_status' => 'paid']);
    }else{

    Order::where('id', $orderId)->update(['payment_status' => 'failed']);
    }

        return response()->json(['received' => true]);


    }

}
