<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Location;
use App\Models\AssetStatus;
use App\Models\AssetCondition;
use App\Models\Vendor;
use App\Models\Brand;
use App\Models\AssetType;

class DinamicLookupController extends Controller
{
    /**
     * Map entity name to Eloquent model class.
     */
    protected function modelFor(string $entity)
    {
        return [
            'locations' => Location::class,
            'statuses' => AssetStatus::class,
            'conditions' => AssetCondition::class,
            'vendors' => Vendor::class,
            'brands' => Brand::class,
            'types' => AssetType::class,
        ][$entity] ?? null;
    }

    /**
     * GET /masters/lookup/{entity}/search?q=...
     */
    public function search(Request $request, string $entity)
    {
        $model = $this->modelFor($entity);
        if (!$model) {
            return response()->json(['message' => 'Unknown entity'], Response::HTTP_BAD_REQUEST);
        }

        $q = trim((string)$request->query('q', ''));
        $query = $model::query();
        if ($q !== '') {
            $query->where('name', 'like', '%' . $q . '%');
        }
        $rows = $query->orderBy('name')->limit(25)->get(['id','name']);

        return response()->json([
            'data' => $rows,
        ]);
    }

    /**
     * POST /masters/lookup/{entity}
     * body: { name: string }
     */
    public function store(Request $request, string $entity)
    {
        $model = $this->modelFor($entity);
        if (!$model) {
            return response()->json(['message' => 'Unknown entity'], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->validate([
            'name' => ['required','string','min:2'],
        ]);

        $name = trim($data['name']);
        // Try find existing (case-insensitive)
        $existing = $model::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
        if ($existing) {
            return response()->json(['data' => $existing], Response::HTTP_OK);
        }

        $created = $model::create(['name' => $name]);

        return response()->json([
            'data' => $created,
        ], Response::HTTP_CREATED);
    }
}

