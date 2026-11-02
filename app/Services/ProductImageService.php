<?php

namespace App\Services;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ProductImageService
{
    /**
     * Thư mục ảnh chính của bạn đang là: public/product_image
     * Mình vẫn cho fallback thêm product_images để nếu bạn lỡ đặt số nhiều thì code vẫn chạy.
     */
    private const BASE_DIRS = ['product_image', 'product_images'];

    private const EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png'];

    public static function firstImage(?string $productName, ?string $storage = null, ?string $color = null): string
    {
        $images = self::gallery($productName, $storage, $color);

        return $images[0] ?? self::fallbackImage();
    }

    public static function gallery(?string $productName, ?string $storage = null, ?string $color = null): array
    {
        $productName = self::clean($productName);
        $storage = self::clean($storage);
        $color = self::clean($color);

        if ($productName === '') {
            return [];
        }

        foreach (self::BASE_DIRS as $baseDir) {
            $productDir = public_path($baseDir . DIRECTORY_SEPARATOR . $productName);

            if (!is_dir($productDir)) {
                continue;
            }

            $candidateDirs = self::candidateDirectories($productDir, $productName, $storage, $color);

            foreach ($candidateDirs as $dir) {
                $images = self::imagesInDirectory($dir);
                if (!empty($images)) {
                    return $images;
                }
            }
        }

        return [];
    }

    /**
     * Tìm thư mục ảnh đúng theo màu + dung lượng.
     * Ví dụ:
     * public/product_image/iPhone Air/iPhone Air Đen Không Gian 256GB/iPhone Air Đen Không Gian 256GB.webp
     */
    private static function candidateDirectories(string $productDir, string $productName, string $storage, string $color): array
    {
        $dirs = [];

        $exactNames = array_filter(array_unique([
            trim($productName . ' ' . $color . ' ' . $storage),
            trim($productName . ' ' . $storage . ' ' . $color),
            trim($color . ' ' . $storage),
            trim($storage . ' ' . $color),
        ]));

        foreach ($exactNames as $name) {
            $path = $productDir . DIRECTORY_SEPARATOR . $name;
            if (is_dir($path)) {
                $dirs[] = $path;
            }
        }

        $subDirs = glob($productDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        foreach ($subDirs as $dir) {
            $folder = self::clean(basename($dir));

            $matchStorage = $storage === '' || mb_stripos($folder, $storage) !== false;
            $matchColor = $color === '' || mb_stripos($folder, $color) !== false;

            if ($matchStorage && $matchColor) {
                $dirs[] = $dir;
            }
        }

        // Fallback cuối: lấy ảnh bất kỳ bên trong thư mục sản phẩm.
        $dirs[] = $productDir;

        return array_values(array_unique($dirs));
    }

    private static function imagesInDirectory(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $images = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $ext = strtolower($file->getExtension());
            if (!in_array($ext, self::EXTENSIONS, true)) {
                continue;
            }

            $images[] = self::toAssetUrl($file->getPathname());
        }

        sort($images, SORT_NATURAL | SORT_FLAG_CASE);

        return $images;
    }

    private static function toAssetUrl(string $absolutePath): string
    {
        $public = str_replace('\\', '/', public_path());
        $absolutePath = str_replace('\\', '/', $absolutePath);
        $relative = ltrim(str_replace($public, '', $absolutePath), '/');

        return asset($relative);
    }

    private static function fallbackImage(): string
    {
        if (file_exists(public_path('images/no-image.png'))) {
            return asset('images/no-image.png');
        }

        return 'https://via.placeholder.com/500x500?text=No+Image';
    }

    private static function clean(?string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    }
}
