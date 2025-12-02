<?php

use Illuminate\Support\Str;

if (!function_exists('generateTrackingNumber')) {
    /**
     * Generate a tracking number with a prefix based on shipping service.
     * This helper is autoloaded via composer `files` so it's always available.
     */
    function generateTrackingNumber($shippingService)
    {
        $prefixes = [
            'JNE' => 'JNE',
            'JNT' => 'JNT',
            'SiCepat' => 'SP',
            'Gojek' => 'GJ',
            'Grab' => 'GR'
        ];

        $prefix = $prefixes[$shippingService] ?? 'TRK';
        $random = strtoupper(Str::random(10));
        return $prefix . $random;
    }
}

if (!function_exists('getPaymentAccount')) {
    /**
     * Return consistent payment account details for a payment method.
     */
    function getPaymentAccount($paymentMethod)
    {
        $accounts = [
            'QRIS' => [
                'name' => 'QRIS - ChickPatrol Store',
                'account' => 'QR-' . substr(md5('qris_chickpatrol'), 0, 6) . '-' . substr(md5('qris_chickpatrol'), 6, 4),
                'type' => 'QRIS'
            ],
            'Transfer Bank' => [
                'name' => 'Bank BCA',
                'account' => '1234567890',
                'account_name' => 'PT ChickPatrol Indonesia',
                'type' => 'Bank Transfer'
            ]
        ];

        return $accounts[$paymentMethod] ?? null;
    }
}
