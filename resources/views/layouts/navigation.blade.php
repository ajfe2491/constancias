<div class="menu p-4 min-h-full bg-base-200 text-base-content flex flex-col transition-all duration-300"
    :class="sidebarOpen ? 'w-64' : 'w-20'">

    <!-- Sidebar Header -->
    <div class="mb-6 px-2 flex items-center justify-between gap-3 overflow-hidden whitespace-nowrap">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-primary flex-shrink-0 flex items-center justify-center text-primary-content font-bold text-xl shadow-lg shadow-primary/30">
                C
            </div>
            <div class="transition-opacity duration-300" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">
                <h1 class="font-bold text-lg tracking-tight">Constancias</h1>
                <p class="text-xs opacity-60 font-medium">Panel Administrativo</p>
            </div>
        </div>

        <!-- Toggle Button -->
        <button @click="toggleSidebar()" class="btn btn-ghost btn-sm btn-square" :class="sidebarOpen ? '' : 'hidden'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Collapsed Toggle Button (Centered when closed) -->
    <div class="px-2 mb-6 flex justify-center" :class="sidebarOpen ? 'hidden' : ''">
        <button @click="toggleSidebar()" class="btn btn-ghost btn-sm btn-square">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Menu Items -->
    <ul class="space-y-1 flex-1">
        <li class="menu-title px-2 uppercase text-xs font-bold opacity-50 tracking-wider mb-2 transition-opacity duration-300"
            :class="sidebarOpen ? 'opacity-50' : 'opacity-0 hidden'">
            Principal
        </li>
        <li>
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-4 text-sm {{ request()->routeIs('dashboard') ? 'active font-medium' : '' }}"
                :class="sidebarOpen ? '' : 'justify-center'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span :class="sidebarOpen ? 'block' : 'hidden'">Dashboard</span>
            </a>
        </li>

        <li class="menu-title px-2 uppercase text-xs font-bold opacity-50 tracking-wider mt-6 mb-2 transition-opacity duration-300"
            :class="sidebarOpen ? 'opacity-50' : 'opacity-0 hidden'">
            Gestión
        </li>
        <li>
            <a href="{{ route('events.index') }}"
                class="flex items-center gap-4 text-sm {{ request()->routeIs('events.*') ? 'active font-medium' : '' }}"
                :class="sidebarOpen ? '' : 'justify-center'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span :class="sidebarOpen ? 'block' : 'hidden'">Eventos</span>
            </a>
        </li>
        <li>
            <a href="{{ route('certificate-sending.index') }}"
                class="flex items-center gap-4 text-sm {{ request()->routeIs('certificate-sending.*') ? 'active font-medium' : '' }}"
                :class="sidebarOpen ? '' : 'justify-center'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span :class="sidebarOpen ? 'block' : 'hidden'">Envíos</span>
            </a>
        </li>
        <li>
            <a href="#" class="flex items-center gap-4 text-sm" :class="sidebarOpen ? '' : 'justify-center'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span :class="sidebarOpen ? 'block' : 'hidden'">Participantes</span>
            </a>
        </li>
        <li>
            <a href="{{ route('document-configurations.index') }}"
                class="flex items-center gap-4 text-sm {{ request()->routeIs('document-configurations.*') ? 'active font-medium' : '' }}"
                :class="sidebarOpen ? '' : 'justify-center'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span :class="sidebarOpen ? 'block' : 'hidden'">Configuración</span>
            </a>
        </li>
    </ul>

    <!-- Footer Info -->
    <div class="mt-auto pt-6 border-t border-base-300/50">
        <div class="bg-base-100 rounded-xl p-4 border border-base-300/50 transition-all duration-300"
            :class="sidebarOpen ? '' : 'p-2'">



            <button @click="toggle()" class="btn btn-ghost btn-sm w-full mb-3 gap-3"
                :class="sidebarOpen ? 'justify-start' : 'justify-center px-0'">
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <span :class="sidebarOpen ? 'block' : 'hidden'" x-text="dark ? 'Modo Claro' : 'Modo Oscuro'"></span>
            </button>

            <div class="flex items-center gap-3 mb-2" :class="sidebarOpen ? '' : 'justify-center'">
                <div class="avatar online">
                    <div
                        class="w-8 rounded-full bg-neutral-focus text-neutral-content flex items-center justify-center">
                        <span class="text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                </div>
                <div class="overflow-hidden transition-all duration-300"
                    :class="sidebarOpen ? 'w-auto opacity-100' : 'w-0 opacity-0'">
                    <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] opacity-60 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
        <p class="text-[10px] text-center opacity-40 mt-4 transition-opacity duration-300"
            :class="sidebarOpen ? 'opacity-40' : 'opacity-0 hidden'">
            v{{ app()->version() }} © {{ date('Y') }}
        </p>
    </div>
</div>