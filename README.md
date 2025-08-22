# Admin Wallet & Payment Manager

The Wallet & Payment system was developed to enable seamless money management between users and the platform. It allows users to manage funds, make withdrawal requests, and perform transactions, while enabling the admin to monitor activities, manage requests, and earn commissions. This module improves financial transparency and streamlines payment operations for both users and administrators.

---

## Features
Admin Side:-
-> Withdraw request list/show/status
-> Withdraw request status update
-> Wallet transactions history list/show

API Side:-
-> Wallet add money
-> Wallet Withdraw Request
-> Wallet Balance check
-> User Connect Stripe Account	
-> My Wallet Transactions
-> Withdraw request cancellation by user (if status = pending)
-> User referral system (referral bonus added to wallet)
-> Create whole module package wise
-> read documentation how to setup a composer package
-> prepare composer package
-> upload package to github
-> install this package in another laravel project
-> check this package dependencies
-> this package test in another project
-> use this package functionality to another package

---

## Requirements

- PHP >=8.2
- Laravel Framework >= 12.x

---

## Installation

### 1. Add Git Repository to `composer.json`

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/nikhiljangid-dotsquares/wallet-payment.git"
    }
]
```

### 2. Require the package via Composer
    ```bash
    composer require admin/wallets:dev-main
    ```

### 3. Publish assets
    ```bash
    php artisan wallets:publish --force
    ```
---

### 4. Run Migrations
    ```bash
    php artisan migrate --path=Modules/Wallets/Database/Migrations
    ```
---

### 5. Update User Model
    ```bash
    use Modules\Wallets\App\Models\Wallet;
    use Modules\Wallets\App\Models\WalletTransaction;
    ```

    ```bash
    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
    ```

### 6. Configure .env
    ```bash
    STRIPE_KEY=SECRET_PUBLISH_KEY
    STRIPE_SECRET=SECRET_KEY
    STRIPE_WEBHOOK_SECRET=STRIPE_WEBHOOK_SECRET_KEY
    STRIPE_CURRENCY=USD
    STRIPE_CURRENCY_SIGN=$
    ```

### 7. Update config/services.php
    ```bash
    'stripe' => [
        'publicKey' => env('STRIPE_KEY'),
        'secretKey' => env('STRIPE_SECRET'),
        'stripeCurrency' => env('STRIPE_CURRENCY'),
        'stripeWebhookSecret' => env('STRIPE_WEBHOOK_SECRET'),
        'stripeCurrencySign' => env('STRIPE_CURRENCY_SIGN', '$'),
    ],
    ```

## API Routes (examples)

| Method | Endpoint                                          | Description                       |
|--------|---------------------------------------------------|-----------------------------------|
| GET    | `/api/v1/wallets/wallet/balance`                  | Check wallet balance              |
| POST   | `/api/v1/wallets/wallet/deposit/initiate`         | Initiate deposit via Stripe       |
| POST   | `//api/v1/wallets/wallet/deposit/confirm`         | Confirm deposit                   |
| POST   | `/api/v1/wallets/wallet/withdraw-request`         | Request withdrawal                |
| POST   | `/api/v1/wallets/wallet/withdraw-request-cancel`  | Cancel withdrawal (if pending)    |
| GET    | `/api/v1/wallets/wallet/transactions`             | List wallet transactions          |
| GET    | `/api/v1/wallets/connect-stripe`                  | Connect Stripe account            |

---

## Protecting Admin Routes

Protect your routes using the provided middleware:

```php
Route::middleware(['web','admin.auth'])->group(function () {
    // routes here
});

Route::middleware(['api','auth:sanctum'])->group(function () {
    // routes here
});
```

---

## License

This package is open-sourced software licensed under the MIT license.
