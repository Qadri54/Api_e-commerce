<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Helper\MidtransTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use \App\Models\OrderHistory;
class OrderController extends Controller {
    // 6 & 7: CHECKOUT & PAYMENT
    public function checkout(Request $request) {
        // Validasi input: User mengirim array items (id produk & qty)
        // Contoh JSON dari Flutter: { "items": [ {"id": 1, "quantity": 2}, {"id": 2, "quantity": 1} ] }
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        // Gunakan DB Transaction agar jika ada error di tengah, data tidak masuk sebagian
        try {
            return DB::transaction(function () use ($request, $user) {
                // 1. Buat Order ID (UUID/String Unik)
                $orderId = 'ORD-' . strtoupper(Str::random(10));

                // 2. Hitung Total Harga & Siapkan Item
                $totalPrice = 0;
                $orderItems = [];

                // Simpan sementara object Order
                $order = Order::create([
                    'id' => $orderId,
                    'user_id' => $user->id,
                    'total_price' => 0, // Update nanti
                    'status_pembayaran' => 'pending',
                ]);

                foreach ($request->items as $itemData) {
                    $product = Product::find($itemData['id']);

                    // Cek Stok (Opsional)
                    if ($product->stock < $itemData['quantity']) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                    }

                    $subtotal = $product->price * $itemData['quantity'];
                    $totalPrice += $subtotal;

                    // Buat Order Item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $itemData['quantity'],
                        'price_at_purchase' => $product->price,
                    ]);

                    // Kurangi stok (Opsional, bisa juga dikurangi saat status 'settlement')
                    // $product->decrement('stock', $itemData['quantity']);
                }

                // 3. Update Total Price di Order
                $order->update(['total_price' => $totalPrice]);

                // 4. Panggil Helper Midtrans
                $params = [
                    'order_id' => $order->id, // Pakai ID asli database
                    'gross_amount' => (int) $totalPrice,
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                $midtransResponse = MidtransTransaction::createTransaction($params);

                if (isset($midtransResponse['token'])) {
                    return response()->json([
                        'message' => 'Order berhasil dibuat',
                        'order_id' => $order->id,
                        'snap_token' => $midtransResponse['token'],
                        'redirect_url' => $midtransResponse['redirect_url'] ?? null,
                    ], 201);
                } else {
                    throw new \Exception('Gagal mendapatkan Token Midtrans');
                }
            });

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // 9: RIWAYAT PEMBELIAN
    public function index(Request $request) {
        // Ambil order milik user yang sedang login
        $orders = Order::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $orders]);
    }

    // 8: DETAIL & STATUS PESANAN
    public function show(Order $order) {
        // Pastikan user hanya bisa melihat order miliknya sendiri
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Load items beserta detail produknya
        $order->load('items.product');

        return response()->json(['data' => $order]);
    }

    // WEBHOOK HANDLER (Menggantikan closure di routes/api.php)
    public function notificationHandler(Request $request) {
        $payload = $request->all();

        Log::info('Midtrans Webhook:', $payload); // Tetap log untuk debugging

        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $grossAmount = $payload['gross_amount'];
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        // Cari order di DB
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $newStatus = null;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $newStatus = 'pending'; // Tergantung kebijakan, bisa deny
            } else if ($fraudStatus == 'accept') {
                $newStatus = 'settlement';
            }
        } else if ($transactionStatus == 'settlement') {
            $newStatus = 'settlement';
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $newStatus = 'cancel'; // atau 'deny' sesuai enum db anda
        } else if ($transactionStatus == 'pending') {
            $newStatus = 'pending';
        }

        if ($newStatus) {
            // Update status di tabel orders
            $order->update(['status_pembayaran' => $newStatus]);

            // Catat di tabel order_history (Sesuai schema yang kita buat sebelumnya)
            OrderHistory::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'description' => "Midtrans status update: $transactionStatus",
            ]);
        }

        return response()->json(['message' => 'Notification processed']);
    }
}
