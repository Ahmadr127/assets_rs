<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\AssetType;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function index()
    {
        $items = AssetType::orderBy('name')->paginate(15);
        return view('masters.types.index', compact('items'));
    }

    public function create()
    {
        return view('masters.types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:asset_types,name',
            'code' => 'nullable|string|max:50|unique:asset_types,code',
            'description' => 'nullable|string',
        ]);
        AssetType::create($data);
        return redirect()->route('masters.types.index')->with('success', 'Tipe asset berhasil ditambahkan');
    }

    public function edit(AssetType $type)
    {
        return view('masters.types.edit', compact('type'));
    }

    public function update(Request $request, AssetType $type)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:asset_types,name,' . $type->id,
            'code' => 'nullable|string|max:50|unique:asset_types,code,' . $type->id,
            'description' => 'nullable|string',
        ]);
        $type->update($data);
        return redirect()->route('masters.types.index')->with('success', 'Tipe asset berhasil diperbarui');
    }

    public function destroy(AssetType $type)
    {
        $type->delete();
        return redirect()->route('masters.types.index')->with('success', 'Tipe asset berhasil dihapus');
    }
}
