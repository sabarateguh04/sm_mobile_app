<?php

namespace App\Http\Controllers\Wil;

use App\Http\Controllers\HierarchicalController;
use Illuminate\Http\Request;

/**
 * WilArusmudikController - Level Wilayah
 * 
 * Controller untuk view data arus mudik & balik di level wilayah.
 * Read-only dengan filter berdasarkan wilayah user.
 */
class WilArusmudikController extends HierarchicalController
{
    protected $hierarchyLevel = 'wilayah';
    protected $operasionalTable = 'arusmudik';
    protected $aggregationTable = 'wil_arusmudik';
    protected $viewFolder = 'wil.arusmudik';
    protected $primaryKey = 'id_wil_arusmudik';
    
    protected $indexFields = [
        'id_arusmudik',
        'wilayah',
        'id_profileposko',
        'arusmudik_id_profileposko',
        'tanggal',
        'piket',
        'usm_nama',
    ];
    
    protected $searchFields = [
        'id_arusmudik',
        'wilayah',
        'id_profileposko',
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
                  $this->aggregationTable . '.wilayah',
                  $this->aggregationTable . '.id_profileposko',
                  $this->aggregationTable . '.arusmudik_id_profileposko',
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
        return 'Wilayah - Arus Mudik & Balik';
    }

    /**
     * Get report title
     */
    protected function getReportTitle()
    {
        return 'Laporan Posko : Data Arus Mudik';
    }

    /**
     * Override show - tidak ada view detail untuk level hierarki (read-only)
     */
    public function show($id)
    {
        abort(404, 'View detail tidak tersedia untuk level hierarki');
    }
}
