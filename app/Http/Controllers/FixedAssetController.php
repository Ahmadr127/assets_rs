<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\Location;
use App\Models\AssetStatus;
use App\Models\AssetCondition;
use App\Models\Vendor;
use App\Models\Brand;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FixedAssetController extends Controller
{
    /**
     * Normalize lookup fields: if <name>_id missing but <name>_text provided,
     * find or create the master and merge the id back into the request.
     */
    protected function normalizeLookups(Request $request): void
    {
        $map = [
            'asset_type_id' => [AssetType::class, 'asset_type_id_text'],
            'location_id'   => [Location::class, 'location_id_text'],
            'status_id'     => [AssetStatus::class, 'status_id_text'],
            'condition_id'  => [AssetCondition::class, 'condition_id_text'],
            'vendor_id'     => [Vendor::class, 'vendor_id_text'],
            'brand_id'      => [Brand::class, 'brand_id_text'],
        ];

        $merged = [];
        foreach ($map as $field => [$model, $textField]) {
            $id = $request->input($field);
            $text = trim((string)$request->input($textField, ''));

            if ($id) { continue; }
            if ($text === '') { continue; }

            // Find by case-insensitive name first
            $existing = $model::whereRaw('LOWER(name) = ?', [mb_strtolower($text)])->first();
            if ($existing) {
                $merged[$field] = $existing->id;
                continue;
            }
            // Create if name length >= 2
            if (mb_strlen($text) >= 2) {
                $created = $model::create(['name' => $text]);
                $merged[$field] = $created->id;
            }
        }
        if (!empty($merged)) {
            $request->merge($merged);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FixedAsset::with(['location','statusRef','conditionRef','vendorRef','brandRef','typeRef']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter (normalized)
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Condition filter (normalized)
        if ($request->filled('condition_id')) {
            $query->where('condition_id', $request->condition_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('efektif_mulai', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('efektif_mulai', '<=', $request->date_to);
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        $fixedAssets = $query->paginate(15)->withQueryString();

        $statuses = AssetStatus::orderBy('name')->pluck('name','id');
        $conditions = AssetCondition::orderBy('name')->pluck('name','id');

        return view('fixed-assets.index', compact('fixedAssets','statuses','conditions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = Location::orderBy('name')->pluck('name','id');
        $statuses = AssetStatus::orderBy('name')->pluck('name','id');
        $conditions = AssetCondition::orderBy('name')->pluck('name','id');
        $vendors = Vendor::orderBy('name')->pluck('name','id');
        $brands = Brand::orderBy('name')->pluck('name','id');
        $types = AssetType::orderBy('name')->pluck('name','id');
        return view('fixed-assets.create', compact('locations','statuses','conditions','vendors','brands','types'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Resolve/create lookups from *_text before validation
        $this->normalizeLookups($request);
        Log::info('FixedAsset store payload', [ 'payload' => $request->all() ]);
        $validator = Validator::make($request->all(), FixedAsset::rules());

        if ($validator->fails()) {
            Log::warning('FixedAsset store validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['harus_dicek_fisik'] = $request->has('harus_dicek_fisik');
        // Backfill legacy non-null columns for compatibility during transition
        if (empty($data['tipe_fixed_asset']) && !empty($data['asset_type_id'])) {
            $type = AssetType::find($data['asset_type_id']);
            if ($type) {
                $data['tipe_fixed_asset'] = $type->name;
            }
        }
        if (empty($data['lokasi']) && !empty($data['location_id'])) {
            $loc = Location::find($data['location_id']);
            if ($loc) {
                $data['lokasi'] = $loc->name;
            }
        }
        if (empty($data['vendor']) && !empty($data['vendor_id'])) {
            $vendor = Vendor::find($data['vendor_id']);
            if ($vendor) {
                $data['vendor'] = $vendor->name;
            }
        }
        if (empty($data['brand']) && !empty($data['brand_id'])) {
            $brand = Brand::find($data['brand_id']);
            if ($brand) {
                $data['brand'] = $brand->name;
            }
        }
        FixedAsset::create($data);

        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed Asset berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(FixedAsset $fixedAsset)
    {
        $fixedAsset->load(['location','statusRef','conditionRef','vendorRef','brandRef','typeRef']);
        return view('fixed-assets.show', compact('fixedAsset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FixedAsset $fixedAsset)
    {
        $locations = Location::orderBy('name')->pluck('name','id');
        $statuses = AssetStatus::orderBy('name')->pluck('name','id');
        $conditions = AssetCondition::orderBy('name')->pluck('name','id');
        $vendors = Vendor::orderBy('name')->pluck('name','id');
        $brands = Brand::orderBy('name')->pluck('name','id');
        $types = AssetType::orderBy('name')->pluck('name','id');
        return view('fixed-assets.edit', compact('fixedAsset','locations','statuses','conditions','vendors','brands','types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FixedAsset $fixedAsset)
    {
        // Resolve/create lookups from *_text before validation
        $this->normalizeLookups($request);
        Log::info('FixedAsset update payload', [ 'id' => $fixedAsset->id, 'payload' => $request->all() ]);
        $validator = Validator::make($request->all(), FixedAsset::rules($fixedAsset->id));

        if ($validator->fails()) {
            Log::warning('FixedAsset update validation failed', [
                'id' => $fixedAsset->id,
                'errors' => $validator->errors()->toArray()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['harus_dicek_fisik'] = $request->has('harus_dicek_fisik');
        // Backfill legacy non-null columns for compatibility during transition
        if (empty($data['tipe_fixed_asset']) && !empty($data['asset_type_id'])) {
            $type = AssetType::find($data['asset_type_id']);
            if ($type) {
                $data['tipe_fixed_asset'] = $type->name;
            }
        }
        if (empty($data['lokasi']) && !empty($data['location_id'])) {
            $loc = Location::find($data['location_id']);
            if ($loc) {
                $data['lokasi'] = $loc->name;
            }
        }
        if (empty($data['vendor']) && !empty($data['vendor_id'])) {
            $vendor = Vendor::find($data['vendor_id']);
            if ($vendor) {
                $data['vendor'] = $vendor->name;
            }
        }
        if (empty($data['brand']) && !empty($data['brand_id'])) {
            $brand = Brand::find($data['brand_id']);
            if ($brand) {
                $data['brand'] = $brand->name;
            }
        }

        $fixedAsset->update($data);

        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed Asset berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FixedAsset $fixedAsset)
    {
        $fixedAsset->delete();

        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed Asset berhasil dihapus');
    }
}
