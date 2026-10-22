<?php

namespace App\Imports;

use App\Models\PriceItem;
use App\Models\PriceCategory;
use App\Models\PriceHistory;
use App\Models\PriceUpdateLog;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PriceItemImport
{
    protected array $errors = [];
    protected array $warnings = [];
    protected array $preview = [];
    protected int $matched = 0;
    protected int $created = 0;

    protected array $allowedColumns = ['name', 'name_urdu', 'category_slug', 'price', 'unit'];

    public function validate(string $filePath): array
    {
        $this->errors = [];
        $this->preview = [];
        $this->matched = 0;
        $this->created = 0;

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            return ['success' => false, 'errors' => ['Invalid Excel file format.']];
        }

        if (count($rows) < 2) {
            return ['success' => false, 'errors' => ['Excel file is empty or has no data rows.']];
        }

        $headers = array_map(fn($h) => trim(strtolower((string) $h)), $rows[0]);
        $headers = array_filter($headers, fn($h) => $h !== '');

        $extra = array_diff($headers, $this->allowedColumns);
        if (count($extra) > 0) {
            return ['success' => false, 'errors' => ['Unknown columns found: ' . implode(', ', $extra) . '. Allowed columns: ' . implode(', ', $this->allowedColumns)]];
        }

        if (!in_array('name', $headers)) {
            return ['success' => false, 'errors' => ['Required column "name" is missing.']];
        }
        if (!in_array('price', $headers)) {
            return ['success' => false, 'errors' => ['Required column "price" is missing.']];
        }

        $colIndex = array_flip($headers);

        $hasCategory = isset($colIndex['category_slug']);
        $hasUrdu = isset($colIndex['name_urdu']);
        $hasUnit = isset($colIndex['unit']);

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNum = $i + 1;

            $name = trim((string) ($row[$colIndex['name']] ?? ''));
            $rawPrice = trim((string) ($row[$colIndex['price']] ?? ''));
            $price = ($rawPrice === '' || !is_numeric($rawPrice)) ? 0 : (float) $rawPrice;

            if (empty($name) && $rawPrice === '') {
                continue;
            }

            $rowErrors = [];

            if (empty($name)) {
                $rowErrors[] = "Row {$rowNum}: name is empty";
            }

            if ($rawPrice !== '' && !is_numeric($rawPrice)) {
                $rowErrors[] = "Row {$rowNum}: price must be a number";
            } elseif ($price < 0) {
                $rowErrors[] = "Row {$rowNum}: price cannot be negative";
            }

            $categorySlug = null;
            $cat = null;
            if ($hasCategory && !empty(trim((string) ($row[$colIndex['category_slug']] ?? '')))) {
                $categorySlug = trim((string) $row[$colIndex['category_slug']]);
                $cat = PriceCategory::where('slug', $categorySlug)->first();
                if (!$cat) {
                    $rowErrors[] = "Row {$rowNum}: category_slug '{$categorySlug}' not found";
                }
            }

            if (count($rowErrors) > 0) {
                $this->errors = array_merge($this->errors, $rowErrors);
                $this->preview[] = [
                    'row' => $rowNum,
                    'name' => $name,
                    'name_urdu' => $hasUrdu ? trim((string) ($row[$colIndex['name_urdu']] ?? '')) : '',
                    'category_slug' => $categorySlug ?? '',
                    'price' => $price,
                    'unit' => $hasUnit ? trim((string) ($row[$colIndex['unit']] ?? '')) : '',
                    'status' => 'error',
                    'message' => implode('; ', $rowErrors),
                ];
                continue;
            }

            $query = PriceItem::where('is_active', true);
            $query->where(function ($q) use ($name, $hasUrdu, $row, $colIndex) {
                $q->whereRaw('LOWER(name) = ?', [strtolower($name)]);
                if ($hasUrdu && !empty(trim((string) ($row[$colIndex['name_urdu']] ?? '')))) {
                    $q->orWhereRaw('LOWER(name_urdu) = ?', [strtolower(trim((string) $row[$colIndex['name_urdu']]))]);
                }
            });

            if ($categorySlug && $cat) {
                $query->where('price_category_id', $cat->id);
            }

            $item = $query->first();

            if (!$item) {
                if ($cat) {
                    $this->created++;
                    $this->preview[] = [
                        'row' => $rowNum,
                        'name' => $name,
                        'name_urdu' => $hasUrdu ? trim((string) ($row[$colIndex['name_urdu']] ?? '')) : '',
                        'category_slug' => $categorySlug,
                        'price' => $price,
                        'unit' => $hasUnit ? trim((string) ($row[$colIndex['unit']] ?? '')) : '1 Kg',
                        'status' => 'new',
                        'message' => null,
                    ];
                } else {
                    $this->warnings[] = "Row {$rowNum}: '{$name}' not found and no category_slug — skipped";
                    $this->preview[] = [
                        'row' => $rowNum,
                        'name' => $name,
                        'name_urdu' => $hasUrdu ? trim((string) ($row[$colIndex['name_urdu']] ?? '')) : '',
                        'category_slug' => '',
                        'price' => $price,
                        'unit' => $hasUnit ? trim((string) ($row[$colIndex['unit']] ?? '')) : '',
                        'status' => 'not_found',
                        'message' => 'Provide category_slug to create this item',
                    ];
                }
                continue;
            }

            $this->matched++;
            $this->preview[] = [
                'row' => $rowNum,
                'name' => $item->name,
                'name_urdu' => $item->name_urdu,
                'category_slug' => $item->category?->slug,
                'price' => $price,
                'old_price' => $item->price,
                'unit' => $item->unit,
                'new_unit' => $hasUnit ? trim((string) ($row[$colIndex['unit']] ?? '')) : null,
                'status' => 'ok',
                'message' => null,
            ];
        }

        if (count($this->errors) > 0) {
            return [
                'success' => false,
                'errors' => $this->errors,
                'preview' => $this->preview,
                'total_rows' => count($this->preview),
                'matched' => $this->matched,
                'created' => $this->created,
                'not_found' => count($this->warnings),
                'errors_count' => count($this->errors),
            ];
        }

        return [
            'success' => true,
            'preview' => $this->preview,
            'total_rows' => count($this->preview),
            'matched' => $this->matched,
            'created' => $this->created,
            'not_found' => count($this->warnings),
            'warnings' => $this->warnings,
        ];
    }

    public function process(string $filePath, int $userId): array
    {
        $validation = $this->validate($filePath);
        if (!$validation['success']) {
            return $validation;
        }

        $updated = 0;
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($validation, $userId, &$updated, &$created, &$skipped) {
            foreach ($validation['preview'] as $row) {
                if ($row['status'] === 'new') {
                    $cat = PriceCategory::where('slug', $row['category_slug'])->first();
                    if (!$cat) {
                        $skipped++;
                        continue;
                    }

                    $item = PriceItem::create([
                        'price_category_id' => $cat->id,
                        'name' => $row['name'],
                        'name_urdu' => $row['name_urdu'] ?: null,
                        'unit' => $row['unit'] ?: '1 Kg',
                        'price' => $row['price'],
                        'previous_price' => $row['price'],
                        'price_change' => 0,
                        'change_percent' => 0,
                        'is_active' => true,
                    ]);

                    PriceHistory::create([
                        'price_item_id' => $item->id,
                        'price' => $row['price'],
                        'recorded_at' => now(),
                    ]);

                    $created++;
                    continue;
                }

                if ($row['status'] !== 'ok') {
                    $skipped++;
                    continue;
                }

                $item = PriceItem::where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [strtolower($row['name'])])
                    ->first();

                if (!$item) {
                    $skipped++;
                    continue;
                }

                $newPrice = (float) $row['price'];
                $oldPrice = $item->price;
                $change = $newPrice - $oldPrice;
                $pct = $oldPrice > 0 ? round(($change / $oldPrice) * 100, 2) : 0;

                $updateData = [
                    'previous_price' => $oldPrice,
                    'price' => $newPrice,
                    'price_change' => $change,
                    'change_percent' => $pct,
                ];

                if (!empty($row['new_unit'])) {
                    $updateData['unit'] = $row['new_unit'];
                }

                $item->update($updateData);

                PriceHistory::create([
                    'price_item_id' => $item->id,
                    'price' => $newPrice,
                    'recorded_at' => now(),
                ]);

                PriceUpdateLog::create([
                    'price_item_id' => $item->id,
                    'updated_by' => $userId,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'change' => $change,
                    'change_percent' => $pct,
                    'source' => 'import',
                ]);

                $updated++;
            }
        });

        return [
            'success' => true,
            'updated' => $updated,
            'created' => $created,
            'skipped' => $skipped,
            'warnings' => $validation['warnings'] ?? [],
        ];
    }
}
