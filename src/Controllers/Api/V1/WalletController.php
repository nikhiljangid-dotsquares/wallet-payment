<?php


namespace admin\wallets\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use admin\wallets\Models\Wallet;
use admin\wallets\Models\WalletTransaction;
use admin\wallets\Models\WithdrawRequest;
use Stripe\Stripe;
use Illuminate\Support\Facades\DB;
use admin\wallets\Models\Wallet;
use admin\wallets\Requests\Api\WalletWithdrawRequest;
use Illuminate\Support\Facades\Mail;


class WalletController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secretKey'));
    }
    
    protected function getUserOrFail()
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'code'    => 401,
                'message' => 'Unauthenticated user.',
                'data'    => (object) []
            ], 401);
        }
        return $user;
    }

    protected function respond($status, $code, $message, $data = [])
    {
        return response()->json([
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'data'    => $data ?: (object) []
        ], $code);
    }

    public function depositInitiate(Request $request)
    {
        $user = $this->getUserOrFail();
        if (!$user instanceof \App\Models\User) return $user; // return JSON if unauthenticated

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount   = $request->amount;
        $currency = config('services.stripe.stripeCurrency', 'usd');
        $stripe   = new StripeClient(config('services.stripe.secretKey'));

        try {
            // Create Stripe customer if not exists
            if (!$user->stripe_customer_id) {
                $customer = $stripe->customers->create([
                    'email' => $user->email,
                    'name'  => $user->name,
                ]);
                $user->update(['stripe_customer_id' => $customer->id]);
            }

            // Create payment intent
            $paymentIntent = $stripe->paymentIntents->create([
                'amount'   => intval($amount * 100),
                'currency' => $currency,
                'customer' => $user->stripe_customer_id,
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'user_id' => $user->id,
                    'type'    => 'wallet_deposit',
                ],
            ]);

            return $this->respond(true, 200, 'Payment intent created.', [
                'payment_intent_id' => $paymentIntent->id,
                'client_secret'     => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, 400, 'Deposit initiation failed: '.$e->getMessage());
        }
    }

    public function depositConfirm(Request $request)
    {
        $user = $this->getUserOrFail();
        if (!$user instanceof \App\Models\User) return $user;

        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $stripe = new StripeClient(config('services.stripe.secretKey'));

        try {
            $paymentIntent = $stripe->paymentIntents->retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                return $this->respond(false, 400, 'Payment not completed.');
            }

            $amount = $paymentIntent->amount / 100;

            DB::transaction(function () use ($user, $amount, $paymentIntent) {
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
                $wallet->increment('balance', $amount);

                WalletTransaction::create([
                    'user_id'        => $user->id,
                    'type'           => 'deposit',
                    'amount'         => $amount,
                    'status'         => 'succeeded',
                    'transaction_id' => $paymentIntent->id,
                    'description'    => 'Wallet deposit via Stripe',
                ]);
            });

            return $this->respond(true, 200, 'Wallet updated successfully.', [
                'balance' => $user->wallet->balance
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, 400, 'Deposit confirmation failed: '.$e->getMessage());
        }
    }

    public function getBalance(Request $request)
    {
        $user = $this->getUserOrFail();
        if (!$user instanceof \App\Models\User) return $user;

        try {
            $wallet   = $user->wallet;
            $balance  = $wallet ? $wallet->balance : 0.00;
            $currency = config('services.stripe.stripeCurrency', 'usd');

            return $this->respond(true, 200, 'Wallet balance fetched successfully.', [
                'balance'  => $balance,
                'currency' => $currency
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, 400, 'Get Balance failed: '.$e->getMessage());
        }
    }

    public function withdrawRequest(WalletWithdrawRequest $request)
    {
        $user = $this->getUserOrFail();
        if (!$user instanceof \App\Models\User) return $user;

        $amount   = (float) $request->amount;
        $currency = config('services.stripe.currency_sign', '$');
        $wallet   = $user->wallet;

        if (!$wallet || $wallet->balance < $amount) {
            return $this->respond(false, 400, 'Insufficient wallet balance.');
        }

        if (!$user->stripe_account_id) {
            return $this->respond(false, 400, 'Stripe account not connected.');
        }

        $alreadyPending = WithdrawRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return $this->respond(false, 400, 'You already have a pending withdrawal request.');
        }

        try {
            DB::transaction(function () use ($user, $amount, $request) {
                WithdrawRequest::create([
                    'user_id'        => $user->id,
                    'amount'         => $amount,
                    'method'         => $request->method,
                    'method_details' => json_encode($request->method_details),
                ]);
            });

            return $this->respond(true, 200, 'Withdrawal request submitted successfully.', [
                'balance' => $wallet->balance
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, 400, 'Withdrawal failed: '.$e->getMessage());
        }
    }

    public function withdrawRequestCancel(Request $request)
    {
        $user = $this->getUserOrFail();
        if (!$user instanceof \App\Models\User) return $user;

        try {
            $withdraw = WithdrawRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$withdraw) {
                return $this->respond(false, 404, 'No pending withdrawal request found.');
            }

            $withdraw->update(['status' => 'cancelled']);

            return $this->respond(true, 200, 'Withdrawal request cancelled successfully.');
        } catch (\Exception $e) {
            return $this->respond(false, 400, 'Cancellation failed: '.$e->getMessage());
        }
    }

    public function transactionHistory(Request $request)
    {
        $user = $this->getUserOrFail();
        if (!$user instanceof \App\Models\User) return $user;

        try {
            $transactions = $user->transactions()
                ->latest()
                ->get();

            return $this->respond(true, 200, 'Transaction history fetched successfully.', [
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return $this->respond(false, 400, 'Fetching history failed: '.$e->getMessage());
        }
    }
} 