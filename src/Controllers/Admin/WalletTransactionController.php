<?php

namespace admin\wallets\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use admin\wallets\Models\WalletTransaction;

class WalletTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->query('keyword');
            $status = $request->query('status');

            $transactions = WalletTransaction::with(['user','relatedUser'])
                            ->whereHas('user', function ($query) use ($search) {
                                if (!empty($search)) {
                                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                                }
                            })
                            ->filterByStatus($status)
                            ->sortable()
                            ->latest()
                            ->paginate(WalletTransaction::getPerPageLimit())
                            ->withQueryString();

            return view('wallet::admin.transactions.index', compact('transactions'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load transactions: ' . $e->getMessage());
        }
    }

    /**
     * show wallet details
     */
    public function show(WalletTransaction $walletTransaction)
    {
        try {
            return view('wallet::admin.transactions.show', [
                'transaction' => $walletTransaction
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load transactions: ' . $e->getMessage());
        }
    }
}
