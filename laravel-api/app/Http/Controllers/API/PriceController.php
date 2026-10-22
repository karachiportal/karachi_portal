<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PriceCategory;
use App\Models\PriceItem;
use App\Models\PriceHistory;
use App\Models\PriceUpdateLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    // ─────────────────────────────────────────────
    //  PUBLIC: categories list
    // ─────────────────────────────────────────────
    public function categories(): JsonResponse
    {
        $cats = PriceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => [
                'slug'  => $c->slug,
                'name'  => $c->name,
                'urdu'  => $c->name_urdu,
                'icon'  => $c->icon,
                'count' => $c->items()->where('is_active', true)->count(),
            ]);

        // Prepend "All Items"
        $all = collect([[
            'slug'  => 'all',
            'name'  => 'All Items',
            'urdu'  => 'تمام اشیاء',
            'icon'  => '🟩',
            'count' => PriceItem::where('is_active', true)->count(),
        ]]);

        return response()->json([
            'success' => true,
            'data'    => $all->merge($cats)->values(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  PUBLIC: price list (filterable + searchable)
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = PriceItem::with('category')
            ->where('is_active', true)
            ->orderBy('sort_order');

        if ($request->category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('name_urdu', 'like', "%{$request->search}%");
            });
        }

        $items = $query->paginate(50);
        $items->getCollection()->transform(fn($i) => $this->formatItem($i));

        return response()->json([
            'success'      => true,
            'last_updated' => PriceItem::max('updated_at')
                ? \Carbon\Carbon::parse(PriceItem::max('updated_at'))->timezone('Asia/Karachi')->format('d M Y, h:i A')
                : now()->timezone('Asia/Karachi')->format('d M Y, h:i A'),
            'data'         => $items,
        ]);
    }

    // ─────────────────────────────────────────────
    //  PUBLIC: search single item
    // ─────────────────────────────────────────────
    public function search(Request $request): JsonResponse
    {
        $query = $request->q ?? '';
        $item = PriceItem::where('is_active', true)
            ->where(fn($q) =>
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('name_urdu', 'like', "%{$query}%"))
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatItem($item),
        ]);
    }

    // ─────────────────────────────────────────────
    //  PUBLIC: single item detail
    // ─────────────────────────────────────────────
    public function show($id): JsonResponse
    {
        $item = PriceItem::with('category')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data'    => $this->formatItem($item),
        ]);
    }

    // ─────────────────────────────────────────────
    //  PUBLIC: price trend
    // ─────────────────────────────────────────────
    public function trend(Request $request, $id): JsonResponse
    {
        $item = PriceItem::findOrFail($id);
        $days = match ($request->period) {
            '30days'   => 30,
            '3months'  => 90,
            default    => 7,
        };

        $trend = PriceHistory::where('price_item_id', $id)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at')
            ->get()
            ->map(fn($h) => [
                'date'  => $h->recorded_at->format('d M'),
                'price' => $h->price,
            ]);

        // Stats
        $prices = $trend->pluck('price');
        $stats = $prices->isNotEmpty() ? [
            'average'      => round($prices->avg(), 2),
            'highest'      => $prices->max(),
            'highest_date' => PriceHistory::where('price_item_id', $id)
                ->where('price', $prices->max())->first()?->recorded_at?->format('d M Y') ?? '',
            'lowest'       => $prices->min(),
            'lowest_date'  => PriceHistory::where('price_item_id', $id)
                ->where('price', $prices->min())->first()?->recorded_at?->format('d M Y') ?? '',
        ] : null;

        return response()->json([
            'success' => true,
            'data'    => [
                'trend' => $trend,
                'stats' => $stats,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: categories CRUD
    // ─────────────────────────────────────────────
    public function adminCategories(): JsonResponse
    {
        $cats = PriceCategory::withCount('items')->orderBy('sort_order')->get();
        return response()->json(['success' => true, 'data' => $cats]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $this->denyIfViewer();

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:100',
            'name_urdu'  => 'nullable|string|max:100',
            'slug'       => 'required|string|unique:price_categories,slug|max:50',
            'icon'       => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $cat = PriceCategory::create($validator->validated() + ['is_active' => true]);
        return response()->json(['success' => true, 'data' => $cat], 201);
    }

    public function updateCategory(Request $request, $id): JsonResponse
    {
        $this->denyIfViewer();

        $cat = PriceCategory::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name'       => 'sometimes|string|max:100',
            'name_urdu'  => 'nullable|string|max:100',
            'icon'       => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $cat->update($validator->validated());
        return response()->json(['success' => true, 'data' => $cat]);
    }

    public function destroyCategory($id): JsonResponse
    {
        $this->denyIfViewer();

        $cat = PriceCategory::findOrFail($id);
        $cat->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted']);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: items CRUD
    // ─────────────────────────────────────────────
    public function adminItems(Request $request): JsonResponse
    {
        $query = PriceItem::with('category')->orderBy('price_category_id')->orderBy('sort_order');
        if ($request->category_id) {
            $query->where('price_category_id', $request->category_id);
        }
        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function storeItem(Request $request): JsonResponse
    {
        $this->denyIfViewer();

        $validator = Validator::make($request->all(), [
            'price_category_id' => 'required|exists:price_categories,id',
            'name'              => 'required|string|max:100',
            'name_urdu'         => 'nullable|string|max:100',
            'unit'              => 'required|string|max:20',
            'unit_urdu'         => 'nullable|string|max:20',
            'price'             => 'required|numeric|min:0',
            'sort_order'        => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['previous_price'] = $data['price'];
        $data['price_change']   = 0;
        $data['change_percent'] = 0;
        $data['is_active']      = true;

        $item = PriceItem::create($data);

        // Log history
        PriceHistory::create([
            'price_item_id' => $item->id,
            'price'         => $item->price,
            'recorded_at'   => now(),
        ]);

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function updateItem(Request $request, $id): JsonResponse
    {
        $this->denyIfViewer();

        $item = PriceItem::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'price_category_id' => 'sometimes|exists:price_categories,id',
            'name'              => 'sometimes|string|max:100',
            'name_urdu'         => 'nullable|string|max:100',
            'unit'              => 'sometimes|string|max:20',
            'unit_urdu'         => 'nullable|string|max:20',
            'price'             => 'sometimes|numeric|min:0',
            'sort_order'        => 'nullable|integer',
            'is_active'         => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // If price changed, log history and calculate change
        if (isset($data['price']) && $data['price'] != $item->price) {
            $oldPrice = $item->price;
            $change   = $data['price'] - $oldPrice;
            $pct      = $oldPrice > 0 ? round(($change / $oldPrice) * 100, 2) : 0;

            $data['previous_price'] = $oldPrice;
            $data['price_change']   = $change;
            $data['change_percent'] = $pct;

            PriceHistory::create([
                'price_item_id' => $item->id,
                'price'         => $data['price'],
                'recorded_at'   => now(),
            ]);

            // Log to price_update_logs
            PriceUpdateLog::create([
                'price_item_id'  => $item->id,
                'updated_by'     => $request->user()?->id,
                'old_price'      => $oldPrice,
                'new_price'      => $data['price'],
                'change'         => $change,
                'change_percent' => $pct,
                'source'         => 'single',
            ]);
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'data'    => $this->formatItem($item->fresh('category')),
        ]);
    }

    public function destroyItem($id): JsonResponse
    {
        $this->denyIfViewer();

        PriceItem::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Item deleted']);
    }

    // ─────────────────────────────────────────────
    //  ADMIN: Bulk update prices
    // ─────────────────────────────────────────────
    public function bulkUpdate(Request $request): JsonResponse
    {
        $this->denyIfViewer();

        $validator = Validator::make($request->all(), [
            'items'         => 'required|array',
            'items.*.id'    => 'required|exists:price_items,id',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()?->id;

        DB::transaction(function () use ($request, $userId) {
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

                PriceHistory::create([
                    'price_item_id' => $item->id,
                    'price'         => $row['price'],
                    'recorded_at'   => now(),
                ]);

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

        return response()->json([
            'success' => true,
            'message' => count($request->items) . ' item(s) updated',
        ]);
    }

    // ─────────────────────────────────────────────
    //  Helper
    // ─────────────────────────────────────────────
    private function formatItem(PriceItem $item): array
    {
        return [
            'id'            => $item->id,
            'name'          => $item->name,
            'name_urdu'     => $item->name_urdu,
            'unit'          => $item->unit,
            'unit_urdu'     => $item->unit_urdu,
            'price'         => (float) $item->price,
            'previous_price'=> (float) $item->previous_price,
            'price_change'  => (float) $item->price_change,
            'change_percent'=> (float) $item->change_percent,
            'category_slug' => $item->category?->slug,
            'category_name' => $item->category?->name,
            'image_url'     => $item->image_url,
            'is_active'     => $item->is_active,
        ];
    }
}
