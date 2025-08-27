<div class="bg-white rounded shadow p-4">
    <nav class="space-y-2" x-data="{ dataMasterOpen: {{ request()->routeIs('superadmin.materials.*') || request()->routeIs('superadmin.work-items.*') ? 'true' : 'false' }} }">
        <a href="{{ route('superadmin.dashboard') }}"
           class="block px-4 py-2 text-sm rounded {{ request()->routeIs('superadmin.dashboard') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
            Dashboard
        </a>
        <a href="{{ route('superadmin.tokens.index') }}"
           class="block px-4 py-2 text-sm rounded {{ request()->routeIs('superadmin.tokens.index') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
            Manajemen Token
        </a>

        {{-- TAMBAHKAN: Menu expand/collapse untuk Data Master --}}
        <div>
            <button @click="dataMasterOpen = !dataMasterOpen"
                    class="w-full flex justify-between items-center px-4 py-2 text-sm rounded {{ request()->routeIs('superadmin.materials.*') || request()->routeIs('superadmin.work-items.*') ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                <span>Manajemen Data Master</span>
                <svg class="w-4 h-4 transition-transform" :class="{'rotate-90': dataMasterOpen}" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="dataMasterOpen" class="pl-4 mt-2 space-y-2 border-l-2 border-gray-200">
                <a href="{{ route('superadmin.materials.index') }}"
                   class="block px-4 py-2 text-sm rounded {{ request()->routeIs('superadmin.materials.*') ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Manajemen Material
                </a>
                <a href="{{ route('superadmin.work-items.index') }}"
                   class="block px-4 py-2 text-sm rounded {{ request()->routeIs('superadmin.work-items.*') ? 'text-blue-600 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Manajemen Pekerjaan
                </a>
            </div>
        </div>
    </nav>
</div>