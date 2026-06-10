// config/billing.php

return [
    'stripe_secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'free_price_id' => env('STRIPE_FREE_PRICE_ID'),
    'pro_price_id' => env('STRIPE_PRO_PRICE_ID'),
];