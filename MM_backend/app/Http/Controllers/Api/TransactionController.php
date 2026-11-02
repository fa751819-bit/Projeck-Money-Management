<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
      public function index(Request $request)
    {
        $transactions = $request->user()
            ->transactions()
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
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
            'transaction_date' => ['required', 'date'],
        ]);

        $transaction = $request->user()
            ->transactions()
            ->create($validated);

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

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless(
            $transaction->user_id === $request->user()->id,
            404
        );

        $validated = $request->validate([
            'type' => ['sometimes', 'required', Rule::in(['income', 'expense'])],
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'amount' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'transaction_date' => ['sometimes', 'required', 'date'],
        ]);

        $transaction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diperbarui.',
            'data' => $transaction->fresh(),
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
