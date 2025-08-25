# Admin Wallet & Payment Manager

The Wallet & Payment system was developed to enable seamless money management between users and the platform. It allows users to manage funds, make withdrawal requests, and perform transactions, while enabling the admin to monitor activities, manage requests. This module improves financial transparency and streamlines payment operations for both users and administrators.

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
    composer require admin/wallets:@dev
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
    protected $fillable = [
        'stripe_customer_id',
        'stripe_account_id',
        'stripe_payouts_enabled',
    ];
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

### 8. Add Status Change Script in any (custom JS)
```bash
    function openModelToChangeStatus(element, currentStatus) {
        var url = $(element).data('url'); // now this works
        var id = $(element).data('id'); // optional, if needed
        console.log("URL:", url);
        console.log("ID:", id);
        Swal.fire({
            title: "Change Withdraw Status",
            input: "select",
            inputOptions: {
                approved: "Approve",
                declined: "Decline",
                pending: "Pending"
            },
            inputValue: currentStatus,
            inputPlaceholder: "Select status",
            showCancelButton: true,
            confirmButtonText: "Update",
            cancelButtonText: "Cancel",
            preConfirm: (selectedStatus) => {
                if (!selectedStatus) {
                    Swal.showValidationMessage("Please select a status.");
                    return false;
                }
                return selectedStatus;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                changeStatusAjax(id, result.value, url);
            }
        });
    }

    function changeStatusAjax(id, selectedStatus, url) {
        $.ajax({
            url: url, // Make sure this matches your Laravel route
            method: 'POST',
            data: {
                status: selectedStatus,
                _token: $('meta[name="csrf-token"]').attr('content') // CSRF token for Laravel
            },
            success: function (response) {
                toastr.success(response.message || 'Status updated successfully'); // Toastr success
                // Optionally reload page or update UI dynamically
                setTimeout(() => {
                    location.reload();
                }, 1000); // small delay to show toast before reload
            },
            error: function (xhr) {
                let message = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message); // Toastr error
            }
        });
    }
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

## Protecting Admin/Api Routes

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
