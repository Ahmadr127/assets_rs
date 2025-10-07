<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FixedAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FixedAsset::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Condition filter
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
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

        return view('fixed-assets.index', compact('fixedAssets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fixed-assets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), FixedAsset::rules());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['harus_dicek_fisik'] = $request->has('harus_dicek_fisik');

        FixedAsset::create($data);

        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed Asset berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FixedAsset $fixedAsset)
    {
        return view('fixed-assets.show', compact('fixedAsset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FixedAsset $fixedAsset)
    {
        return view('fixed-assets.edit', compact('fixedAsset'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $validator = Validator::make($request->all(), FixedAsset::rules($fixedAsset->id));

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['harus_dicek_fisik'] = $request->has('harus_dicek_fisik');

        $fixedAsset->update($data);

        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed Asset berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FixedAsset $fixedAsset)
    {
        $fixedAsset->delete();

        return redirect()->route('fixed-assets.index')
            ->with('success', 'Fixed Asset berhasil dihapus.');
    }
}
