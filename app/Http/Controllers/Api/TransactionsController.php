<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class TransactionsController extends Controller
{
    /**
     * Get all transactions for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTransactions()
    {
        try {
            $user = Auth::user();
            $transactions = Transaction::where('user_id', $user->id)
                ->latest()
                ->paginate(10);

            // Transform all transactions
            $transformedTransactions = $transactions->map(function ($transaction) {
                return $this->transformTransaction($transaction);
            });
            // Tokenize user_id
            $userCodec = tokenize($user->id);

            return $this->success('Transactions retrieved successfully', [
                'user_codec' => $userCodec,
                'transactions' => $transformedTransactions
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Get all FUND transactions for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFundTransactions()
    {
        try {
            $user = Auth::user();
            $transactions = Transaction::where('user_id', $user->id)
                ->where('type', 'FUND')
                ->latest()  // Use latest to order by created_at descending
                ->paginate(10); // Add pagination here

            // Transform fund transactions
            $transformedTransactions = $transactions->map(function ($transaction) {
                return $this->transformTransaction($transaction);
            });
            // Tokenize user_id
            $userCodec = tokenize($user->id);

            return $this->success('Transactions retrieved successfully', [
                'user_codec' => $userCodec,
                'transactions' => $transformedTransactions
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * Get all AIRTIME transactions for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAirtimeTransactions()
    {
        try {
            $user = Auth::user();
            $transactions = Transaction::where('user_id', $user->id)
                ->where('type', 'AIRTIME')
                ->latest()  // Use latest to order by created_at descending
                ->paginate(10); // Add pagination here

            // Transform airtime transactions
            $transformedTransactions = $transactions->map(function ($transaction) {
                return $this->transformTransaction($transaction);
            });
            // Tokenize user_id
            $userCodec = tokenize($user->id);

            return $this->success('Transactions retrieved successfully', [
                'user_codec' => $userCodec,
                'transactions' => $transformedTransactions
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * Transform the transaction data to include user_codec and other required details.
     *
     * @param  Transaction  $transaction
     * @param  string  $userCodec
     * @return array
     */
    private function transformTransaction($transaction)
    {
        // Initialize the transaction data
        $transformed = [
            'id' => $transaction->id,
            'transaction_id' => $transaction->transaction_id,
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at->toISOString(),
            'updated_at' => $transaction->updated_at->toISOString(),
        ];

        // Add metadata if the transaction type is not 'FUND'
        if ($transaction->type !== 'FUND') {
            $transformed['metadata'] = json_decode($transaction->metadata, true);
        }

        return $transformed;
    }
}
