<?php

namespace admin\wallets\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class WalletWebStripeController extends Controller
{
    public function connectAccountRedirect($userId)
    {
        try {
            Log::info('ConnectAccountRedirect=====URL=======Call========', ['user_id' => $userId]);
    
            $user = User::find($userId);
    
            if (!$user) {
                Log::warning("connectRedirect: User not found.", ['user_id' => $userId]);
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
    
            if (!$user->stripe_account_id) {
                Log::warning("connectRedirect: Stripe account not found for user.", ['user_id' => $userId]);
                return response()->json(['success' => false, 'message' => 'Stripe account not found.'], 400);
            }
    
            $stripe = new \Stripe\StripeClient(config('services.stripe.secretKey'));
    
            $acctStatus = $stripe->accounts->retrieve($user->stripe_account_id, []);
    
            // Check both charges and payouts enabled
            if ($acctStatus->charges_enabled && $acctStatus->payouts_enabled) {
                $user->stripe_payouts_enabled = 1;
                $user->save();
    
                Log::info("Stripe payouts enabled for user.", ['user_id' => $user->id]);
                return response()->json(['success' => true, 'message' => 'Stripe payouts enabled successfully.']);
            }
    
            Log::warning("Stripe payouts not enabled yet.", [
                'user_id' => $user->id,
                'charges_enabled' => $acctStatus->charges_enabled,
                'payouts_enabled' => $acctStatus->payouts_enabled,
            ]);
    
            return response()->json(['success' => false, 'message' => 'Stripe payouts not enabled yet.']);
        } catch (\Exception $e) {
            Log::error('Stripe Connect Redirect Error: '.$e->getMessage(), ['user_id' => $userId]);
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }
}
