<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $items = Brand::orderBy('name')->paginate(15);
        return view('masters.brands.index', compact('items'));
    }

    public function create()
    {
        return view('masters.brands.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'code' => 'nullable|string|max:50|unique:brands,code',
            'description' => 'nullable|string',
        ]);
        Brand::create($data);
        return redirect()->route('masters.brands.index')->with('success', 'Brand berhasil ditambahkan');
    }

    public function edit(Brand $brand)
    {
        return view('masters.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'code' => 'nullable|string|max:50|unique:brands,code,' . $brand->id,
            'description' => 'nullable|string',
        ]);
        $brand->update($data);
        return redirect()->route('masters.brands.index')->with('success', 'Brand berhasil diperbarui');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return redirect()->route('masters.brands.index')->with('success', 'Brand berhasil dihapus');
    }
}
