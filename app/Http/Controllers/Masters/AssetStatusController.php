<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\AssetStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssetStatusController extends Controller
{
    public function index()
    {
        $items = AssetStatus::orderBy('name')->paginate(15);
        return view('masters.statuses.index', compact('items'));
    }

    public function create()
    {
        return view('masters.statuses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:asset_statuses,name',
            'slug' => 'nullable|string|max:255|unique:asset_statuses,slug',
            'description' => 'nullable|string',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        AssetStatus::create($data);
        return redirect()->route('masters.statuses.index')->with('success', 'Status berhasil ditambahkan');
    }

    public function edit(AssetStatus $status)
    {
        return view('masters.statuses.edit', compact('status'));
    }

    public function update(Request $request, AssetStatus $status)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:asset_statuses,name,' . $status->id,
            'slug' => 'nullable|string|max:255|unique:asset_statuses,slug,' . $status->id,
            'description' => 'nullable|string',
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $status->update($data);
        return redirect()->route('masters.statuses.index')->with('success', 'Status berhasil diperbarui');
    }

    public function destroy(AssetStatus $status)
    {
        $status->delete();
        return redirect()->route('masters.statuses.index')->with('success', 'Status berhasil dihapus');
    }
}
