<?php

namespace App\Http\Controllers\Sm;

use App\Http\Controllers\Controller;
use App\Support\SmDacolData;
use App\Support\SmMasterData;
use App\Support\SmRecordPresenter;
use App\Support\SmTransactionData;
use App\Support\TerritoryIndexFilters;
use Illuminate\Http\Request;

class SmDataController extends Controller
{
    public function masterIndex(Request $request)
    {
        $tables = SmMasterData::tableRegistry();
        $active = $this->resolveActiveTable($request->get('table'), $tables);
        $meta = $active ? ($tables[$active] ?? null) : null;
        $rows = $active
            ? SmMasterData::paginated($active, (int) $request->get('per_page', 20), $request->get('search'))
            : null;

        return view('sm.master.index', [
            'pageTitle' => 'Master Referensi',
            'tables' => $tables,
            'active' => $active,
            'meta' => $meta,
            'rows' => $rows,
            'listColumns' => $active ? SmMasterData::listColumns($active) : [],
            'available' => SmMasterData::isAvailable(),
        ]);
    }

    public function masterShow(string $table, int|string $id)
    {
        abort_unless(SmMasterData::hasTable($table), 404);

        $row = SmMasterData::find($id, $table);
        abort_if($row === null, 404);

        $registry = SmMasterData::tableRegistry();

        return view('sm.master.show', [
            'pageTitle' => ($registry[$table]['label'] ?? $table).' — Detail',
            'table' => $table,
            'meta' => $registry[$table] ?? ['label' => $table, 'column' => ''],
            'row' => $row,
            'fields' => SmRecordPresenter::fieldRows(SmMasterData::connection(), $table, $row),
            'coordinates' => SmRecordPresenter::coordinates($row),
        ]);
    }

    public function transaksiIndex(Request $request)
    {
        $tables = SmTransactionData::tableRegistry();
        $active = $this->resolveActiveTable($request->get('table'), $tables);
        $meta = $active ? config("smd_tables.transaksi.{$active}") : null;
        $rows = $active
            ? SmTransactionData::paginated($active, $request, (int) $request->get('per_page', 20))
            : null;
        $displayColumns = $active ? SmTransactionData::displayColumns($active) : [];
        $listColumns = $active ? SmTransactionData::listColumns($active) : $displayColumns;

        return view('sm.transaksi.index', array_merge([
            'pageTitle' => 'Data Operasional',
            'tables' => $tables,
            'active' => $active,
            'meta' => $meta,
            'rows' => $rows,
            'displayColumns' => $displayColumns,
            'listColumns' => $listColumns,
            'available' => SmTransactionData::isAvailable(),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function transaksiShow(string $table, int|string $id)
    {
        abort_unless(SmTransactionData::hasTable($table), 404);

        $row = SmTransactionData::find($id, $table);
        abort_if($row === null, 404);

        $registry = SmTransactionData::tableRegistry();
        $coordinates = SmRecordPresenter::coordinates($row);

        return view('sm.transaksi.show', [
            'pageTitle' => ($registry[$table]['label'] ?? $table).' — Detail',
            'table' => $table,
            'meta' => config("smd_tables.transaksi.{$table}", []),
            'row' => $row,
            'fields' => SmRecordPresenter::fieldRows(SmTransactionData::connection(), $table, $row),
            'coordinates' => $coordinates,
        ]);
    }

    public function dacolIndex(Request $request)
    {
        $tables  = SmDacolData::tableRegistry();
        $active  = $this->resolveActiveTable($request->get('table'), $tables);
        $meta    = $active ? config("smd_tables.dacol.{$active}") : null;
        $rows    = $active
            ? SmDacolData::paginated($active, $request, 50)
            : null;
        $displayColumns  = $active ? SmDacolData::displayColumns($active) : [];
        $availableSources = $active ? SmDacolData::availableSources($active) : [];

        return view('sm.dacol.index', array_merge([
            'pageTitle'       => 'Data Koleksi (sm_dacol)',
            'tables'          => $tables,
            'active'          => $active,
            'meta'            => $meta,
            'rows'            => $rows,
            'displayColumns'  => $displayColumns,
            'availableSources' => $availableSources,
            'available'       => SmDacolData::isAvailable(),
        ], TerritoryIndexFilters::viewBundle($request)));
    }

    public function dacolShow(string $table, int|string $id)
    {
        abort_unless(SmDacolData::hasTable($table), 404);

        $row = SmDacolData::find($id, $table);
        abort_if($row === null, 404);

        $registry = SmDacolData::tableRegistry();

        return view('sm.dacol.show', [
            'pageTitle'   => ($registry[$table]['label'] ?? $table).' — Detail',
            'table'       => $table,
            'meta'        => config("smd_tables.dacol.{$table}", []),
            'row'         => $row,
            'fields'      => SmRecordPresenter::fieldRows(SmDacolData::connection(), $table, $row),
            'coordinates' => SmRecordPresenter::coordinates($row),
        ]);
    }

    /**
     * @param  array<string, mixed>  $tables
     */
    private function resolveActiveTable(?string $table, array $tables): ?string
    {
        if ($table && isset($tables[$table])) {
            return $table;
        }

        return $tables !== [] ? array_key_first($tables) : null;
    }
}
