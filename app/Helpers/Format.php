<?php

class Format {
    public static function currency(float $amount, bool $showDecimal = false): string {
        $formatted = number_format(abs($amount), $showDecimal ? 2 : 0, '.', ',');
        $sign = $amount < 0 ? '-' : '';
        return $sign . '₹ ' . $formatted;
    }

    public static function date(?string $dateStr, string $format = 'd M Y'): string {
        if (!$dateStr) return 'N/A';
        $timestamp = strtotime($dateStr);
        return $timestamp ? date($format, $timestamp) : 'N/A';
    }

    public static function datetime(?string $dateStr, string $format = 'd M Y, h:i A'): string {
        if (!$dateStr) return 'N/A';
        $timestamp = strtotime($dateStr);
        return $timestamp ? date($format, $timestamp) : 'N/A';
    }

    public static function badge(string $type, string $text): string {
        $classes = [
            'success' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'danger' => 'bg-rose-100 text-rose-800 border border-rose-200',
            'warning' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'info' => 'bg-sky-100 text-sky-800 border border-sky-200',
            'purple' => 'bg-purple-100 text-purple-800 border border-purple-200',
            'neutral' => 'bg-slate-100 text-slate-700 border border-slate-200',
        ];
        $cls = $classes[$type] ?? $classes['neutral'];
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ' . $cls . '">' . e($text) . '</span>';
    }

    public static function brandLogo(array $brand, string $sizeClass = 'w-10 h-10', string $textClass = 'text-base'): string {
        $logoPath = $brand['logo'] ?? null;
        $name = $brand['brand_name'] ?? 'Brand';
        $initials = strtoupper(substr(e($name), 0, 2));

        if (!empty($logoPath) && file_exists(__DIR__ . '/../../public/' . $logoPath)) {
            $url = BASE_URL . '/' . ltrim($logoPath, '/');
            return '<img src="' . e($url) . '" alt="' . e($name) . '" class="' . $sizeClass . ' rounded-xl object-contain bg-white border border-slate-200 p-1 shadow-xs flex-shrink-0">';
        }

        return '<div class="' . $sizeClass . ' rounded-xl bg-sky-600 text-white flex items-center justify-center font-extrabold ' . $textClass . ' shadow-xs flex-shrink-0 uppercase">' . $initials . '</div>';
    }
}
