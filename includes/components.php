<?php
// Small reusable UI building blocks so every module page looks consistent.

function kpi_card(string $label, string $value, string $sublabel = '', string $subClass = 'text-slate-500'): string {
    return '<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <p class="text-xs font-semibold text-slate-500 mb-2">' . e($label) . '</p>
        <p class="text-2xl font-extrabold text-slate-900">' . e($value) . '</p>
        ' . ($sublabel ? '<p class="text-xs font-bold mt-1 ' . e($subClass) . '">' . e($sublabel) . '</p>' : '') . '
    </div>';
}

function status_badge(string $text, string $tone = 'slate'): string {
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber'   => 'bg-amber-50 text-amber-600',
        'rose'    => 'bg-rose-50 text-rose-600',
        'indigo'  => 'bg-indigo-50 text-indigo-600',
        'slate'   => 'bg-slate-100 text-slate-500',
    ];
    $cls = $tones[$tone] ?? $tones['slate'];
    return '<span class="inline-block text-xs font-bold px-2.5 py-1 rounded-full ' . $cls . '">' . e($text) . '</span>';
}

function btn(string $label, string $href, string $variant = 'primary', string $extra = ''): string {
    $variants = [
        'primary' => 'bg-teal-600 text-white hover:bg-teal-700',
        'ghost'   => 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50',
        'danger'  => 'text-rose-500 hover:text-rose-700',
    ];
    $cls = $variants[$variant] ?? $variants['primary'];
    return '<a href="' . e($href) . '" ' . $extra . ' class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-[10px] text-sm font-bold transition ' . $cls . '">' . e($label) . '</a>';
}

function delete_link(string $href, string $confirmMsg = 'Are you sure?', string $label = 'Delete'): string {
    return '<a href="' . e($href) . '" onclick="return confirm(' . htmlspecialchars(json_encode($confirmMsg), ENT_QUOTES) . ')" class="text-rose-500 hover:text-rose-700 text-sm font-bold">' . e($label) . '</a>';
}
