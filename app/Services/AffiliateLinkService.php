<?php

namespace App\Services;

use Illuminate\Support\Str;

class AffiliateLinkService
{
    /**
     * Tạo link affiliate đúng chuẩn AccessTrade deep link:
     * deep_link_base?url_enc=base64(product_url)&utm_source=...&utm_medium=...
     *
     * Nếu shop chưa có deep_link_base, trả về null để hệ thống fallback sang product_url.
     */
    public function make(string $productUrl, ?string $storeName = null): ?string
    {
        $productUrl = trim($productUrl);

        if ($productUrl === '' || ! config('affiliate.enabled', true)) {
            return null;
        }

        $storeKey = $this->detectStoreKey($productUrl, $storeName);
        $deepLinkBase = trim((string) config("affiliate.stores.{$storeKey}.deep_link_base"));

        if ($deepLinkBase === '') {
            return null;
        }

        $utmSource = config("affiliate.stores.{$storeKey}.utm_source", config('affiliate.utm_source', 'web_app'));
        $utmMedium = config("affiliate.stores.{$storeKey}.utm_medium", $storeKey);
        $utmCampaign = config("affiliate.stores.{$storeKey}.utm_campaign", config('affiliate.utm_campaign', 'price_tracking'));

        $query = http_build_query([
            'url_enc' => base64_encode($productUrl),
            'utm_source' => $utmSource,
            'utm_medium' => $utmMedium,
            'utm_campaign' => $utmCampaign,
        ], '', '&', PHP_QUERY_RFC3986);

        return rtrim($deepLinkBase, '?&').(str_contains($deepLinkBase, '?') ? '&' : '?').$query;
    }

    public function buyUrl(?string $affiliateUrl, ?string $productUrl): string
    {
        $affiliateUrl = trim((string) $affiliateUrl);
        $productUrl = trim((string) $productUrl);

        return $affiliateUrl !== '' ? $affiliateUrl : $productUrl;
    }

    private function detectStoreKey(string $productUrl, ?string $storeName = null): string
    {
        $text = Str::lower($productUrl.' '.$storeName);

        return match (true) {
            str_contains($text, 'cellphones') => 'cellphones',
            str_contains($text, 'fptshop'), str_contains($text, 'fpt shop') => 'fptshop',
            default => 'default',
        };
    }
}
