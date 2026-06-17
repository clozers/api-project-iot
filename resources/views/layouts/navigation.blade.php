<nav x-data="{ open: false }" class="bg-slate-900 border-b border-slate-800">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="font-bold text-lg text-slate-100 tracking-wider">SMART SAFETY</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-300 active:text-emerald-400 hover:text-white border-emerald-500">
                        {{ __('ui.dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('logs.index')" :active="request()->routeIs('logs.index')" class="text-slate-300 active:text-emerald-400 hover:text-white border-emerald-500">
                        {{ __('ui.sensor_logs') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">

                <!-- Language Switcher -->
                <div class="flex items-center rounded-lg overflow-hidden border border-slate-700 text-xs font-bold">
                    <a href="{{ route('lang.switch', 'id') }}"
                       class="px-2.5 py-1.5 transition {{ app()->getLocale() === 'id' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white' }}"
                       title="Bahasa Indonesia">
                        🇮🇩 ID
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="px-2.5 py-1.5 transition {{ app()->getLocale() === 'en' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white' }}"
                       title="English">
                        🇬🇧 EN
                    </a>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-400 bg-slate-900 hover:text-slate-200 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-slate-300 hover:bg-slate-800 hover:text-white">
                            {{ __('ui.profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="text-slate-300 hover:bg-slate-800 hover:text-white">
                                {{ __('ui.log_out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-300 hover:bg-slate-800 focus:outline-none focus:bg-slate-800 focus:text-slate-300 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-slate-900 border-t border-slate-850">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-300 hover:text-white hover:bg-slate-800">
                {{ __('ui.dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('logs.index')" :active="request()->routeIs('logs.index')" class="text-slate-300 hover:text-white hover:bg-slate-800">
                {{ __('ui.sensor_logs') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-slate-800">
            <div class="px-4">
                <div class="font-medium text-base text-slate-300">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <!-- Mobile Language Switcher -->
            <div class="px-4 mt-3 flex gap-2">
                <a href="{{ route('lang.switch', 'id') }}"
                   class="flex-1 text-center py-1.5 text-xs font-bold rounded-lg transition {{ app()->getLocale() === 'id' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                    🇮🇩 Indonesia
                </a>
                <a href="{{ route('lang.switch', 'en') }}"
                   class="flex-1 text-center py-1.5 text-xs font-bold rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 border border-slate-700' }}">
                    🇬🇧 English
                </a>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-slate-400 hover:text-white hover:bg-slate-800">
                    {{ __('ui.profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();"
                            class="text-slate-400 hover:text-white hover:bg-slate-800">
                        {{ __('ui.log_out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
