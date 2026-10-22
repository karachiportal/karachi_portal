<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceUpdateLog;
use App\Models\Complaint;
use Illuminate\Http\Request;

class PriceUpdateLogAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceUpdateLog::with(['item.category', 'updater'])
            ->orderByDesc('created_at');

        if ($request->item_name) {
            $s = $request->item_name;
            $query->whereHas('item', fn($q) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('name_urdu', 'like', "%{$s}%")
            );
        }

        if ($request->category_id) {
            $query->whereHas('item', fn($q) =>
                $q->where('price_category_id', $request->category_id)
            );
        }

        if ($request->source) {
            $query->where('source', $request->source);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString();

        $pendingCount = Complaint::where('status', 'pending')->count();

        return view('prices.update-logs', compact('logs', 'pendingCount'));
    }

    public function exportCsv(Request $request)
    {
        $query = PriceUpdateLog::with(['item.category', 'updater'])
            ->orderByDesc('created_at');

        if ($request->item_name) {
            $s = $request->item_name;
            $query->whereHas('item', fn($q) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('name_urdu', 'like', "%{$s}%")
            );
        }

        if ($request->category_id) {
            $query->whereHas('item', fn($q) =>
                $q->where('price_category_id', $request->category_id)
            );
        }

        if ($request->source) {
            $query->where('source', $request->source);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'price-update-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $fh = fopen('php://output', 'w');

            // BOM for UTF-8
            fwrite($fh, "\xEF\xBB\xBF");

            // Header
            fputcsv($fh, [
                'ID', 'Item Name', 'Item Name (Urdu)', 'Category',
                'Old Price (Rs.)', 'New Price (Rs.)', 'Change (Rs.)',
                'Change (%)', 'Source', 'Updated By', 'Updated At',
            ]);

            foreach ($logs as $log) {
                fputcsv($fh, [
                    $log->id,
                    $log->item?->name ?? 'N/A',
                    $log->item?->name_urdu ?? 'N/A',
                    $log->item?->category?->name ?? 'N/A',
                    number_format($log->old_price, 2),
                    number_format($log->new_price, 2),
                    number_format($log->change, 2),
                    number_format($log->change_percent, 2) . '%',
                    ucfirst($log->source),
                    $log->updater?->name ?? 'System',
                    $log->created_at?->format('d M Y, h:i A'),
                ]);
            }

            fclose($fh);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
