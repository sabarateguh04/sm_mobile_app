<?php

namespace App\Http\Controllers;

use App\Traits\Exportable;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    use Exportable;

    /**
     * Export data based on format
     */
    public function export(Request $request, $model, $format = 'excel')
    {
        // Get model class
        $modelClass = "App\\Models\\" . ucfirst($model);
        
        if (!class_exists($modelClass)) {
            abort(404, 'Model not found');
        }

        // Get data with filters
        $query = $modelClass::query();
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            // Add search logic based on model
            $query->where(function($q) use ($search, $model) {
                // Generic search - override in specific controllers
                $fillable = (new $modelClass)->getFillable();
                foreach ($fillable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Apply filters
        if ($request->filled('filter')) {
            foreach ($request->filter as $field => $value) {
                $query->where($field, $value);
            }
        }

        $data = $query->get();
        $filename = $request->get('filename', $model . '_' . date('Y-m-d'));
        $title = $request->get('title', ucfirst($model) . ' Report');

        // Export based on format
        switch (strtolower($format)) {
            case 'pdf':
                $view = $request->get('view', 'exports.pdf.' . $model);
                return $this->exportToPdf(
                    $data,
                    $view,
                    $filename,
                    $title,
                    $request->get('orientation', 'portrait'),
                    $request->get('paper_size', 'A4')
                );
                
            case 'excel':
                return $this->exportToExcel($data, $filename, $title);
                
            case 'csv':
                return $this->exportToCsv($data, $filename);
                
            case 'word':
                $view = $request->get('view', 'exports.word.' . $model);
                return $this->exportToWord($data, $view, $filename, $title);
                
            case 'json':
                return $this->exportToJson($data, $filename);
                
            default:
                abort(400, 'Invalid export format');
        }
    }
}

