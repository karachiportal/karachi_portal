<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceCategory;
use App\Models\PriceItem;
use App\Models\PriceHistory;
use App\Models\PriceUpdateLog;
use App\Exports\PriceItemExport;
use App\Imports\PriceItemImport;
use App\Models\Complaint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceAdminController extends Controller
{
    public function categories()
    {
        $categories  = PriceCategory::withCount('items')->orderBy('sort_order')->get();
        $pendingCount = Complaint::where('status','pending')->count();
        return view('prices.categories', compact('categories','pendingCount'));
    }

    public function storeCategory(Request $request)
    {
        $this->denyIfViewer();
        $request->validate([
            'name'       => 'required|string|max:100',
            'slug'       => 'required|string|unique:price_categories,slug|max:50',
            'name_urdu'  => 'nullable|string|max:100',
            'icon'       => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $cat = PriceCategory::create($request->all() + ['is_active' => true]);
        return response()->json(['success' => true, 'data' => $cat]);
    }

    public function updateCategory(Request $request, $id)
    {
        $this->denyIfViewer();
        $cat = PriceCategory::findOrFail($id);
        $request->validate([
            'name'       => 'nullable|string|max:100',
            'name_urdu'  => 'nullable|string|max:100',
            'icon'       => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);
        $cat->update($request->only('name','name_urdu','icon','sort_order','is_active'));
        return response()->json(['success' => true, 'data' => $cat]);
    }

    public function destroyCategory($id)
    {
        $this->denyIfViewer();
        $cat = PriceCategory::findOrFail($id);
        $cat->delete();
        return response()->json(['success' => true]);
    }

    public function items(Request $request)
    {
        $categories  = PriceCategory::withCount('items')
            ->where('is_active', true)->orderBy('sort_order')->get();
        $query = PriceItem::with('category')->orderBy('price_category_id')->orderBy('sort_order');
        if ($request->cat) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->cat));
        }
        $items       = $query->paginate(50);
        $totalItems  = PriceItem::count();
        $lastUpdated = PriceItem::max('updated_at')
            ? Carbon::parse(PriceItem::max('updated_at'))->format('d M Y, h:i A')
            : 'N/A';
        $pendingCount = Complaint::where('status','pending')->count();
        return view('prices.items', compact('categories','items','totalItems','lastUpdated','pendingCount'));
    }

    public function storeItem(Request $request)
    {
        $this->denyIfViewer();
        $request->validate([
            'price_category_id' => 'required|exists:price_categories,id',
            'name'  => 'required|string|max:100',
            'unit'  => 'required|string|max:20',
            'price' => 'required|numeric|min:0',
        ]);
        $item = PriceItem::create($request->all() + [
            'previous_price' => $request->price,
            'price_change'   => 0,
            'change_percent' => 0,
            'is_active'      => true,
        ]);
        PriceHistory::create(['price_item_id'=>$item->id,'price'=>$item->price,'recorded_at'=>now()]);
        return response()->json(['success' => true, 'data' => $item]);
    }

    public function updateItem(Request $request, $id)
    {
        $this->denyIfViewer();
        $item = PriceItem::findOrFail($id);
        $data = $request->only('price_category_id','name','name_urdu','unit','unit_urdu','price','sort_order','is_active');

        if (isset($data['price']) && $data['price'] != $item->price) {
            $oldPrice = $item->price;
            $change   = $data['price'] - $oldPrice;
            $pct      = $oldPrice > 0 ? round(($change / $oldPrice) * 100, 2) : 0;
            $data    += ['previous_price'=>$oldPrice,'price_change'=>$change,'change_percent'=>$pct];
            PriceHistory::create(['price_item_id'=>$item->id,'price'=>$data['price'],'recorded_at'=>now()]);

            PriceUpdateLog::create([
                'price_item_id'  => $item->id,
                'updated_by'     => auth()->id(),
                'old_price'      => $oldPrice,
                'new_price'      => $data['price'],
                'change'         => $change,
                'change_percent' => $pct,
                'source'         => 'single',
            ]);
        }

        $item->update($data);
        return response()->json(['success' => true, 'data' => $item->fresh('category')]);
    }

    public function destroyItem($id)
    {
        $this->denyIfViewer();
        PriceItem::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function bulkView()
    {
        $categories  = PriceCategory::with(['items' => fn($q) => $q->where('is_active',true)->orderBy('sort_order')])
            ->where('is_active', true)->orderBy('sort_order')->get();
        $lastUpdated = PriceItem::max('updated_at')
            ? Carbon::parse(PriceItem::max('updated_at'))->format('d M Y, h:i A')
            : 'N/A';
        $pendingCount = Complaint::where('status','pending')->count();
        return view('prices.bulk', compact('categories','lastUpdated','pendingCount'));
    }

    public function bulkUpdate(Request $request)
    {
        $this->denyIfViewer();
        $request->validate([
            'items'         => 'required|array',
            'items.*.id'    => 'required|exists:price_items,id',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $userId = auth()->id();

        DB::transaction(function() use ($request, $userId) {
            foreach ($request->items as $row) {
                $item    = PriceItem::find($row['id']);
                $oldPrice = $item->price;
                $change  = $row['price'] - $oldPrice;
                $pct     = $oldPrice > 0 ? round(($change / $oldPrice) * 100, 2) : 0;
                $item->update([
                    'previous_price' => $oldPrice,
                    'price'          => $row['price'],
                    'price_change'   => $change,
                    'change_percent' => $pct,
                ]);
                PriceHistory::create(['price_item_id'=>$item->id,'price'=>$row['price'],'recorded_at'=>now()]);

                PriceUpdateLog::create([
                    'price_item_id'  => $item->id,
                    'updated_by'     => $userId,
                    'old_price'      => $oldPrice,
                    'new_price'      => $row['price'],
                    'change'         => $change,
                    'change_percent' => $pct,
                    'source'         => 'bulk',
                ]);
            }
        });

        return response()->json(['success'=>true,'message'=>count($request->items).' item(s) updated']);
    }

    // ─────────────────────────────────────────────
    //  EXPORT current prices to Excel
    // ─────────────────────────────────────────────
    public function exportPrices(Request $request)
    {
        return (new PriceItemExport)->download($request->category_slug);
    }

    // ─────────────────────────────────────────────
    //  IMPORT preview (validate + show preview)
    // ─────────────────────────────────────────────
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getPathname();

        $importer = new PriceItemImport;
        $result = $importer->validate($path);

        return response()->json($result);
    }

    // ─────────────────────────────────────────────
    //  IMPORT process (confirm + update DB)
    // ─────────────────────────────────────────────
    public function importProcess(Request $request)
    {
        $this->denyIfViewer();
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->getPathname();

        $importer = new PriceItemImport;
        $result = $importer->process($path, auth()->id());

        return response()->json($result);
    }
}
