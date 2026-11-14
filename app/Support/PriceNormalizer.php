<?php

namespace App\Support;

class PriceNormalizer
{
    public static function toInteger(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $number = (int) round((float) $value);
            return $number > 0 ? $number : null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        // Bỏ ký tự tiền tệ và chữ thường gặp: đ, ₫, VND, khoảng trắng.
        $text = str_replace([',', '₫', 'đ', 'Đ', 'VND', 'vnd', ' '], ['', '', '', '', '', '', ''], $text);

        // Nếu còn dạng 24.390.000 thì bỏ dấu chấm ngăn cách hàng nghìn.
        if (preg_match('/\d{1,3}(?:\.\d{3})+/', $text, $matches)) {
            $number = (int) str_replace('.', '', $matches[0]);
            return $number > 0 ? $number : null;
        }

        // Nếu là chuỗi có số liền nhau.
        if (preg_match('/\d+/', $text, $matches)) {
            $number = (int) $matches[0];
            return $number > 0 ? $number : null;
        }

        return null;
    }
}
