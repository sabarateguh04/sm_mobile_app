<?php

$dir = dirname(__DIR__) . '/resources/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$updatedFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // 1. Wrap <th>Polda</th>
        $content = preg_replace_callback(
            '/(?<!@if\(\\\\App\\\\Support\\\\TerritoryIndexFilters::canFilterByDaerah\(\)\)\s)(?<!@if\(\\\\App\\\\Support\\\\TerritoryIndexFilters::canFilterByDaerah\(\)\)\n)(\s*<th[^>]*>Polda<\/th>)/i',
            function ($matches) {
                return "\n@if(\\App\\Support\\TerritoryIndexFilters::canFilterByDaerah())" . $matches[1] . "\n@endif";
            },
            $content
        );

        // 2. Wrap <td> corresponding to Polda
        $daerahTdPattern = '/(\s*<td[^>]*>\s*<span[^>]*>\s*\{\{\s*strtoupper\(\$item->(?:daerah|daerah_nama|usm_daerah|petugas_daerah)\s*\?\?\s*[\'"](?:—|-)[\'"]\)\s*\}\}\s*<\/span>\s*<\/td>)/';
        $content = preg_replace_callback(
            $daerahTdPattern,
            function ($matches) {
                return "\n@if(\\App\\Support\\TerritoryIndexFilters::canFilterByDaerah())" . $matches[1] . "\n@endif";
            },
            $content
        );

        // 3. Combined daerah in Wilayah column
        $combinedDaerahPattern = '/(\s*<span[^>]*>\s*\{\{\s*strtoupper\(\$item->(?:daerah|daerah_nama|usm_daerah|petugas_daerah)\s*\?\?\s*[\'"](?:—|-)[\'"]\)\s*\}\}\s*<\/span>)(?=\s*<span[^>]*>\s*\{\{\s*\$item->wilayah)/';
        $content = preg_replace_callback(
            $combinedDaerahPattern,
            function ($matches) {
                return "\n@if(\\App\\Support\\TerritoryIndexFilters::canFilterByDaerah())" . $matches[1] . "\n@endif";
            },
            $content
        );

        if ($content !== $original) {
            file_put_contents($path, $content);
            $updatedFiles[] = $file->getFilename();
        }
    }
}
echo "Updated files:\n" . implode("\n", $updatedFiles);
