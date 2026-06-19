<?php

namespace App\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;


trait Exportable
{
    /**
     * Export data to PDF
     */
    public function exportToPdf($data, $view, $filename = 'report', $title = 'Report', $orientation = 'landscape', $paperSize = 'A4')
    {
        $pdf = Pdf::loadView($view, compact('data', 'title'))
            ->setPaper($paperSize, $orientation)
            ->setOption('isRemoteEnabled', true);
        
        return $pdf->download("{$filename}.pdf");
    }

    /**
     * Export data to Excel
     */
    public function exportToExcel($data, $filename = 'report', $title = 'Report')
    {
        $export = new class($data) implements FromCollection, WithHeadings, WithStyles, WithDrawings, WithEvents {
            private $data;
            private $drawingsList = [];
            private $imageRows = [];

            public function __construct($data)
            {
                $processed = [];
                $rowIndex = 2; // Row 1 is header
                foreach ($data as $record) {
                    $arr = is_object($record) ? (array) $record : $record;
                    $colIndex = 1;
                    foreach ($arr as $key => $val) {
                        $isImg = is_string($val) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $val) && !preg_match('/\s/', $val);
                        $imgPath = $val;
                        if (is_string($val) && str_contains($val, ',')) {
                            $firstVal = explode(',', $val)[0];
                            if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $firstVal)) {
                                $isImg = true;
                                $imgPath = $firstVal;
                            }
                        }

                        if ($isImg) {
                            $path = null;
                            if (\Illuminate\Support\Str::startsWith($imgPath, 'http')) {
                                $path = $imgPath;
                            } elseif (file_exists(public_path($imgPath))) {
                                $path = public_path($imgPath);
                            } elseif (\Illuminate\Support\Str::startsWith($imgPath, '/storage/') && file_exists(storage_path('app/public/' . substr($imgPath, 9)))) {
                                $path = storage_path('app/public/' . substr($imgPath, 9));
                            }
                            
                            if ($path) {
                                try {
                                    $drawing = new Drawing();
                                    $drawing->setName('Image');
                                    $drawing->setDescription('Image');
                                    $drawing->setPath($path);
                                    $drawing->setHeight(50);
                                    $drawing->setCoordinates(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex);
                                    $this->drawingsList[] = $drawing;
                                    $this->imageRows[] = $rowIndex;
                                    $arr[$key] = ''; // clear text
                                } catch (\Exception $e) {
                                    $arr[$key] = \Illuminate\Support\Str::startsWith($imgPath, 'http') ? $imgPath : url($imgPath);
                                }
                            } else {
                                $arr[$key] = \Illuminate\Support\Str::startsWith($imgPath, 'http') ? $imgPath : url($imgPath);
                            }
                        }
                        $colIndex++;
                    }
                    $processed[] = $arr;
                    $rowIndex++;
                }
                $this->data = $processed;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                if (empty($this->data)) {
                    return [];
                }
                $first = $this->data[0];
                return array_map(function($key) {
                    return ucwords(str_replace('_', ' ', $key));
                }, array_keys($first));
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }

            public function drawings()
            {
                return $this->drawingsList;
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function(AfterSheet $event) {
                        foreach (array_unique($this->imageRows) as $row) {
                            $event->sheet->getDelegate()->getRowDimension($row)->setRowHeight(40);
                        }
                    },
                ];
            }
        };

        return Excel::download($export, "{$filename}.xlsx");
    }

    /**
     * Export data to CSV
     */
    public function exportToCsv($data, $filename = 'report')
    {
        if (empty($data)) {
            return response('No data to export', 404);
        }

        $headers = array_keys((array) $data[0]);

        return Response::stream(function() use ($data, $headers) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, $headers);
            foreach ($data as $record) {
                $row = [];
                foreach ($headers as $header) {
                    $value = is_object($record) ? $record->$header : $record[$header] ?? '';
                    if (is_string($value) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $value) && !preg_match('/\s/', $value)) {
                        $value = \Illuminate\Support\Str::startsWith($value, 'http') ? $value : url($value);
                    } elseif (is_string($value) && str_contains($value, ',')) {
                        $firstVal = explode(',', $value)[0];
                        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $firstVal)) {
                            $value = \Illuminate\Support\Str::startsWith($firstVal, 'http') ? $firstVal : url($firstVal);
                        }
                    }
                    $row[] = $value;
                }
                fputcsv($output, $row);
            }
            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    /**
     * Export data to Word (HTML format)
     */
    public function exportToWord($data, $view, $filename = 'report', $title = 'Report')
    {
        $html = view($view, compact('data', 'title'))->render();
        
        return Response::make($html, 200, [
            'Content-Type' => 'application/vnd.ms-word',
            'Content-Disposition' => "attachment; filename=\"{$filename}.doc\"",
            'Content-Length' => strlen($html),
        ]);
    }

    /**
     * Export data to JSON
     */
    public function exportToJson($data, $filename = 'report')
    {
        return Response::json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
        ], JSON_PRETTY_PRINT);
    }
}
