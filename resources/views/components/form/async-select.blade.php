@props([
    'label' => null,
    'name',            // hidden field name for id, e.g., location_id
    'entity',          // lookup entity: locations|statuses|conditions|vendors|brands|types
    'value' => '',     // current selected id
    'text' => '',      // current selected display text
    'placeholder' => 'Ketik untuk mencari atau tambah baru...',
    'required' => false
])

<div x-data="asyncSelect({
        entity: '{{ $entity }}',
        name: '{{ $name }}',
        initialId: '{{ (string)old($name, $value) }}',
        initialText: `{{ old($name . '_text', $text) }}`,
        placeholder: `{{ $placeholder }}`,
        searchUrl: '{{ route('masters.lookup.search', ['entity' => $entity]) }}',
        createUrl: '{{ route('masters.lookup.store', ['entity' => $entity]) }}'
    })" x-init="initWatchers()" class="relative">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" x-model="selectedId" @if($required) required @endif>
    <input type="hidden" name="{{ $name }}_text" x-model="query">
    <input type="text"
           x-model="query"
           @input.debounce.300ms="search()"
           @keydown.enter.prevent="createOrSelect()"
           @blur="autoSelectExact()"
           :placeholder="placeholder"
           class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

    <div x-show="open" @click.away="open=false" class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-56 overflow-auto">
        <template x-if="loading">
            <div class="p-3 text-sm text-gray-500">Memuat...</div>
        </template>
        <ul>
            <template x-for="item in results" :key="item.id">
                <li>
                    <button type="button"
                            class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm"
                            @click="select(item)">
                        <span x-text="item.name"></span>
                    </button>
                </li>
            </template>
        </ul>
    </div>

    <template x-if="error">
        <p class="mt-1 text-sm text-red-600" x-text="error"></p>
    </template>
    
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <script>
        function asyncSelect(cfg) {
            return {
                name: cfg.name,
                entity: cfg.entity,
                searchUrl: cfg.searchUrl,
                createUrl: cfg.createUrl,
                selectedId: cfg.initialId || '',
                query: cfg.initialText || '',
                placeholder: cfg.placeholder || '',
                open: false,
                loading: false,
                submitting: false,
                results: [],
                error: '',
                // normalize helper
                norm(s){ return (s || '').toString().trim().toLowerCase(); },
                // watch query changes to keep validation accurate
                initWatchers(){
                    this.$watch('query', (val) => {
                        // If user starts typing something different than current selected text, clear the selected id
                        const selected = this.results.find(r => r.id == this.selectedId);
                        if (!selected || this.norm(selected?.name) !== this.norm(val)) {
                            this.selectedId = '';
                        }
                        this.error = '';
                    });
                },
                // No submit interception; backend will resolve/create based on *_text when *_id empty
                // Form handler removed - let form submit naturally
                async search() {
                    this.error = '';
                    if (!this.query) { this.results = []; this.open = false; return; }
                    this.loading = true;
                    this.open = true;
                    try {
                        const url = new URL(this.searchUrl, window.location.origin);
                        url.searchParams.set('q', this.query);
                        const res = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        const data = await res.json();
                        this.results = data.data || [];
                    } catch (e) {
                        this.error = 'Gagal memuat data';
                    } finally {
                        this.loading = false;
                    }
                },
                select(item) {
                    this.selectedId = item.id;
                    this.query = item.name;
                    this.open = false;
                },
                async autoSelectExact(){
                    // If there's an exact match, select it. If results are empty, try searching first.
                    const q = this.norm(this.query);
                    if (!q) return;
                    if (!this.results || this.results.length === 0) {
                        await this.search();
                    }
                    const exact = this.results.find(r => this.norm(r.name) === q);
                    if (exact) this.select(exact);
                },
                async createOrSelect() {
                    // if there is an exact match in results (case insensitive), select it
                    const queryLower = this.norm(this.query);
                    const exact = this.results.find(r => (r.name || '').toLowerCase().trim() === queryLower);
                    if (exact) { this.select(exact); return; }
                    
                    // create new only if query has minimum length
                    if (!this.query || this.query.trim().length < 2) {
                        this.error = 'Minimal 2 karakter untuk menambah data baru';
                        return;
                    }
                    
                    this.loading = true;
                    this.error = '';
                    try {
                        const res = await fetch(this.createUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ name: this.query.trim() })
                        });
                        if (!res.ok) {
                            let msg = 'Request failed';
                            try { const err = await res.json(); msg = (err.message || JSON.stringify(err)); } catch(e) {}
                            throw new Error(msg);
                        }
                        const data = await res.json();
                        this.select(data.data);
                    } catch (e) {
                        this.error = 'Gagal menambah data';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</div>
