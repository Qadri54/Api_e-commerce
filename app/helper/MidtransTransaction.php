<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class MidtransTransaction {
    public static function createTransaction($data) {
        $serverKey = env('SERVER_MIDTRANS_KEY'); // Pastikan ini ada di .env
        $encodedKey = base64_encode($serverKey . ':');

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $encodedKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://app.sandbox.midtrans.com/snap/v1/transactions', [
                    'transaction_details' => [
                        'order_id' => $data['order_id'], // Ambil dari parameter Controller
                        'gross_amount' => $data['gross_amount'],
                    ],
                    'customer_details' => [
                        'first_name' => $data['name'],
                        'email' => $data['email'],
                    ]
                ]);

        if ($response->successful()) {
            return $response->json();
        }

        return $response->json(); // Return error json dari Midtrans jika gagal
    }
}
