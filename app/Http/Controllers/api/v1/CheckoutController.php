<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $abaMerchantId;
    protected $abaSecretKey;
    protected $abaApiUrl;

    public function __construct()
    {
        $this->abaMerchantId = env('ABA_MERCHANT_ID');
        $this->abaSecretKey = env('ABA_SECRET_KEY');
        $this->abaApiUrl = env('ABA_API_URL', 'https://payway.ababank.com/api/purchase');
    }

    /**
     * Create a new payment order and redirect to ABA payment page.
     */
    public function checkout(Request $request)
    {
        $orderID = uniqid('order_');
        $amount = $request->input('amount');
        $returnUrl = route('checkout.callback'); // callback after payment

        // Prepare the data for ABA API request
        $data = [
            'merchant_id' => $this->abaMerchantId,
            'order_id'    => $orderID,
            'amount'      => $amount,
            'return_url'  => $returnUrl,
            'transaction_id' => uniqid(),
        ];

        // Generate signature (ABA uses HMAC)
        $data['hash'] = $this->generateSignature($data);

        // Send the payment request to ABA API
        $client = new Client();
        try {
            $response = $client->post($this->abaApiUrl, [
                'form_params' => $data
            ]);
            $responseBody = json_decode($response->getBody(), true);

            // Check if request was successful and redirect to the ABA payment page
            if (isset($responseBody['url'])) {
                return redirect($responseBody['url']);
            } else {
                return back()->withErrors('Payment request failed.');
            }
        } catch (\Exception $e) {
            Log::error('ABA Payment error: ' . $e->getMessage());
            return back()->withErrors('An error occurred during payment processing.');
        }
    }

    /**
     * Generate the hash signature using ABA's secret key.
     */
    private function generateSignature($data)
    {
        $stringToHash = $data['merchant_id'] . $data['order_id'] . $data['amount'];
        return hash_hmac('sha256', $stringToHash, $this->abaSecretKey);
    }

    /**
     * Handle the callback after payment.
     */
    public function callback(Request $request)
    {
        // Handle the callback from ABA, verify the payment status
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
        $paymentStatus = $request->input('status'); // Example: success or failed
        $signature = $request->input('hash');

        // Verify the signature
        $isValid = $this->verifySignature($request->all());

        if ($isValid && $paymentStatus === 'success') {
            // Update your order as paid
            // Order::where('id', $orderId)->update(['status' => 'paid']);
            return redirect()->route('checkout.success');
        } else {
            return redirect()->route('checkout.failed');
        }
    }

    /**
     * Verify the ABA signature.
     */
    private function verifySignature($data)
    {
        $stringToHash = $data['merchant_id'] . $data['order_id'] . $data['amount'];
        $expectedSignature = hash_hmac('sha256', $stringToHash, $this->abaSecretKey);

        return hash_equals($expectedSignature, $data['hash']);
    }

    public function success()
    {
        return view('checkout.success');
    }

    public function failed()
    {
        return view('checkout.failed');
    }
}
