<?php

return [
    'enabled' => env('AFFILIATE_ENABLED', true),

    'utm_source' => env('AFFILIATE_UTM_SOURCE', 'web_app'),
    'utm_campaign' => env('AFFILIATE_UTM_CAMPAIGN', 'price_tracking'),

    'stores' => [
        'cellphones' => [
            'deep_link_base' => env(
                'ACCESSTRADE_DEEP_LINK_BASE',
                'https://go.isclix.com/deep_link/v5/6959567748204051379/6259155740535091857'
            ),
            'utm_source' => env('AFFILIATE_UTM_SOURCE', 'web_app'),
            'utm_medium' => env('AFFILIATE_UTM_MEDIUM', 'cellphones'),
            'utm_campaign' => env('AFFILIATE_UTM_CAMPAIGN', 'price_tracking'),
        ],

        // Chỉ điền FPTSHOP_DEEP_LINK_BASE khi bạn đã được duyệt chiến dịch FPT Shop.
        // Nếu để trống, hệ thống sẽ không tạo link affiliate sai cho FPT và sẽ fallback sang product_url.
        'fptshop' => [
            'deep_link_base' => env('FPTSHOP_DEEP_LINK_BASE', ''),
            'utm_source' => env('AFFILIATE_UTM_SOURCE', 'web_app'),
            'utm_medium' => 'fptshop',
            'utm_campaign' => env('AFFILIATE_UTM_CAMPAIGN', 'price_tracking'),
        ],

        'default' => [
            'deep_link_base' => '',
            'utm_source' => env('AFFILIATE_UTM_SOURCE', 'web_app'),
            'utm_medium' => 'default',
            'utm_campaign' => env('AFFILIATE_UTM_CAMPAIGN', 'price_tracking'),
        ],
    ],
];
