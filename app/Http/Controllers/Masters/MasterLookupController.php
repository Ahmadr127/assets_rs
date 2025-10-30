<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Location;
use App\Models\AssetStatus;
use App\Models\AssetCondition;
use App\Models\Vendor;
use App\Models\Brand;
use App\Models\AssetType;

class MasterLookupController extends Controller
{
    protected function resolveModel(string $entity)
    {
        return match ($entity) {
            'locations' => [Location::class, ['name']],
            'statuses' => [AssetStatus::class, ['name','slug']],
            'conditions' => [AssetCondition::class, ['name','slug']],
            'vendors' => [Vendor::class, ['name']],
            'brands' => [Brand::class, ['name']],
            'types' => [AssetType::class, ['name']],
            default => null,
        };
    }

    public function search(Request $request, string $entity)
    {
        [$model, $fields] = $this->resolveModel($entity) ?? [null, null];
        abort_unless($model, 404);

        $q = trim((string)$request->get('q', ''));
        $query = $model::query()->select(['id','name']);
        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }
        $items = $query->orderBy('name')->limit(10)->get();

        Log::info('MasterLookup search', [
            'entity' => $entity,
            'query' => $q,
            'count' => $items->count(),
        ]);

        return response()->json([
            'data' => $items,
        ]);
    }

    public function store(Request $request, string $entity)
    {
        [$model, $fields] = $this->resolveModel($entity) ?? [null, null];
        abort_unless($model, 404);

        Log::info('MasterLookup store request', [
            'entity' => $entity,
            'payload' => $request->all(),
            'user_id' => optional($request->user())->id,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $name = trim($validated['name']);

        // Return existing if name already exists (case-insensitive)
        $existing = $model::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->select(['id','name'])
            ->first();
        if ($existing) {
            Log::info('MasterLookup existing found', [
                'entity' => $entity,
                'name' => $name,
                'existing_id' => $existing->id,
            ]);
            return response()->json(['data' => $existing], 200);
        }

        // For statuses/conditions, also set slug
        if (in_array('slug', $fields, true)) {
            $slug = Str::slug($name);
            // ensure unique slug
            $suffix = 1;
            $base = $slug ?: Str::random(6);
            while ($model::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $data = ['name' => $name, 'slug' => $slug];
        } else {
            $data = ['name' => $name];
        }

        try {
            $created = $model::create($data);
        } catch (\Throwable $e) {
            Log::error('MasterLookup create failed', [
                'entity' => $entity,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal membuat data: '.$e->getMessage()], 422);
        }

        Log::info('MasterLookup created', [
            'entity' => $entity,
            'id' => $created->id,
            'name' => $created->name,
        ]);

        return response()->json([
            'data' => ['id' => $created->id, 'name' => $created->name],
        ], 201);
    }
}
