<?php

namespace admin\wallets\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use admin\wallets\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->query('keyword');
            $status = $request->query('status');

            $withdrawList = WithdrawRequest::with('user')
                            ->whereHas('user', function ($query) use ($search) {
                                if (!empty($search)) {
                                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                                }
                            })
                            ->filterByStatus($status)
                            ->sortable()
                            ->latest()
                            ->paginate(WithdrawRequest::getPerPageLimit())
                            ->withQueryString();

            return view('wallet::admin.withdraws.index', compact('withdrawList'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load withdraws: ' . $e->getMessage());
        }
    }

    /**
     * show wallet details
     */
    public function show(WithdrawRequest $WithdrawRequest)
    {
        try {
            return view('wallet::admin.withdraws.show', [
                'withdraw' => $WithdrawRequest
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load withdraws: ' . $e->getMessage());
        }
    }
}
