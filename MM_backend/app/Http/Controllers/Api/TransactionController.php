<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Category;

class TransactionController extends Controller
{
    public function index(Request $request)
{
    $query = $request->user()->transactions();

    // Filter berdasarkan jenis transaksi
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // Filter berdasarkan kategori
    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }

    $transactions = $query
        ->latest('transaction_date')
        ->latest('id')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $transactions,
    ]);
}

   public function store(Request $request)
{
    $validated = $request->validate([
        'type' => 'required|in:income,expense',
        'category_id' => 'required|exists:categories,id',
        'amount' => 'required|numeric|min:0.01',
        'description' => 'nullable|string',
        'transaction_date' => 'required|date',
    ]);

    $category = Category::whereNull('user_id')
        ->where('id', $validated['category_id'])
        ->where('type', $validated['type'])
        ->first();

    if (!$category) {
        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak valid untuk jenis transaksi ini.',
        ], 422);
    }

    $transaction = $request->user()->transactions()->create([
        'type' => $validated['type'],
        'category_id' => $category->id,
        'category' => $category->name,
        'amount' => $validated['amount'],
        'description' => $validated['description'] ?? null,
        'transaction_date' => $validated['transaction_date'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil ditambahkan.',
        'data' => $transaction,
    ], 201);
}

    public function show(Request $request, Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === $request->user()->id,
            404
        );

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

   public function update(Request $request, string $id)
{
    $transaction = $request->user()
        ->transactions()
        ->findOrFail($id);

    $validated = $request->validate([
        'type' => 'required|in:income,expense',
        'category_id' => 'required|exists:categories,id',
        'amount' => 'required|numeric|min:0.01',
        'description' => 'nullable|string',
        'transaction_date' => 'required|date',
    ]);

    $category = Category::whereNull('user_id')
        ->where('id', $validated['category_id'])
        ->where('type', $validated['type'])
        ->first();

    if (!$category) {
        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak valid untuk jenis transaksi ini.',
        ], 422);
    }

    $transaction->update([
        'type' => $validated['type'],
        'category_id' => $category->id,
        'category' => $category->name,
        'amount' => $validated['amount'],
        'description' => $validated['description'] ?? null,
        'transaction_date' => $validated['transaction_date'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Transaksi berhasil diperbarui.',
        'data' => $transaction,
    ]);
}

    public function destroy(Request $request, Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === $request->user()->id,
            404
        );

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }
}
