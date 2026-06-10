@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-350 bg-white text-gray-900 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-emerald-500 dark:focus:ring-emerald-500/20 rounded-md shadow-sm']) }}>
