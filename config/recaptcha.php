<?php

return [
    'public_key' => env('RECAPTCHA_PUBLIC_KEY'),
    'private_key' => env('RECAPTCHA_PRIVATE_KEY'),
    'field_name' => 'g-recaptcha-response',
    'url' => 'https://www.google.com/recaptcha/api/siteverify',
];
