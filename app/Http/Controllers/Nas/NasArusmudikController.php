<?php

namespace App\Http\Controllers\Nas;

use App\Http\Controllers\HierarchicalController;
use Illuminate\Http\Request;

/**
 * NasArusmudikController - Level Nasional
 * 
 * Controller untuk view data arus mudik & balik di level nasional.
 * Read-only tanpa filter (semua data nasional).
 */
class NasArusmudikController extends HierarchicalController
{
    protected $hierarchyLevel = 'nasional';
    protected $operasionalTable = 'arusmudik';
    protected $aggregationTable = 'nas_arusmudik';
    protected $viewFolder = 'nas.arusmudik';
    protected $primaryKey = 'id_nas_arusmudik';
    
    protected $indexFields = [
        'id_arusmudik',
        'daerah',
        'wilayah',
        'id_profileposko',
        'arusmudik_id_profileposko',
        'statarus',
        'tanggal',
        'piket',
        'usm_nama',
    ];
    
    protected $searchFields = [
        'id_arusmudik',
        'daerah',
        'wilayah',
        'id_profileposko',
        'statarus',
        'tanggal',
        'piket',
    ];
    
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    /**
     * Apply joins dengan tabel operasional (INNER JOIN)
     */
    protected function applyJoins($query)
    {
        $query->join('arusmudik', $this->aggregationTable . '.id_arusmudik', '=', 'arusmudik.id_arusmudik')
              ->join('usm', 'arusmudik.piket', '=', 'usm.id_usm')
              ->select(
                  $this->aggregationTable . '.id_arusmudik',
                  $this->aggregationTable . '.daerah',
                  $this->aggregationTable . '.wilayah',
                  $this->aggregationTable . '.id_profileposko',
                  $this->aggregationTable . '.arusmudik_id_profileposko',
                  'arusmudik.statarus',
                  'arusmudik.tanggal',
                  'arusmudik.piket',
                  'usm.nama as usm_nama'
              );
        
        return $query;
    }

    /**
     * Get page title
     */
    protected function getPageTitle()
    {
        return 'Nasional - Arus Mudik & Balik';
    }

    /**
     * Get report title
     */
    protected function getReportTitle()
    {
        return 'Report Ops : Arus Mudik & Balik';
    }

    /**
     * Override show - tidak ada view detail untuk level hierarki (read-only)
     */
    public function show($id)
    {
        abort(404, 'View detail tidak tersedia untuk level hierarki');
    }
}
