<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller {
    // 1 & 3: Lihat daftar produk + Pencarian
    public function index(Request $request) {
        $query = Product::query();

        // Fitur Pencarian (Search by name)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Fitur Filter Kategori (Jika nanti ada kolom category)
        // if ($request->has('category')) { ... }

        $products = $query->get();

        return response()->json(['message' => 'List products', 'data' => $products], 200);
    }

    // 2: Lihat Detail Produk
    public function show(Product $product) {
        return response()->json(['data' => $product], 200);
    }

    // --- ADMIN METHODS ---
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0', // Tambahkan validasi stok
        ]);

        $product = Product::create($request->all());
        return response()->json(['message' => 'Produk berhasil ditambahkan', 'data' => $product], 201);
    }

    public function update(Request $request, Product $product) {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ]);

        $product->update($request->all());
        return response()->json(['message' => 'Produk berhasil diupdate', 'data' => $product]);
    }

    public function destroy(Product $product) {
        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}
