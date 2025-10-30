<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\AssetCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetConditionController extends Controller
{
    public function index()
    {
        $items = AssetCondition::orderBy('name')->paginate(15);
        return view('masters.conditions.index', compact('items'));
    }

    public function create()
    {
        return view('masters.conditions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:asset_conditions,name',
            'slug' => 'nullable|string|max:255|unique:asset_conditions,slug',
            'description' => 'nullable|string',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        AssetCondition::create($data);
        return redirect()->route('masters.conditions.index')->with('success', 'Kondisi berhasil ditambahkan');
    }

    public function edit(AssetCondition $condition)
    {
        return view('masters.conditions.edit', compact('condition'));
    }

    public function update(Request $request, AssetCondition $condition)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:asset_conditions,name,' . $condition->id,
            'slug' => 'nullable|string|max:255|unique:asset_conditions,slug,' . $condition->id,
            'description' => 'nullable|string',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $condition->update($data);
        return redirect()->route('masters.conditions.index')->with('success', 'Kondisi berhasil diperbarui');
    }

    public function destroy(AssetCondition $condition)
    {
        $condition->delete();
        return redirect()->route('masters.conditions.index')->with('success', 'Kondisi berhasil dihapus');
    }
}
