<?php

return [
    'client_id' => getenv('PAYPAL_CLIENT_ID') ?: '',
    'client_secret' => getenv('PAYPAL_CLIENT_SECRET') ?: '',
    'mode' => getenv('PAYPAL_MODE') ?: 'sandbox',
    'currency' => getenv('PAYPAL_CURRENCY') ?: 'USD',
];

