<?php

namespace admin\wallets\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use admin\wallets\Models\WithdrawRequest;
use admin\wallets\Models\Wallet;
use admin\wallets\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Transfer;


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

    public function changeWithdrawStatus(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $withdrawReq = WithdrawRequest::find($id);

            if (!$withdrawReq) {
                return response()->json(['message' => 'Withdraw request not found.'], 404);
            }

            if (in_array($withdrawReq->status, ['approved','declined','cancelled'])) {
                return response()->json(['message' => 'Withdraw request already processed.'], 400);
            }

            $user = $withdrawReq->user;
            $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            if (!$wallet) {
                DB::rollBack();
                return response()->json(['message' => 'User wallet not found.'], 404);
            }

            $replacement = [
                'USERNAME'     => $user->name,
                'CURRENCY'     => config('services.stripe.currency_sign', '$'),
                'AMOUNT'       => $withdrawReq->amount,
                'REQUESTED_AT' => $withdrawReq->created_at->format(config('GET.admin_date_format')),
                'STATUS'       => ucfirst($request->status),
            ];

            $msg = "Invalid status provided.";
            $code = 400;

            // Approved flow
            if ($request->status === 'approved') {
                if ($wallet->balance < $withdrawReq->amount) {
                    DB::rollBack();
                    return response()->json(['message' => 'Insufficient wallet balance.'], 400);
                }

                // Deduct balance
                $wallet->decrement('balance', $withdrawReq->amount);

                if ($withdrawReq->method === 'stripe') {
                    // Stripe payout
                    if (!$user->stripe_account_id) {
                        DB::rollBack();
                        return response()->json(['message' => 'User Stripe account not connected.'], 400);
                    }

                    // Example: transfer to connected account
                    Stripe::setApiKey(config('services.stripe.secret'));

                    Transfer::create([
                        "amount"      => $withdrawReq->amount * 100, // cents
                        "currency"    => config('services.stripe.currency', 'usd'),
                        "destination" => $user->stripe_account_id,
                    ]);
                }

                // Mark request approved
                $withdrawReq->status = 'approved';
                $withdrawReq->save();

                // Log wallet transaction
                WalletTransaction::create([
                    'user_id'          => $user->id,
                    'type'             => 'withdraw',
                    'amount'           => $withdrawReq->amount,
                    'status'           => 'completed',
                    'related_user_id'  => null,
                    'description'      => 'Withdrawal approved',
                    'admin_commission' => 0,
                ]);

                $replacement['ACTION'] = "Your withdrawal request has been approved.";
                $msg = "Withdraw request approved successfully.";
                $code = 200;
            }

            // Declined flow
            if ($request->status === 'declined') {
                $withdrawReq->status = 'declined';
                $withdrawReq->save();

                $replacement['ACTION'] = "Your withdrawal request has been declined.";
                $msg = "Withdraw request declined successfully.";
                $code = 200;
            }

            DB::commit();
            return response()->json(['message' => $msg], $code);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdraw status update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update status.'], 500);
        }
    }
}
