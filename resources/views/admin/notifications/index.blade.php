@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-full blur opacity-20"></div>
                    <div class="relative flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Alerts
                        </h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            System alerts and updates across ERP
                        </p>
                    </div>
                    @if(!empty($alerts))
                        <span class="mt-2 inline-flex items-center rounded-full bg-gradient-to-r from-error-500 to-error-600 px-4 py-1.5 text-xs font-semibold text-white shadow-md sm:mt-0">
                            {{ count($alerts) }} {{ Str::plural('Alert', count($alerts)) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        @if(isset($userNotifications) && method_exists($userNotifications, 'links') && $userNotifications->isNotEmpty())
        <div class="flex flex-wrap items-center justify-start gap-2 sm:justify-end">
            <button onclick="window.location.reload()" 
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
            
            @if(Route::has('admin.notifications.mark-all-read'))
            <a href="{{ route('admin.notifications.mark-all-read') }}" 
               class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand-500 to-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:from-brand-600 hover:to-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mark All as Read
            </a>
            @endif
            
            @if(Route::has('admin.notifications.mark-all-unread'))
            <a href="{{ route('admin.notifications.mark-all-unread') }}" 
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm px-4 py-2.5 text-sm font-medium text-gray-700 shadow-xs hover:bg-white hover:shadow-sm dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"/>
                </svg>
                Mark All as Unread
            </a>
            @endif
        </div>
        @endif
    </div>

    @php
        // Normalise variables
        $alerts = $alerts ?? [];
        $userNotifications = $userNotifications ?? collect();
        $hasAlerts = !empty($alerts);
        $hasNotifications = $userNotifications->isNotEmpty();
        $totalUnread = $userNotifications->whereNull('read_at')->count();
        $totalRead = $userNotifications->whereNotNull('read_at')->count();
    @endphp

    @if(!$hasAlerts && !$hasNotifications)
        <!-- Empty State - Modern All Clear -->
        <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white/50 backdrop-blur-sm px-6 py-10 text-center shadow-xl dark:border-gray-800 dark:bg-gray-900/50 sm:p-12 lg:p-16">
            <!-- Decorative background -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 h-40 w-40 rounded-full bg-gradient-to-br from-brand-100 to-brand-50 opacity-20 dark:from-brand-900 dark:to-brand-800 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-40 w-40 rounded-full bg-gradient-to-br from-success-100 to-success-50 opacity-20 dark:from-success-900 dark:to-success-800 blur-3xl"></div>
            
            <div class="relative">
                <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-gradient-to-br from-success-100 to-success-50 dark:from-success-900/30 dark:to-success-800/30">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h2 class="mt-8 text-2xl font-bold text-gray-900 dark:text-white">All Clear</h2>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Your notification center is quiet. No system alerts or notifications require your attention at this moment.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <button onclick="window.location.reload()" 
                            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Check Again
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="space-y-10">
            <!-- System Alerts Section - Modern Redesign -->
            @if($hasAlerts)
                <div class="relative overflow-hidden rounded-3xl border border-error-200 bg-gradient-to-br from-error-50/50 to-white p-4 dark:border-error-900/30 dark:from-error-950/20 dark:to-gray-900 sm:p-6 lg:p-8">
                    <!-- Decorative elements -->
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 h-32 w-32 rounded-full bg-gradient-to-br from-error-200 to-error-100 opacity-30 dark:from-error-900 dark:to-error-800 blur-2xl"></div>
                    
                    <div class="relative">
                        <div class="mt-2 grid gap-3">
                            @foreach($alerts as $index => $alert)
                                @php
                                    $message = is_array($alert) ? ($alert['message'] ?? '') : $alert;
                                    $variant = is_array($alert) ? ($alert['variant'] ?? 'error') : 'error';
                                    $title = $variant === 'success' ? 'Good news' : 'System alert';
                                @endphp
                                <x-alert :variant="$variant" :title="$title" :message="$message">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ now()->format('d M Y, H:i') }} • System Generated
                                    </p>
                                </x-alert>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- User Notifications Section - Modern Redesign with Read/Unread -->
            @if($hasNotifications)
                <div class="space-y-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="absolute -inset-1 bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl blur opacity-20"></div>
                                <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-lg">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Your Notifications</h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $totalUnread }} unread</span> · 
                                    <span class="text-gray-500 dark:text-gray-400">{{ $totalRead }} read</span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Filter Tabs -->
                        <div class="flex flex-wrap items-center gap-2 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 justify-start md:justify-end">
                            <button type="button" 
                                    id="show-all-btn"
                                    class="px-4 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 shadow-sm transition-all">
                                All
                            </button>
                            <button type="button" 
                                    id="show-unread-btn"
                                    class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all">
                                Unread
                            </button>
                            <button type="button" 
                                    id="show-read-btn"
                                    class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all">
                                Read
                            </button>
                        </div>
                    </div>

                    <div id="notifications-container" class="grid gap-4">
                        @foreach($userNotifications as $notification)
                            @php
                                $data = $notification->data;
                                $title = $data['title'] ?? 'New Notification';
                                $message = $data['message'] ?? '';
                                $type = $data['type'] ?? 'info';
                                $link = $data['link'] ?? null;
                                $isRead = !is_null($notification->read_at);
                                
                                $typeConfig = [
                                    'success' => [
                                        'gradient' => 'from-success-500 to-success-600',
                                        'light' => 'bg-success-50 dark:bg-success-950/30',
                                        'border' => 'border-success-200 dark:border-success-900',
                                        'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'badge' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300'
                                    ],
                                    'warning' => [
                                        'gradient' => 'from-orange-500 to-orange-600',
                                        'light' => 'bg-orange-50 dark:bg-orange-950/30',
                                        'border' => 'border-orange-200 dark:border-orange-900',
                                        'icon' => 'M12 9v3.75m-1.5-2.25h3M12 15.75h.007v.008H12v-.008z',
                                        'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300'
                                    ],
                                    'error' => [
                                        'gradient' => 'from-error-500 to-error-600',
                                        'light' => 'bg-error-50 dark:bg-error-950/30',
                                        'border' => 'border-error-200 dark:border-error-900',
                                        'icon' => 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                                        'badge' => 'bg-error-100 text-error-700 dark:bg-error-900 dark:text-error-300'
                                    ],
                                    'info' => [
                                        'gradient' => 'from-brand-500 to-brand-600',
                                        'light' => 'bg-brand-50 dark:bg-brand-950/30',
                                        'border' => 'border-brand-200 dark:border-brand-900',
                                        'icon' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
                                        'badge' => 'bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300'
                                    ],
                                ];
                                
                                $config = $typeConfig[$type] ?? $typeConfig['info'];
                                $typeIcon = $config['icon'];
                                $typeGradient = $config['gradient'];
                                $typeBorder = $config['border'];
                                $typeBadge = $config['badge'];
                                
                                $readClass = $isRead ? 'opacity-75' : '';
                                $readBadge = $isRead 
                                    ? '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Read</span>'
                                    : '<span class="inline-flex items-center rounded-full bg-brand-100 px-2.5 py-0.5 text-xs font-medium text-brand-700 dark:bg-brand-900 dark:text-brand-300">Unread</span>';
                            @endphp
                            
                            <div class="notification-item group relative overflow-hidden rounded-2xl border {{ $typeBorder }} bg-white p-6 shadow-sm transition-all hover:shadow-lg dark:bg-gray-900 {{ $readClass }}" 
                                 data-read="{{ $isRead ? 'true' : 'false' }}">
                                <!-- Decorative gradient line -->
                                <div class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b {{ $typeGradient }}"></div>
                                
                                <div class="flex flex-col sm:flex-row sm:items-start gap-4 pl-3">
                                    <!-- Icon with gradient background -->
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $typeGradient }} text-white shadow-md">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeIcon }}" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <div class="flex items-center gap-3 flex-wrap">
                                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeBadge }}">
                                                        {{ ucfirst($type) }}
                                                    </span>
                                                    {!! $readBadge !!}
                                                </div>
                                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $message }}</p>
                                            </div>
                                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap bg-gray-50 dark:bg-gray-800/50 px-3 py-1.5 rounded-full">
                                                {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                        
                                        @if($link)
                                            <div class="mt-4 flex items-center gap-4">
                                                <a href="{{ $link }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                                    View Details
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex flex-shrink-0 items-start gap-2">
                                        @if(Route::has('admin.notifications.mark-read') && !$isRead)
                                        <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST" class="mark-read-form">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm p-2.5 text-gray-500 shadow-xs hover:bg-white hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-brand-400 transition-all group"
                                                    title="Mark as read">
                                                <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if(Route::has('admin.notifications.mark-unread') && $isRead)
                                        <form action="{{ route('admin.notifications.mark-unread', $notification->id) }}" method="POST" class="mark-unread-form">
                                            @csrf
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm p-2.5 text-gray-500 shadow-xs hover:bg-white hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800/80 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-all group"
                                                    title="Mark as unread">
                                                <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3z"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($userNotifications, 'links'))
                        <div class="mt-8">
                            {{ $userNotifications->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const allBtn = document.getElementById('show-all-btn');
    const unreadBtn = document.getElementById('show-unread-btn');
    const readBtn = document.getElementById('show-read-btn');
    const notificationItems = document.querySelectorAll('.notification-item');
    
    function setActiveFilter(activeBtn) {
        [allBtn, unreadBtn, readBtn].forEach(btn => {
            btn.classList.remove('bg-white', 'dark:bg-gray-900', 'shadow-sm', 'text-gray-700', 'dark:text-gray-300');
            btn.classList.add('text-gray-600', 'dark:text-gray-400');
        });
        activeBtn.classList.add('bg-white', 'dark:bg-gray-900', 'shadow-sm', 'text-gray-700', 'dark:text-gray-300');
        activeBtn.classList.remove('text-gray-600', 'dark:text-gray-400');
    }
    
    function filterNotifications(filter) {
        notificationItems.forEach(item => {
            const isRead = item.dataset.read === 'true';
            
            if (filter === 'all') {
                item.style.display = '';
            } else if (filter === 'unread') {
                item.style.display = isRead ? 'none' : '';
            } else if (filter === 'read') {
                item.style.display = isRead ? '' : 'none';
            }
        });
    }
    
    if (allBtn) {
        allBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveFilter(this);
            filterNotifications('all');
        });
    }
    
    if (unreadBtn) {
        unreadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveFilter(this);
            filterNotifications('unread');
        });
    }
    
    if (readBtn) {
        readBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveFilter(this);
            filterNotifications('read');
        });
    }
    
    // Handle mark as read/unread via AJAX for better UX
    function handleFormSubmit(form, successCallback) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = this.action;
            const method = this.method;
            
            fetch(action, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successCallback();
                    // Optional: Show toast notification
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback: submit form normally
                form.submit();
            });
        });
    }
    
    // Mark as read forms
    document.querySelectorAll('.mark-read-form').forEach(form => {
        handleFormSubmit(form, function() {
            location.reload(); // Simple reload to update UI
        });
    });
    
    // Mark as unread forms
    document.querySelectorAll('.mark-unread-form').forEach(form => {
        handleFormSubmit(form, function() {
            location.reload(); // Simple reload to update UI
        });
    });
});
</script>
@endpush
@endsection
