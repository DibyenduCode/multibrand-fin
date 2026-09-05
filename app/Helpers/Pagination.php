<?php

class Pagination {
    public static function getParams(int $totalItems, int $perPage = 10): array {
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $offset = ($currentPage - 1) * $perPage;

        return [
            'total' => $totalItems,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'limit' => $perPage,
        ];
    }

    public static function render(array $params, string $baseUrl = ''): string {
        if ($params['total_pages'] <= 1) {
            return '';
        }

        $currentPage = $params['current_page'];
        $totalPages = $params['total_pages'];

        // Preserve current GET query params except 'page'
        $queryParams = $_GET;
        unset($queryParams['page']);
        unset($queryParams['route']);

        $buildUrl = function($page) use ($baseUrl, $queryParams) {
            $params = array_merge($queryParams, ['page' => $page]);
            $qs = http_build_query($params);
            return $baseUrl . ($qs ? '?' . $qs : '');
        };

        $html = '<div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 bg-white border-t border-slate-200/80 text-xs text-slate-600 rounded-b-2xl">';
        
        $start = (($currentPage - 1) * $params['per_page']) + 1;
        $end = min($params['total'], $currentPage * $params['per_page']);
        $html .= '<div>Showing <span class="font-bold text-slate-900">' . $start . '</span> to <span class="font-bold text-slate-900">' . $end . '</span> of <span class="font-bold text-slate-900">' . $params['total'] . '</span> records</div>';

        $html .= '<div class="flex items-center gap-1.5">';

        // Previous Button
        if ($currentPage > 1) {
            $html .= '<a href="' . e($buildUrl($currentPage - 1)) . '" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 font-semibold text-slate-700 transition-colors"><i class="fa-solid fa-chevron-left text-[10px] mr-1"></i> Prev</a>';
        } else {
            $html .= '<span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 font-semibold text-slate-400 cursor-not-allowed"><i class="fa-solid fa-chevron-left text-[10px] mr-1"></i> Prev</span>';
        }

        // Page Number Buttons
        $range = 2;
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)) {
                if ($i == $currentPage) {
                    $html .= '<span class="px-3 py-1.5 rounded-lg bg-sky-600 text-white font-bold shadow-xs">' . $i . '</span>';
                } else {
                    $html .= '<a href="' . e($buildUrl($i)) . '" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 font-semibold text-slate-700 transition-colors">' . $i . '</a>';
                }
            } elseif ($i == $currentPage - $range - 1 || $i == $currentPage + $range + 1) {
                $html .= '<span class="px-2 py-1 text-slate-400 font-bold">...</span>';
            }
        }

        // Next Button
        if ($currentPage < $totalPages) {
            $html .= '<a href="' . e($buildUrl($currentPage + 1)) . '" class="px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 font-semibold text-slate-700 transition-colors">Next <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i></a>';
        } else {
            $html .= '<span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 font-semibold text-slate-400 cursor-not-allowed">Next <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i></span>';
        }

        $html .= '</div></div>';
        return $html;
    }
}
