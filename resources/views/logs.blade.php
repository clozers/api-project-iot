<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0">
            <div>
                <h2 class="font-semibold text-xl text-slate-100 leading-tight">
                    {{ __('Sensor Communication Logs') }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">Search, sort, analyze, and export historical MQ-2 and KY-026 readings</p>
            </div>
            <div>
                <a href="{{ route('logs.export', ['search' => $search, 'sort_by' => $sortBy, 'order' => $order]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-md transition duration-150 ease-in-out border border-emerald-500/30 gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export Logs to CSV</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Glassmorphism Styles -->
    <style>
        .glass-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(51, 65, 85, 0.4);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Search Filter Bar -->
            <div class="glass-card rounded-xl p-5 text-slate-100">
                <form method="GET" action="{{ route('logs.index') }}" class="flex flex-col md:flex-row md:items-center gap-4">
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="order" value="{{ $order }}">

                    <div class="flex-grow">
                        <label for="search-input" class="sr-only">Search</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input id="search-input" type="text" name="search" value="{{ $search }}" placeholder="Search by Gas ppm value, ID, or flame status ('safe', 'fire', 'true')..." class="block w-full rounded-lg bg-slate-900 border-slate-750 pl-10 text-sm text-slate-200 placeholder-slate-500 focus:border-slate-600 focus:ring-0">
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold rounded-lg border border-slate-700 transition w-full md:w-auto">
                            Apply Filter
                        </button>
                        
                        @if($search)
                            <a href="{{ route('logs.index', ['sort_by' => $sortBy, 'order' => $order]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900/50 hover:bg-slate-800/80 text-slate-400 text-sm font-semibold rounded-lg border border-slate-800 transition w-full md:w-auto">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Sensor Logs Data Table -->
            <div class="glass-card rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-900 text-slate-400 uppercase text-xs border-b border-slate-800">
                            <tr>
                                <!-- ID Column Sort Header -->
                                <th class="py-3.5 px-6 font-bold">
                                    <a href="{{ route('logs.index', ['search' => $search, 'sort_by' => 'id', 'order' => ($sortBy === 'id' && $order === 'desc') ? 'asc' : 'desc']) }}" class="flex items-center space-x-1.5 hover:text-slate-100 transition">
                                        <span>ID</span>
                                        @if($sortBy === 'id')
                                            <span class="text-emerald-400 text-[10px]">{{ $order === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-600 text-[10px]">▼</span>
                                        @endif
                                    </a>
                                </th>
                                <!-- Gas Value Column Sort Header -->
                                <th class="py-3.5 px-6 font-bold">
                                    <a href="{{ route('logs.index', ['search' => $search, 'sort_by' => 'gas_value', 'order' => ($sortBy === 'gas_value' && $order === 'desc') ? 'asc' : 'desc']) }}" class="flex items-center space-x-1.5 hover:text-slate-100 transition">
                                        <span>Gas Value</span>
                                        @if($sortBy === 'gas_value')
                                            <span class="text-emerald-400 text-[10px]">{{ $order === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-600 text-[10px]">▼</span>
                                        @endif
                                    </a>
                                </th>
                                <!-- Flame Status Column Sort Header -->
                                <th class="py-3.5 px-6 font-bold">
                                    <a href="{{ route('logs.index', ['search' => $search, 'sort_by' => 'flame_detected', 'order' => ($sortBy === 'flame_detected' && $order === 'desc') ? 'asc' : 'desc']) }}" class="flex items-center space-x-1.5 hover:text-slate-100 transition">
                                        <span>Flame Status</span>
                                        @if($sortBy === 'flame_detected')
                                            <span class="text-emerald-400 text-[10px]">{{ $order === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-600 text-[10px]">▼</span>
                                        @endif
                                    </a>
                                </th>
                                <!-- Timestamp Column Sort Header -->
                                <th class="py-3.5 px-6 font-bold">
                                    <a href="{{ route('logs.index', ['search' => $search, 'sort_by' => 'created_at', 'order' => ($sortBy === 'created_at' && $order === 'desc') ? 'asc' : 'desc']) }}" class="flex items-center space-x-1.5 hover:text-slate-100 transition">
                                        <span>Timestamp</span>
                                        @if($sortBy === 'created_at')
                                            <span class="text-emerald-400 text-[10px]">{{ $order === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-600 text-[10px]">▼</span>
                                        @endif
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/30">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-800/15 transition duration-100">
                                    <td class="py-3.5 px-6 font-medium text-slate-400">#{{ $log->id }}</td>
                                    <td class="py-3.5 px-6">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-bold text-slate-100">{{ $log->gas_value }}</span>
                                            <span class="text-xs text-slate-500">ppm</span>
                                            @if($log->gas_value > 1500)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-950/60 text-amber-400 border border-amber-800/30">THRESHOLD EXCEEDED</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6">
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $log->flame_detected ? 'bg-red-950/45 text-red-400 border border-red-500/20' : 'bg-emerald-950/45 text-emerald-400 border border-emerald-500/20' }}">
                                            {{ $log->flame_detected ? 'FIRE DETECTED' : 'SAFE' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-6 text-slate-400 text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }} <span class="text-slate-600 text-[10px] ml-1">({{ $log->created_at->diffForHumans() }})</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-500">No sensor log events match the query criteria in MySQL database.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Custom dark pagination block -->
                @if($logs->hasPages())
                    <div class="px-6 py-4 bg-slate-900/60 border-t border-slate-800/80 pagination-dark">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
    
    <!-- Inline override to fit Laravel's default Tailwind paginator into a premium dark theme -->
    <style>
        .pagination-dark nav {
            background-color: transparent !important;
        }
        .pagination-dark nav div {
            background-color: transparent !important;
            border-color: transparent !important;
            color: #94a3b8 !important;
        }
        .pagination-dark nav a, .pagination-dark nav span {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
            border-radius: 0.375rem;
            margin: 0 0.125rem;
        }
        .pagination-dark nav span.bg-gray-100 {
            background-color: #10b981 !important;
            color: #0f172a !important;
            font-weight: bold;
        }
        .pagination-dark nav a:hover {
            background-color: #1e293b !important;
            border-color: #475569 !important;
            color: #ffffff !important;
        }
    </style>
</x-app-layout>
