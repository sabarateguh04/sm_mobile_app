<?php

namespace App\Http\Controllers\Da;

use App\Http\Controllers\HierarchicalController;
use Illuminate\Http\Request;

/**
 * DaArusmudikController - Level Daerah
 * 
 * Controller untuk view data arus mudik & balik di level daerah.
 * Read-only dengan filter berdasarkan daerah user.
 */
class DaArusmudikController extends HierarchicalController
{
    protected $hierarchyLevel = 'daerah';
    protected $operasionalTable = 'arusmudik';
    protected $aggregationTable = 'da_arusmudik';
    protected $viewFolder = 'da.arusmudik';
    protected $primaryKey = 'id_da_arusmudik';
    
    protected $indexFields = [
        'id_arusmudik',
        'daerah',
        'wilayah',
        'katops',
        'jenisops',
        'tanggal',
        'namaposko',
        'arusmudik_id_profileposko',
    ];
    
    protected $searchFields = [
        'id_arusmudik',
        'daerah',
        'wilayah',
        'katops',
        'jenisops',
        'tanggal',
        'namaposko',
        'tahunops',
        'piket',
        'id_profileposko',
    ];
    
    protected $defaultOrderBy = 'tanggal';
    protected $defaultOrderType = 'desc';

    /**
     * Apply joins dengan tabel operasional (INNER JOIN)
     */
    protected function applyJoins($query)
    {
        $query->join('arusmudik', $this->aggregationTable . '.id_arusmudik', '=', 'arusmudik.id_arusmudik')
              ->join('profilposko', 'arusmudik.id_profileposko', '=', 'profilposko.id_profilposko')
              ->select(
                  $this->aggregationTable . '.id_arusmudik',
                  $this->aggregationTable . '.daerah',
                  $this->aggregationTable . '.wilayah',
                  'profilposko.katops',
                  'profilposko.jenisops',
                  'arusmudik.tanggal',
                  'profilposko.namaposko',
                  $this->aggregationTable . '.arusmudik_id_profileposko'
              );
        
        return $query;
    }

    /**
     * Get page title
     */
    protected function getPageTitle()
    {
        return 'Daerah - Arus Mudik & Balik';
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
