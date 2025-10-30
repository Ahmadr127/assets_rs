<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2">
    <!-- Success Toast -->
    @if(session('success'))
        @php $successId = 'toast-success-' . uniqid(); @endphp
        <div id="{{ $successId }}" 
             class="flex items-center w-full max-w-xs p-4 text-sm rounded-lg border-l-4 shadow-lg bg-green-500 border-green-600 text-white transform translate-x-full opacity-0 transition-all duration-300 ease-in-out"
             role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 mr-3">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
            <button type="button" 
                    class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-black hover:bg-opacity-10 inline-flex h-6 w-6 items-center justify-center"
                    onclick="closeToast('{{ $successId }}')">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    @endif
    
    <!-- Error Toast -->
    @if(session('error'))
        @php $errorId = 'toast-error-' . uniqid(); @endphp
        <div id="{{ $errorId }}" 
             class="flex items-center w-full max-w-xs p-4 text-sm rounded-lg border-l-4 shadow-lg bg-red-500 border-red-600 text-white transform translate-x-full opacity-0 transition-all duration-300 ease-in-out"
             role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 mr-3">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="flex-1 text-sm font-medium">{{ session('error') }}</div>
            <button type="button" 
                    class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-black hover:bg-opacity-10 inline-flex h-6 w-6 items-center justify-center"
                    onclick="closeToast('{{ $errorId }}')">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    @endif
    
    <!-- Warning Toast -->
    @if(session('warning'))
        @php $warningId = 'toast-warning-' . uniqid(); @endphp
        <div id="{{ $warningId }}" 
             class="flex items-center w-full max-w-xs p-4 text-sm rounded-lg border-l-4 shadow-lg bg-yellow-500 border-yellow-600 text-white transform translate-x-full opacity-0 transition-all duration-300 ease-in-out"
             role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 mr-3">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="flex-1 text-sm font-medium">{{ session('warning') }}</div>
            <button type="button" 
                    class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-black hover:bg-opacity-10 inline-flex h-6 w-6 items-center justify-center"
                    onclick="closeToast('{{ $warningId }}')">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    @endif
    
    <!-- Info Toast -->
    @if(session('info'))
        @php $infoId = 'toast-info-' . uniqid(); @endphp
        <div id="{{ $infoId }}" 
             class="flex items-center w-full max-w-xs p-4 text-sm rounded-lg border-l-4 shadow-lg bg-blue-500 border-blue-600 text-white transform translate-x-full opacity-0 transition-all duration-300 ease-in-out"
             role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 mr-3">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="flex-1 text-sm font-medium">{{ session('info') }}</div>
            <button type="button" 
                    class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-black hover:bg-opacity-10 inline-flex h-6 w-6 items-center justify-center"
                    onclick="closeToast('{{ $infoId }}')">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    @endif
    
    <!-- Validation Errors -->
    @if($errors->any())
        @foreach($errors->all() as $index => $error)
            @php $errorValidationId = 'toast-validation-' . $index . '-' . uniqid(); @endphp
            <div id="{{ $errorValidationId }}" 
                 class="flex items-center w-full max-w-xs p-4 text-sm rounded-lg border-l-4 shadow-lg bg-red-500 border-red-600 text-white transform translate-x-full opacity-0 transition-all duration-300 ease-in-out"
                 role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 mr-3">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="flex-1 text-sm font-medium">{{ $error }}</div>
                <button type="button" 
                        class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-black hover:bg-opacity-10 inline-flex h-6 w-6 items-center justify-center"
                        onclick="closeToast('{{ $errorValidationId }}')">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        @endforeach
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show all existing toasts with animation
    const toasts = document.querySelectorAll('[id^="toast-"]');
    toasts.forEach((toast, index) => {
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                closeToast(toast.id);
            }, 5000);
        }, index * 200); // Stagger animation for multiple toasts
    });
});

// Close toast function
window.closeToast = function(toastId) {
    const toast = typeof toastId === 'string' ? document.getElementById(toastId) : toastId;
    if (toast) {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
};

// Global toast functions for dynamic creation
window.showToast = function(type, message, duration = 5000) {
    const container = document.getElementById('toast-container');
    const toastId = 'toast-dynamic-' + Date.now() + Math.random().toString(36).substr(2, 9);
    
    const typeClasses = {
        'success': 'bg-green-500 border-green-600 text-white',
        'error': 'bg-red-500 border-red-600 text-white',
        'warning': 'bg-yellow-500 border-yellow-600 text-white',
        'info': 'bg-blue-500 border-blue-600 text-white'
    };
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-times-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    const toastClass = typeClasses[type] || typeClasses['success'];
    const toastIcon = icons[type] || icons['success'];
    
    const toastHTML = `
        <div id="${toastId}" 
             class="flex items-center w-full max-w-xs p-4 text-sm rounded-lg border-l-4 shadow-lg transform translate-x-full opacity-0 transition-all duration-300 ease-in-out ${toastClass}"
             role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 mr-3">
                <i class="${toastIcon}"></i>
            </div>
            <div class="flex-1 text-sm font-medium">${message}</div>
            <button type="button" 
                    class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-black hover:bg-opacity-10 inline-flex h-6 w-6 items-center justify-center"
                    onclick="closeToast('${toastId}')">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', toastHTML);
    
    // Show with animation
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }
    }, 100);
    
    // Auto hide
    setTimeout(() => {
        closeToast(toastId);
    }, duration);
};

// CRUD Success Messages
window.showCrudSuccess = function(action, item = 'Data') {
    const messages = {
        'create': `${item} berhasil ditambahkan`,
        'update': `${item} berhasil diperbarui`, 
        'delete': `${item} berhasil dihapus`,
        'restore': `${item} berhasil dipulihkan`
    };
    
    showToast('success', messages[action] || 'Operasi berhasil');
};

window.showCrudError = function(action, item = 'Data') {
    const messages = {
        'create': `Gagal menambahkan ${item}`,
        'update': `Gagal memperbarui ${item}`,
        'delete': `Gagal menghapus ${item}`,
        'restore': `Gagal memulihkan ${item}`
    };
    
    showToast('error', messages[action] || 'Operasi gagal');
};
</script>
@endpush
