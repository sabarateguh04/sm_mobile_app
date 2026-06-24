<?php

$dir = dirname(__DIR__) . '/resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$updatedFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // Replace multiple occurrences of @if(\App\Support\TerritoryIndexFilters::canFilterByDaerah())
        // possibly separated by newlines and spaces, with just one
        $content = preg_replace(
            '/(?:[ \t]*@if\(\\\\App\\\\Support\\\\TerritoryIndexFilters::canFilterByDaerah\(\)\)\r?\n){2,}/m',
            "@if(\\App\\Support\\TerritoryIndexFilters::canFilterByDaerah())\n",
            $content
        );

        // For the endifs that were added by the script, there are also multiples
        // They look like:
        // @endif
        // @endif
        // We only want to remove consecutive @endifs IF they surround our target elements
        // Wait, it's easier to just match the pattern we added and remove the inner ones
        
        // Actually, let's just find the pattern of:
        // @if(...)
        // <th ...>Polda</th>
        // @endif
        // @endif
        // @endif
        // And reduce the endifs.
        
        $content = preg_replace(
            '/(<th[^>]*>Polda<\/th>\r?\n)(?:[ \t]*@endif\r?\n){2,}/m',
            "$1@endif\n",
            $content
        );
        
        $content = preg_replace(
            '/(<span[^>]*>\s*\{\{\s*strtoupper\(\$item->(?:daerah|daerah_nama|usm_daerah|petugas_daerah)\s*\?\?\s*[\'"](?:—|-)[\'"]\)\s*\}\}\s*<\/span>\r?\n\s*<\/td>\r?\n)(?:[ \t]*@endif\r?\n){2,}/m',
            "$1@endif\n",
            $content
        );
        
        $content = preg_replace(
            '/(<span[^>]*>\s*\{\{\s*strtoupper\(\$item->(?:daerah|daerah_nama|usm_daerah|petugas_daerah)\s*\?\?\s*[\'"](?:—|-)[\'"]\)\s*\}\}\s*<\/span>\r?\n)(?:[ \t]*@endif\r?\n){2,}/m',
            "$1@endif\n",
            $content
        );

        if ($content !== $original) {
            file_put_contents($path, $content);
            $updatedFiles[] = $file->getFilename();
        }
    }
}
echo "Cleaned files:\n" . implode("\n", $updatedFiles);
