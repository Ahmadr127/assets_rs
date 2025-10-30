@props(['label' => null, 'name', 'options' => [], 'value' => '', 'required' => false, 'inline' => false])

<div>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    
    <div class="{{ $inline ? 'flex flex-wrap gap-4' : 'space-y-2' }}">
        @foreach($options as $optValue => $optLabel)
            <div class="flex items-center">
                <input type="radio" 
                       id="{{ $name }}_{{ $optValue }}" 
                       name="{{ $name }}" 
                       value="{{ $optValue }}"
                       {{ (string)old($name, $value) === (string)$optValue ? 'checked' : '' }}
                       @if($required) required @endif
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="{{ $name }}_{{ $optValue }}" class="ml-2 block text-sm text-gray-900">
                    {{ $optLabel }}
                </label>
            </div>
        @endforeach
    </div>
    
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
