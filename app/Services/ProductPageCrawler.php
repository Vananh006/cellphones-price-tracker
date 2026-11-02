<?php

namespace App\Services;

use App\Support\PriceNormalizer;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ProductPageCrawler
{
    private const MIN_VALID_IPHONE_PRICE = 1000000;

    private const MAX_VALID_IPHONE_PRICE = 100000000;

    /**
     * Giá tối thiểu theo dòng máy, dùng để loại giá trả góp/khuyến mãi như 100.000đ, 2.000.000đ.
     */
    private int $minimumCandidatePrice = self::MIN_VALID_IPHONE_PRICE;

    public function crawl(string $url): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control' => 'no-cache',
        ])
            ->withOptions(['verify' => (bool) config('crawler.verify_ssl', false)])
            ->timeout((int) config('crawler.timeout', 25))
            ->retry((int) config('crawler.retry_times', 2), (int) config('crawler.retry_sleep_ms', 600))
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Không crawl được trang sản phẩm. HTTP status: '.$response->status());
        }

        $html = $response->body();

        if (blank($html)) {
            throw new RuntimeException('Trang sản phẩm trả về nội dung rỗng.');
        }

        $xpath = $this->makeXPath($html);
        $this->minimumCandidatePrice = $this->guessMinimumPriceByProduct($url, $html);

        // Với CellphoneS/FPT Shop, không quét toàn bộ text để tránh bắt nhầm
        // giá phụ kiện, phí trả góp hoặc khuyến mãi 100.000đ.
        // Chỉ dùng meta/JSON-LD/selector chuyên biệt.
        $blocks = [
            $this->extractFromMetaTags($html, $xpath),
            $this->extractFromJsonLd($html),
            $this->extractFromSiteSpecificSelectors($url, $xpath),
            $this->extractFromEmbeddedJson($html),
        ];

        if (! $this->isKnownStoreUrl($url)) {
            array_unshift($blocks, $this->extractFromVisiblePriceText($html));
        }

        $data = array_filter(array_merge(...$blocks), fn ($value): bool => $value !== null && $value !== '');

        $data['source'] = $this->detectSource($url);
        $data['currency'] = $data['currency'] ?? 'VND';

        if (empty($data['price'])) {
            throw new RuntimeException('Không tìm thấy giá sản phẩm trong HTML. Có thể website render giá bằng JavaScript hoặc đang chặn crawler.');
        }

        $data['price'] = (int) $data['price'];

        if (! $this->isValidIphonePrice($data['price'])) {
            throw new RuntimeException('Giá crawl được không hợp lệ cho iPhone: '.number_format($data['price'], 0, ',', '.').'đ. Đã bỏ qua để không ghi sai database.');
        }

        $data['name'] = $this->cleanProductName($data['name'] ?? parse_url($url, PHP_URL_HOST) ?? 'Sản phẩm');
        $data['image_url'] = $this->absoluteUrl($data['image_url'] ?? null, $url);
        $data['original_price'] = isset($data['original_price']) ? (int) $data['original_price'] : null;

        $data['original_price'] = $this->normalizeOriginalPrice($data['price'], $data['original_price']);

        // Fallback quan trọng: một số trang CellphoneS/FPT hiển thị giá gốc bằng
        // span style="text-decoration: line-through" thay vì class old-price/del.
        // Vì vậy nếu các selector phía trên chưa bắt được original_price, ta quét
        // riêng các phần tử bị gạch ngang và vùng text gần giá hiện tại.
        if ($data['original_price'] === null && $xpath) {
            $data['original_price'] = $this->extractOriginalFromStrikethroughElements($xpath, $data['price']);
        }

        if ($data['original_price'] === null) {
            $data['original_price'] = $this->extractOriginalNearCurrentPrice($html, $data['price']);
        }

        return $data;
    }

    private function extractFromSiteSpecificSelectors(string $url, ?DOMXPath $xpath): array
    {
        if (! $xpath) {
            return [];
        }

        $host = mb_strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if (str_contains($host, 'cellphones')) {
            return $this->extractCellphones($xpath);
        }

        if (str_contains($host, 'fptshop')) {
            return $this->extractFptShop($xpath);
        }

        return $this->extractCommonProductSelectors($xpath);
    }

    private function extractCellphones(DOMXPath $xpath): array
    {
        $name = $this->firstText($xpath, [
            "//h1[contains(@class,'product-name')]",
            "//h1[contains(@class,'product__name')]",
            "//*[contains(@class,'box-name__box-product-name')]//h1",
            "//h1",
        ]);

        $priceText = $this->firstText($xpath, [
            "//*[contains(@class,'product__price--show')]",
            "//*[contains(@class,'box-info__box-price')]//*[contains(@class,'product__price')]",
            "//*[contains(@class,'box-price')]//*[contains(@class,'price') and not(contains(@class,'through')) and not(contains(@class,'old'))]",
            "//*[contains(@class,'tpt---price')]",
            "//*[@data-price]",
        ]);

        $dataPrice = $this->firstAttribute($xpath, [
            "//*[@data-price]",
            "//*[@data-product-price]",
        ], ['data-price', 'data-product-price']);

        // Giá gốc/giá niêm yết của CellphoneS thường nằm ở thẻ gạch ngang hoặc class chứa old/through.
        $originalPriceText = $this->firstText($xpath, [
            "//*[contains(@class,'product__price--through')]",
            "//*[contains(@class,'product__price--old')]",
            "//*[contains(@class,'price--through')]",
            "//*[contains(@class,'old-price')]",
            "//*[contains(@class,'price-old')]",
            "//*[contains(@class,'box-price')]//del",
            "//del[contains(.,'đ') or contains(.,'₫')]",
            "//span[contains(@class,'line-through') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@style,'line-through') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@style,'text-decoration') and contains(@style,'line') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@class,'strike') and (contains(.,'đ') or contains(.,'₫'))]",
        ]);

        $originalDataPrice = $this->firstAttribute($xpath, [
            "//*[@data-original-price]",
            "//*[@data-old-price]",
            "//*[@data-list-price]",
            "//*[@data-compare-price]",
        ], ['data-original-price', 'data-old-price', 'data-list-price', 'data-compare-price']);

        $image = $this->firstAttribute($xpath, [
            "//*[contains(@class,'product-image')]//img",
            "//*[contains(@class,'product__image')]//img",
            "//*[contains(@class,'gallery')]//img",
            "//img[contains(@class,'product-image')]",
            "//img[contains(@alt,'iPhone') or contains(@alt,'Samsung') or contains(@alt,'Xiaomi')]",
        ], ['data-src', 'data-original', 'src']);

        $currentPrice = $this->selectValidPrice($dataPrice, $priceText);
        $originalPrice = $this->selectValidOriginalPrice($currentPrice, $originalDataPrice, $originalPriceText);

        return array_filter([
            'name' => $name,
            'price' => $currentPrice,
            'original_price' => $originalPrice,
            'image_url' => $image,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    private function extractFptShop(DOMXPath $xpath): array
    {
        $name = $this->firstText($xpath, [
            "//h1[contains(@class,'st-name')]",
            "//h1[contains(@class,'product-name')]",
            "//*[contains(@class,'st-product')]//h1",
            "//h1",
        ]);

        $priceText = $this->firstText($xpath, [
            "//*[contains(@class,'st-price-main')]",
            "//*[contains(@class,'ProductPrice_currentPrice')]",
            "//*[contains(@class,'price-main')]",
            "//*[contains(@class,'current-price')]",
            "//*[@data-price and number(@data-price) > 1000000]",
        ]);

        $dataPrice = $this->firstAttribute($xpath, [
            "//*[@data-price]",
            "//*[@data-product-price]",
        ], ['data-price', 'data-product-price']);

        // FPT Shop có thể đặt giá cũ ở st-price-sub, old-price, strike, del hoặc dữ liệu JSON nhúng.
        $originalPriceText = $this->firstText($xpath, [
            "//*[contains(@class,'st-price-sub')]",
            "//*[contains(@class,'st-price__strike')]",
            "//*[contains(@class,'ProductPrice_oldPrice')]",
            "//*[contains(@class,'old-price')]",
            "//*[contains(@class,'price-old')]",
            "//*[contains(@class,'price-line-through')]",
            "//del[contains(.,'đ') or contains(.,'₫')]",
            "//span[contains(@class,'line-through') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@style,'line-through') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@style,'text-decoration') and contains(@style,'line') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@class,'strike') and (contains(.,'đ') or contains(.,'₫'))]",
        ]);

        $originalDataPrice = $this->firstAttribute($xpath, [
            "//*[@data-original-price]",
            "//*[@data-old-price]",
            "//*[@data-list-price]",
            "//*[@data-compare-price]",
        ], ['data-original-price', 'data-old-price', 'data-list-price', 'data-compare-price']);

        $image = $this->firstAttribute($xpath, [
            "//*[contains(@class,'st-slider')]//img",
            "//*[contains(@class,'product')]//img",
            "//img[contains(@class,'product')]",
            "//img",
        ], ['data-src', 'data-original', 'src']);

        $currentPrice = $this->selectValidPrice($dataPrice, $priceText);
        $originalPrice = $this->selectValidOriginalPrice($currentPrice, $originalDataPrice, $originalPriceText);

        return array_filter([
            'name' => $name,
            'price' => $currentPrice,
            'original_price' => $originalPrice,
            'image_url' => $image,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    private function extractCommonProductSelectors(DOMXPath $xpath): array
    {
        $name = $this->firstText($xpath, [
            "//h1[contains(@class,'product')]",
            "//h1",
        ]);

        $priceText = $this->firstText($xpath, [
            "//*[contains(@class,'price') and not(contains(@class,'old'))]",
            "//*[@data-price]",
        ]);

        $originalPriceText = $this->firstText($xpath, [
            "//*[contains(@class,'old-price')]",
            "//*[contains(@class,'price-old')]",
            "//*[contains(@class,'original-price')]",
            "//del[contains(.,'đ') or contains(.,'₫')]",
        ]);

        $image = $this->firstAttribute($xpath, [
            "//meta[@property='og:image']",
            "//img[contains(@class,'product')]",
            "//img",
        ], ['content', 'data-src', 'src']);

        $currentPrice = $this->selectValidPrice($priceText);
        $originalPrice = $this->selectValidOriginalPrice($currentPrice, $originalPriceText);

        return array_filter([
            'name' => $name,
            'price' => $currentPrice,
            'original_price' => $originalPrice,
            'image_url' => $image,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    private function extractFromJsonLd(string $html): array
    {
        foreach ($this->matchJsonLdBlocks($html) as $json) {
            $decoded = json_decode($json, true);

            if (! is_array($decoded)) {
                continue;
            }

            foreach ($this->flattenSchemaNodes($decoded) as $node) {
                if (! is_array($node)) {
                    continue;
                }

                $type = Arr::get($node, '@type');
                $types = is_array($type) ? $type : [$type];

                if (! in_array('Product', $types, true)) {
                    continue;
                }

                $offers = Arr::get($node, 'offers', []);
                $offer = is_array($offers) && array_is_list($offers) ? ($offers[0] ?? []) : $offers;

                $price = PriceNormalizer::toInteger(
                    Arr::get($offer, 'price')
                    ?? Arr::get($offer, 'lowPrice')
                    ?? Arr::get($offer, 'salePrice')
                    ?? Arr::get($offer, 'currentPrice')
                    ?? Arr::get($offer, 'highPrice')
                );

                $originalPrice = PriceNormalizer::toInteger(
                    Arr::get($offer, 'originalPrice')
                    ?? Arr::get($offer, 'oldPrice')
                    ?? Arr::get($offer, 'listPrice')
                    ?? Arr::get($offer, 'highPrice')
                    ?? Arr::get($offer, 'priceSpecification.price')
                );
                $originalPrice = $this->normalizeOriginalPrice($price, $originalPrice);

                $image = Arr::get($node, 'image');
                if (is_array($image)) {
                    $image = $image[0] ?? null;
                }

                $result = array_filter([
                    'name' => Arr::get($node, 'name'),
                    'price' => $price,
                    'original_price' => $originalPrice,
                    'currency' => Arr::get($offer, 'priceCurrency', 'VND'),
                    'image_url' => is_string($image) ? $image : null,
                ], fn ($value): bool => $value !== null && $value !== '');

                if ($result !== []) {
                    return $result;
                }
            }
        }

        return [];
    }

    private function extractFromMetaTags(string $html, ?DOMXPath $xpath = null): array
    {
        $xpath = $xpath ?: $this->makeXPath($html);

        if (! $xpath) {
            return [];
        }

        $meta = [];
        foreach ($xpath->query('//meta[@content]') ?: [] as $node) {
            $key = $node->attributes?->getNamedItem('property')?->nodeValue
                ?? $node->attributes?->getNamedItem('name')?->nodeValue
                ?? $node->attributes?->getNamedItem('itemprop')?->nodeValue;

            if ($key) {
                $meta[mb_strtolower($key)] = trim($node->attributes?->getNamedItem('content')?->nodeValue ?? '');
            }
        }

        $price = PriceNormalizer::toInteger(
            $meta['product:price:amount']
            ?? $meta['price']
            ?? $meta['og:price:amount']
            ?? null
        );

        $originalPrice = PriceNormalizer::toInteger(
            $meta['product:price:regular_amount']
            ?? $meta['product:price:original_amount']
            ?? $meta['product:original_price:amount']
            ?? $meta['og:price:standard_amount']
            ?? $meta['original_price']
            ?? $meta['old_price']
            ?? $meta['list_price']
            ?? null
        );
        $originalPrice = $this->normalizeOriginalPrice($price, $originalPrice);

        return array_filter([
            'name' => $meta['og:title'] ?? $meta['twitter:title'] ?? null,
            'price' => $price,
            'original_price' => $originalPrice,
            'currency' => $meta['product:price:currency'] ?? $meta['og:price:currency'] ?? 'VND',
            'image_url' => $meta['og:image'] ?? $meta['twitter:image'] ?? null,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    private function extractFromEmbeddedJson(string $html): array
    {
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $currentPrices = [];
        $originalPrices = [];

        // Nhiều trang FPT/CellphoneS render giá trong __NEXT_DATA__ hoặc state JSON.
        // Tách riêng giá bán hiện tại và giá gốc để tránh original_price luôn null.
        $currentFields = 'price|salePrice|currentPrice|finalPrice|discountPrice|productPrice|priceValue|specialPrice';
        $originalFields = 'originalPrice|oldPrice|listPrice|marketPrice|regularPrice|basePrice|compareAtPrice|strikethroughPrice';

        if (preg_match_all('/"(?:'.$currentFields.')"\s*:\s*"?([0-9]{6,9})"?/iu', $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $price = PriceNormalizer::toInteger($match);
                if ($this->isValidIphonePrice($price)) {
                    $currentPrices[] = (int) $price;
                }
            }
        }

        if (preg_match_all('/"(?:'.$originalFields.')"\s*:\s*"?([0-9]{6,9})"?/iu', $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $price = PriceNormalizer::toInteger($match);
                if ($this->isValidIphonePrice($price)) {
                    $originalPrices[] = (int) $price;
                }
            }
        }

        if (preg_match_all('/(?:"(?:'.$currentFields.')"\s*:\s*")([0-9\.\s]{7,15})\s*(?:đ|₫|VND)/iu', $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $price = PriceNormalizer::toInteger($match);
                if ($this->isValidIphonePrice($price)) {
                    $currentPrices[] = (int) $price;
                }
            }
        }

        if (preg_match_all('/(?:"(?:'.$originalFields.')"\s*:\s*")([0-9\.\s]{7,15})\s*(?:đ|₫|VND)/iu', $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $price = PriceNormalizer::toInteger($match);
                if ($this->isValidIphonePrice($price)) {
                    $originalPrices[] = (int) $price;
                }
            }
        }

        $currentPrices = array_values(array_unique($currentPrices));
        $originalPrices = array_values(array_unique($originalPrices));
        sort($currentPrices);
        sort($originalPrices);

        if ($currentPrices === [] && $originalPrices === []) {
            return [];
        }

        $currentPrice = $currentPrices[0] ?? null;

        // Nếu chỉ bắt được một nhóm giá, xem giá thấp nhất là giá bán hiện tại,
        // giá cao hơn hợp lệ là giá gốc.
        if ($currentPrice === null) {
            $allPrices = $originalPrices;
            sort($allPrices);
            $currentPrice = $allPrices[0] ?? null;
            $originalPrices = array_values(array_filter($allPrices, fn (int $price): bool => $price !== $currentPrice));
        }

        $originalPrice = $this->selectBestOriginalFromPrices($currentPrice, $originalPrices);

        return array_filter([
            'price' => $currentPrice,
            'original_price' => $originalPrice,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    private function extractFromVisiblePriceText(string $html): array
    {
        $decoded = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $prices = [];

        if (preg_match_all('/([0-9]{1,3}(?:[\.\s][0-9]{3}){1,3}|[0-9]+(?:[\.,][0-9]+)?\s*triệu)\s*(?:đ|₫|vnd)?/ui', $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $price = PriceNormalizer::toInteger($match);

                if ($price !== null && $this->isValidIphonePrice($price)) {
                    $prices[] = $price;
                }
            }
        }

        $prices = array_values(array_unique($prices));
        sort($prices);

        if ($prices === []) {
            return [];
        }

        $currentPrice = $prices[0];
        $originalPrice = collect($prices)
            ->filter(fn (int $price): bool => $price > $currentPrice && $price <= $currentPrice * 2.5)
            ->first();

        return [
            'price' => $currentPrice,
            'original_price' => $originalPrice,
        ];
    }

    private function matchJsonLdBlocks(string $html): array
    {
        preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches);

        return array_map(
            fn (string $json): string => trim(html_entity_decode($json, ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            $matches[1] ?? []
        );
    }

    private function flattenSchemaNodes(array $node): array
    {
        $nodes = [$node];

        foreach (['@graph', 'itemListElement'] as $key) {
            if (isset($node[$key]) && is_array($node[$key])) {
                foreach ($node[$key] as $child) {
                    if (is_array($child)) {
                        $nodes = array_merge($nodes, $this->flattenSchemaNodes($child));
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * @param  list<string>  $queries
     */
    private function firstText(DOMXPath $xpath, array $queries): ?string
    {
        foreach ($queries as $query) {
            try {
                $nodes = $xpath->query($query);
                if (! $nodes || $nodes->length === 0) {
                    continue;
                }

                $node = $nodes->item(0);
                $text = trim(preg_replace('/\s+/u', ' ', $node?->textContent ?? ''));

                if ($text !== '') {
                    return $text;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $queries
     * @param  list<string>  $attributes
     */
    private function firstAttribute(DOMXPath $xpath, array $queries, array $attributes): ?string
    {
        foreach ($queries as $query) {
            try {
                $nodes = $xpath->query($query);
                if (! $nodes || $nodes->length === 0) {
                    continue;
                }

                $node = $nodes->item(0);
                if (! $node instanceof DOMElement) {
                    continue;
                }

                foreach ($attributes as $attribute) {
                    $value = trim($node->getAttribute($attribute));
                    if ($value !== '') {
                        return $value;
                    }
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function makeXPath(string $html): ?DOMXPath
    {
        try {
            $internalErrors = libxml_use_internal_errors(true);
            $document = new DOMDocument();
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);

            return new DOMXPath($document);
        } catch (Throwable) {
            return null;
        }
    }



    private function extractOriginalFromStrikethroughElements(DOMXPath $xpath, int $currentPrice): ?int
    {
        $queries = [
            "//del[contains(.,'đ') or contains(.,'₫')]",
            "//s[contains(.,'đ') or contains(.,'₫')]",
            "//strike[contains(.,'đ') or contains(.,'₫')]",
            "//*[contains(@class,'line-through') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@style,'line-through') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@style,'text-decoration') and contains(@style,'line') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@class,'old') and contains(@class,'price') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@class,'through') and (contains(.,'đ') or contains(.,'₫'))]",
            "//*[contains(@class,'strike') and (contains(.,'đ') or contains(.,'₫'))]",
        ];

        $prices = [];

        foreach ($queries as $query) {
            try {
                $nodes = $xpath->query($query);

                if (! $nodes || $nodes->length === 0) {
                    continue;
                }

                foreach ($nodes as $node) {
                    $text = trim(preg_replace('/\s+/u', ' ', $node?->textContent ?? ''));

                    if ($text === '') {
                        continue;
                    }

                    if (preg_match_all('/[0-9]{1,3}(?:[\.\s][0-9]{3}){1,3}/u', $text, $matches)) {
                        foreach ($matches[0] as $match) {
                            $price = PriceNormalizer::toInteger($match);
                            if ($this->isValidIphonePrice($price)) {
                                $prices[] = (int) $price;
                            }
                        }
                    } else {
                        $price = PriceNormalizer::toInteger($text);
                        if ($this->isValidIphonePrice($price)) {
                            $prices[] = (int) $price;
                        }
                    }
                }
            } catch (Throwable) {
                continue;
            }
        }

        return $this->selectBestOriginalFromPrices($currentPrice, $prices);
    }

    private function extractOriginalNearCurrentPrice(string $html, int $currentPrice): ?int
    {
        $plainText = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plainText = preg_replace('/\s+/u', ' ', $plainText) ?: $plainText;

        if (! preg_match_all('/[0-9]{1,3}(?:[\.\s][0-9]{3}){1,3}\s*(?:đ|₫)?/u', $plainText, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $items = [];
        foreach ($matches[0] as [$raw, $offset]) {
            $price = PriceNormalizer::toInteger($raw);
            if ($this->isValidIphonePrice($price)) {
                $items[] = [
                    'price' => (int) $price,
                    'offset' => (int) $offset,
                ];
            }
        }

        if ($items === []) {
            return null;
        }

        // Ưu tiên giá nằm ngay sau giá hiện tại, vì giao diện thường hiển thị:
        // 33.990.000đ 44.990.000đ, trong đó số thứ hai là giá gốc bị gạch ngang.
        foreach ($items as $index => $item) {
            if ($item['price'] !== $currentPrice) {
                continue;
            }

            for ($i = $index + 1; $i < count($items); $i++) {
                $next = $items[$i];

                if ($next['offset'] - $item['offset'] > 160) {
                    break;
                }

                $original = $this->normalizeOriginalPrice($currentPrice, $next['price']);
                if ($original !== null) {
                    return $original;
                }
            }
        }

        // Nếu không tìm được ở gần giá hiện tại, chọn giá hợp lệ lớn hơn giá hiện tại.
        $candidatePrices = array_map(fn (array $item): int => $item['price'], $items);

        return $this->selectBestOriginalFromPrices($currentPrice, $candidatePrices);
    }

    private function selectValidOriginalPrice(?int $currentPrice, ?string ...$candidates): ?int
    {
        $prices = [];

        foreach ($candidates as $candidate) {
            $price = PriceNormalizer::toInteger($candidate);

            if ($this->isValidIphonePrice($price)) {
                $prices[] = (int) $price;
            }
        }

        return $this->selectBestOriginalFromPrices($currentPrice, $prices);
    }

    /**
     * @param  array<int,int|float|null>  $prices
     */
    private function selectBestOriginalFromPrices(?int $currentPrice, array $prices): ?int
    {
        if ($currentPrice === null) {
            return null;
        }

        $prices = array_values(array_unique(array_map('intval', array_filter($prices, fn ($price): bool => $price !== null))));
        sort($prices);

        foreach ($prices as $price) {
            $price = $this->normalizeOriginalPrice($currentPrice, $price);
            if ($price !== null) {
                return $price;
            }
        }

        return null;
    }

    private function normalizeOriginalPrice(int|float|null $currentPrice, int|float|null $originalPrice): ?int
    {
        if ($currentPrice === null || $originalPrice === null) {
            return null;
        }

        $currentPrice = (int) $currentPrice;
        $originalPrice = (int) $originalPrice;

        if (! $this->isValidIphonePrice($originalPrice)) {
            return null;
        }

        // Giá gốc phải lớn hơn giá hiện tại và không quá vô lý.
        if ($originalPrice <= $currentPrice || $originalPrice > $currentPrice * 2.5) {
            return null;
        }

        return $originalPrice;
    }

    private function selectValidPrice(?string ...$candidates): ?int
    {
        foreach ($candidates as $candidate) {
            $price = PriceNormalizer::toInteger($candidate);

            if ($this->isValidIphonePrice($price)) {
                return $price;
            }
        }

        return null;
    }

    private function isKnownStoreUrl(string $url): bool
    {
        $host = mb_strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        return str_contains($host, 'cellphones') || str_contains($host, 'fptshop');
    }

    private function isValidIphonePrice(int|float|null $price): bool
    {
        if ($price === null) {
            return false;
        }

        $price = (int) $price;

        return $price >= max(self::MIN_VALID_IPHONE_PRICE, $this->minimumCandidatePrice)
            && $price <= self::MAX_VALID_IPHONE_PRICE;
    }

    private function guessMinimumPriceByProduct(string $url, string $html): int
    {
        $text = mb_strtolower($url.' '.strip_tags($html));

        return match (true) {
            str_contains($text, 'iphone-17') || str_contains($text, 'iphone 17') => 12000000,
            str_contains($text, 'iphone-air') || str_contains($text, 'iphone air') => 12000000,
            str_contains($text, 'iphone-16') || str_contains($text, 'iphone 16') => 11000000,
            str_contains($text, 'iphone-15') || str_contains($text, 'iphone 15') => 9000000,
            str_contains($text, 'iphone-14') || str_contains($text, 'iphone 14') => 7000000,
            str_contains($text, 'iphone-13') || str_contains($text, 'iphone 13') => 5000000,
            str_contains($text, 'iphone-12') || str_contains($text, 'iphone 12') => 3500000,
            str_contains($text, 'iphone-11') || str_contains($text, 'iphone 11') => 2500000,
            default => self::MIN_VALID_IPHONE_PRICE,
        };
    }

    private function detectSource(string $url): string
    {
        $host = mb_strtolower(parse_url($url, PHP_URL_HOST) ?? 'unknown');

        return match (true) {
            str_contains($host, 'cellphones') => 'cellphones',
            str_contains($host, 'fptshop') => 'fptshop',
            default => $host,
        };
    }

    private function cleanProductName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $name = preg_replace('/\s*[-|]\s*(CellphoneS|FPT Shop).*$/ui', '', $name) ?: $name;

        return mb_substr($name, 0, 255);
    }

    private function absoluteUrl(?string $url, string $baseUrl): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';

            return $scheme.':'.$url;
        }

        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
            $host = parse_url($baseUrl, PHP_URL_HOST);

            return $host ? $scheme.'://'.$host.$url : $url;
        }

        return $url;
    }
}
