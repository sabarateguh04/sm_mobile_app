<?php

$dir = __DIR__ . '/resources/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // 1. Wrap <th>Polda</th>
        // Regex: find <th>Polda</th> that is NOT already wrapped
        $content = preg_replace_callback(
            '/(?<!@if\(\\\\App\\\\Support\\\\TerritoryIndexFilters::canFilterByDaerah\(\)\)\s)(?<!@if\(\\\\App\\\\Support\\\\TerritoryIndexFilters::canFilterByDaerah\(\)\)\n)(\s*<th[^>]*>Polda<\/th>)/i',
            function ($matches) {
                return "\n@if(\\App\\Support\\TerritoryIndexFilters::canFilterByDaerah())" . $matches[1] . "\n@endif";
            },
            $content
        );

        // 2. Wrap <td> corresponding to Polda
        // This is tricky because we need to know WHICH td. 
        // We know it usually prints $item->daerah, $item->daerah_nama, $item->usm_daerah, $item->petugas_daerah.
        // Let's look for a <td> that ONLY contains the daerah variable (and optionally some spans) and precedes the wilayah <td>.
        // Actually, since there's exactly one <th>Polda</th>, there's exactly one <td> that corresponds to it.
        // Let's just find <td> elements that output `daerah`.
        
        $daerahTdPattern = '/(\s*<td[^>]*>\s*<span[^>]*>\s*\{\{\s*strtoupper\(\$item->(?:daerah|daerah_nama|usm_daerah|petugas_daerah)\s*\?\?\s*[\'"](?:—|-)[\'"]\)\s*\}\}\s*<\/span>\s*<\/td>)/';
        $content = preg_replace_callback(
            $daerahTdPattern,
            function ($matches) {
                // if it's already wrapped, skip
                return "\n@if(\\App\\Support\\TerritoryIndexFilters::canFilterByDaerah())" . $matches[1] . "\n@endif";
            },
            $content
        );

        // What about contigency/index.blade.php where it's combined?
        // <span class="text-[10px] font-bold block" style="color:#D8A808;">{{ strtoupper($item->daerah_nama ?? '—') }}</span>
        $combinedDaerahPattern = '/(\s*<span[^>]*>\s*\{\{\s*strtoupper\(\$item->(?:daerah|daerah_nama)\s*\?\?\s*[\'"](?:—|-)[\'"]\)\s*\}\}\s*<\/span>)(?=\s*<span[^>]*>\s*\{\{\s*\$item->wilayah)/';
        $content = preg_replace_callback(
            $combinedDaerahPattern,
            function ($matches) {
                return "\n@if(\\App\\Support\\TerritoryIndexFilters::canFilterByDaerah())" . $matches[1] . "\n@endif";
            },
            $content
        );

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated: " . $file->getFilename() . "\n";
        }
    }
}
echo "Done.\n";
