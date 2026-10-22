<?php

namespace App\Exports;

use App\Models\PriceItem;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PriceItemExport
{
    public function download(?string $categorySlug = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($categorySlug ? ucfirst($categorySlug) : 'Price Items');

        $headers = ['name', 'name_urdu', 'category_slug', 'price', 'unit'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A5C38']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $col++;
        }

        $items = PriceItem::with('category')
            ->where('is_active', true)
            ->when($categorySlug, fn($q) => $q->whereHas('category', fn($cq) => $cq->where('slug', $categorySlug)))
            ->orderBy('price_category_id')
            ->orderBy('sort_order')
            ->get();

        $row = 2;
        foreach ($items as $item) {
            $sheet->setCellValue("A{$row}", $item->name);
            $sheet->setCellValue("B{$row}", $item->name_urdu);
            $sheet->setCellValue("C{$row}", $item->category?->slug);
            $sheet->setCellValue("D{$row}", $item->price);
            $sheet->setCellValue("E{$row}", $item->unit);

            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $row++;
        }

        foreach (range('A', 'E') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = $categorySlug ? "{$categorySlug}_" . date('Y-m-d_His') . '.xlsx' : 'price_items_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
