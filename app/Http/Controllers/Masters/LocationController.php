<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $items = Location::orderBy('name')->paginate(15);
        return view('masters.locations.index', compact('items'));
    }

    public function create()
    {
        return view('masters.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'code' => 'nullable|string|max:50|unique:locations,code',
            'description' => 'nullable|string',
        ]);
        Location::create($data);
        return redirect()->route('masters.locations.index')->with('success', 'Lokasi berhasil ditambahkan');
    }

    public function edit(Location $location)
    {
        return view('masters.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'code' => 'nullable|string|max:50|unique:locations,code,' . $location->id,
            'description' => 'nullable|string',
        ]);
        $location->update($data);
        return redirect()->route('masters.locations.index')->with('success', 'Lokasi berhasil diperbarui');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('masters.locations.index')->with('success', 'Lokasi berhasil dihapus');
    }
}
