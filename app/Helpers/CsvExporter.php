<?php

class CsvExporter {
    /**
     * Streams data as a downloadable CSV file.
     *
     * @param string $filename Download filename (e.g. 'transactions_export_2026-09-06.csv')
     * @param array $headers List of column header titles
     * @param array $rows Array of associative or indexed rows matching column count
     */
    public static function download(string $filename, array $headers, array $rows): void {
        // Clean output buffer to prevent stray HTML/whitespace
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set response headers for direct download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Write UTF-8 BOM for Microsoft Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, $headers);

        // Write data rows
        foreach ($rows as $row) {
            fputcsv($output, array_values($row));
        }

        fclose($output);
        exit;
    }
}
