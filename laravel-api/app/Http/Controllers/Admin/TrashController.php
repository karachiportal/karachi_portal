<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PriceCategory;
use App\Models\PriceItem;
use App\Models\Complaint;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    protected array $models;

    public function __construct()
    {
        $this->models = [
            'category'     => ['model' => PriceCategory::class, 'label' => 'Category / قسم', 'name_field' => 'name'],
            'item'         => ['model' => PriceItem::class,     'label' => 'Price Item / آئٹم',  'name_field' => 'name'],
            'complaint'    => ['model' => Complaint::class,     'label' => 'Complaint / شکایت',  'name_field' => 'complaint_number'],
            'user'         => ['model' => User::class,          'label' => 'User / صارف',        'name_field' => 'name'],
        ];
    }

    public function index()
    {
        $trashed = [];
        foreach ($this->models as $type => $cfg) {
            $records = $cfg['model']::onlyTrashed()->orderBy('deleted_at', 'desc')->get()->map(
                fn($r) => [
                    'id'         => $r->id,
                    'type'       => $type,
                    'type_label' => $cfg['label'],
                    'name'       => $r->{$cfg['name_field']} ?? '(no name)',
                    'deleted_at' => $r->deleted_at?->format('d M Y, h:i A'),
                ]
            );
            $trashed = array_merge($trashed, $records->toArray());
        }

        // Sort by deleted_at desc
        usort($trashed, fn($a, $b) => strcmp($b['deleted_at'] ?? '', $a['deleted_at'] ?? ''));

        $pendingCount = Complaint::where('status', 'pending')->count();
        return view('trash.index', compact('trashed', 'pendingCount'));
    }

    public function restore($type, $id)
    {
        $this->denyIfViewer();
        $cfg = $this->models[$type] ?? null;
        if (!$cfg) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }
        $record = $cfg['model']::onlyTrashed()->findOrFail($id);
        $record->restore();
        return response()->json(['success' => true, 'message' => $cfg['label'] . ' restored']);
    }

    public function forceDelete($type, $id)
    {
        $this->denyIfViewer();
        $cfg = $this->models[$type] ?? null;
        if (!$cfg) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }
        $record = $cfg['model']::onlyTrashed()->findOrFail($id);
        $record->forceDelete();
        return response()->json(['success' => true, 'message' => $cfg['label'] . ' permanently deleted']);
    }
}
