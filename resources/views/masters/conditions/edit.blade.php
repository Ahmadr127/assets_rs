@extends('layouts.app')

@section('title', 'Edit Kondisi')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Kondisi</h1>
        <a href="{{ route('masters.conditions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase bg-white hover:bg-gray-50">Kembali</a>
    </div>

    <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
        <form action="{{ route('masters.conditions.update', $condition) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <x-form.input name="name" label="Nama" :value="$condition->name" required autofocus />
            <x-form.input name="slug" label="Slug" :value="$condition->slug" placeholder="Opsional, otomatis dari nama jika dikosongkan" />
            <x-form.textarea name="description" label="Deskripsi" rows="4" :value="$condition->description" />

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('masters.conditions.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-xs font-semibold">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
