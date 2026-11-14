<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function export()
    {
        $data = DB::table('product_variants')
            ->join('products', 'product_variants.product_id', '=', 'products.id')

            ->leftJoin('store_prices as sp1', function ($join) {
                $join->on('sp1.variant_id', '=', 'product_variants.id')
                    ->where('sp1.store_id', 1); // CellphoneS
            })

            ->leftJoin('store_prices as sp2', function ($join) {
                $join->on('sp2.variant_id', '=', 'product_variants.id')
                    ->where('sp2.store_id', 2); // FPT
            })

            ->select(
                'products.product_name',

                // 👉 giá (lấy từ CellphoneS)
                'sp1.current_price',
                'sp1.original_price',

                // 👉 link
                'sp1.affiliate_url as cellphones_link',
                'sp2.affiliate_url as fpt_link'
            )
            ->get();
        // Tạo file CSV
        $filename = 'export_affiliate_links.csv';
        $handle = fopen($filename, 'w');
        fwrite($handle, "\xEF\xBB\xBF");
        // Header
        fputcsv($handle, [
            'Sản phẩm',
            'Giá hiện tại',
            'Giá gốc',
            'Link CellphoneS',
            'Link FPT'
        ]);

        foreach ($data as $row) {
            fputcsv($handle, [
                $row->product_name,
                $row->current_price,
                $row->original_price,
                $row->cellphones_link,
                $row->fpt_link ?: 'Chưa có link'
            ]);
        }

        fclose($handle);

        // Download file
        return response()->download($filename)->deleteFileAfterSend(true);
    }
}
