<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Operasional controller untuk tabel sbo tanpa kolom created_at / updated_at.
 */
abstract class LegacySboOperasionalController extends OperasionalController
{
    protected function prepareDataForStore(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }
        if (in_array('piket', $this->fillable, true)) {
            $data['piket'] = isset($data['piket']) && $data['piket'] !== '' && $data['piket'] !== null
                ? (string) $data['piket']
                : \App\Support\RoleMutationPolicy::resolveActorPiketForStore(Auth::user());
        }

        return $this->stampRecordOwnerPiket($data);
    }

    protected function prepareDataForUpdate(array $validated, Request $request)
    {
        $data = [];
        foreach ($this->fillable as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }

        return $data;
    }

    protected function insertLegacySboRow(array $data): int
    {
        return $this->insertLegacyRow($data);
    }
}
