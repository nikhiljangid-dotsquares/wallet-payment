<?php

namespace admin\wallets\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\Customer;
use Stripe\StripeClient;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
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

    protected function getUserOrFail()
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return $this->respond(false, 401, 'Unauthenticated user.');
        }
        return $user;
    }

    public function connectStripe(Request $request)
    {
        try {
            $user = $this->getUserOrFail();
            if (!$user instanceof User) return $user; // return JSON if not logged in

            if ((int)($user->stripe_payouts_enabled ?? 0) === 0) {
                $this->createConnectedAccount($user);

                $user = User::find($user->id);
                if (is_null($user)) {
                    return $this->respond(false, 401, 'Please login again.');
                }

                $stripe   = new StripeClient(config('services.stripe.secret'));
                $acctlink = $stripe->accountLinks->create([
                    'account'     => $user->stripe_account_id,
                    'refresh_url' => 'https://connect.stripe.com/reauth',
                    'return_url'  => url('/'),
                    'type'        => 'account_onboarding',
                ]);

                return $this->respond(true, 200, 'Stripe connect URL generated.', [
                    'url'        => $acctlink->url,
                    'expires_at' => date('d M, Y H:i:s', $acctlink->expires_at),
                ]);
            }

            return $this->respond(false, 400, 'Stripe already connected.');
        } catch (\Exception $e) {
            Log::error('Stripe Connect Error: '.$e->getMessage());
            return $this->respond(false, 400, 'Stripe connection failed: '.$e->getMessage());
        }
    }

    protected function createConnectedAccount(User $checkuser)
    {
        if ($checkuser->stripe_account_id) {
            return $checkuser->stripe_account_id;
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Ensure Stripe customer exists
        if (!$checkuser->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'email' => $checkuser->email,
                'name'  => $checkuser->name,
            ]);

            $checkuser->update(['stripe_customer_id' => $customer->id]);
        }

        $account = $stripe->accounts->create([
            'type'          => 'custom',
            'email'         => $checkuser->email,
            'business_type' => 'individual',
            'capabilities'  => [
                'transfers' => ['requested' => true],
            ],
            'business_profile' => [
                'product_description' => 'Digital wallet system for sending and receiving money.',
                'url'                 => 'https://wallet-system.co.org',
            ],
        ]);

        $checkuser->update([
            'stripe_account_id'  => $account->id,
            'profile_step'       => 3,
        ]);

        return $account->id;
    }
}