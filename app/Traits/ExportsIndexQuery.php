<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ExportsIndexQuery
{
    use Exportable;

    /**
     * @param  callable(Request): \Illuminate\Database\Query\Builder  $buildQuery
     */
    protected function exportIndexQuery(
        Request $request,
        string $format,
        callable $buildQuery,
        string $defaultFilename,
        string $title,
        ?string $idColumn = null,
    ) {
        $query = $buildQuery($request);
        if ($request->filled('id') && $idColumn) {
            $query->where($idColumn, $request->id);
        }

        $rows = $query->get();
        [$filename, $exportTitle] = $this->resolveExportFilenameAndTitle(
            $request,
            $rows->count(),
            $defaultFilename,
            $title
        );

        return match (strtolower($format)) {
            'pdf' => $this->exportToPdf($rows, 'exports.pdf.default', $filename, $exportTitle),
            'csv' => $this->exportToCsv($rows, $filename),
            default => $this->exportToExcel($rows, $filename, $exportTitle),
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveExportFilenameAndTitle(
        Request $request,
        int $rowCount,
        string $defaultFilename,
        string $title,
    ): array {
        $isSingle = $request->filled('id') && $rowCount === 1;
        $filename = $request->get('filename', $isSingle
            ? $defaultFilename.'_'.$request->id.'_'.date('Y-m-d')
            : $defaultFilename.'_'.date('Y-m-d'));
        $exportTitle = $request->get('title', $isSingle ? 'Detail '.$title : $title);

        return [$filename, $exportTitle];
    }
}
