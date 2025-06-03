<?php

return [
    'serverKey' => env('MIDTRANS_SERVER_KEY', ''),
    'isProduction' => env('MIDTRANS_IS_PRODUCTION', false),
    'isSanitized' => env('MIDTRANS_SANITIZED', ''),
    'is2ds' => env('MIDTRANS_IS_3DS', ''),
];
