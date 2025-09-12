<div x-data="notifications" class="relative">
    <button @click="isOpen = !isOpen" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <template x-if="unreadCount > 0">
            <span x-text="unreadCount" class="absolute -top-2 -right-2 flex items-center justify-center h-5 w-5 text-xs font-bold text-white bg-red-500 rounded-full"></span>
        </template>
    </button>

    <div x-show="isOpen" @click.away="isOpen = false" x-cloak
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl overflow-hidden z-20">
        <div class="py-2 px-4 text-sm font-semibold text-gray-700 border-b">Notifikasi</div>
        <div class="divide-y max-h-96 overflow-y-auto">
            <template x-for="notification in notifications" :key="notification.id">
                <a @click.prevent="markAsRead(notification)" href="#"
                   class="flex items-start px-4 py-3 hover:bg-gray-100">
                    <div class="ml-2">
                        <p class="text-sm text-gray-700" x-text="notification.data.message"></p>
                        <p class="text-xs text-gray-500" x-text="timeAgo(notification.created_at)"></p>
                    </div>
                </a>
            </template>
            <template x-if="notifications.length === 0">
                <p class="text-sm text-gray-500 text-center py-4">Tidak ada notifikasi baru.</p>
            </template>
        </div>
        {{-- Nanti bisa ditambahkan link "Lihat Semua Notifikasi" di sini --}}
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notifications', () => ({
        isOpen: false,
        notifications: [],
        unreadCount: 0,
        init() {
            this.fetchNotifications();
            // Refresh notifikasi setiap 1 menit
            setInterval(() => {
                this.fetchNotifications();
            }, 60000);
        },
        fetchNotifications() {
            fetch('{{ route('api.notifications.index') }}')
                .then(response => response.json())
                .then(data => {
                    this.notifications = data;
                    this.unreadCount = data.length;
                });
        },
        markAsRead(notification) {
            fetch(`/api/notifications/${notification.id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                // Arahkan ke URL tujuan setelah ditandai terbaca
                window.location.href = notification.data.action_url;
            });
        },
        timeAgo(dateString) {
            // Fungsi sederhana untuk format waktu
            const date = new Date(dateString);
            const seconds = Math.floor((new Date() - date) / 1000);
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + " tahun lalu";
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + " bulan lalu";
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + " hari lalu";
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + " jam lalu";
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + " menit lalu";
            return "Baru saja";
        }
    }));
});
</script>