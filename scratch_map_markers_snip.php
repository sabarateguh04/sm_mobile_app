    /**
     * Pin Posko (posyan).
     */
    private function getPoskoMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['pos_pelayanan', 'posyan'],
            ['id_posyan', 'id'],
            ['namaposyan', 'nama', 'namaposko'],
            ['lokasiposyan', 'lokasi', 'alamat'],
            'posko_',
            ['fotoposyan', 'foto', 'fotogiat']
        );
    }

    /**
     * Pin Terminal (dataterminal).
     */
    private function getTerminalMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['data_terminal', 'dataterminal'],
            ['id_terminal', 'id'],
            ['namaterminal', 'nama'],
            ['jalan', 'lokasi', 'alamat'],
            'terminal_',
            [],
            ['jenisterminal', 'jenis']
        );
    }

    /**
     * Pin Jalan Gangguan (datagangguan).
     */
    private function getJalanGangguanMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['data_gangguan', 'datagangguan'],
            ['id_datagangguan', 'id'],
            ['namajalan', 'nama'],
            ['fungsijalan', 'lokasi'],
            'jalan_',
            [],
            null,
            ['levelgangguan', 'status']
        );
    }

    private function getContigencyMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['contigency'],
            ['id_contigency', 'id'],
            ['jeniscontigency', 'jenis_peristiwa', 'jenis', 'nama'],
            ['ketcb', 'lokasi', 'uraian'],
            'con_',
            ['fotosit', 'foto', 'fotogiat']
        );
    }

    private function getTrafficstuckMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['trafficstuck'],
            ['id_trafficstuck', 'id'],
            ['faktor', 'penyebab', 'nama'],
            ['ketcb', 'lokasi', 'uraian'],
            'ts_',
            ['fotosituasi', 'fotosit', 'foto']
        );
    }

    /**
     * Pin Turjagwali.
     */
    private function getTurjagwaliMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['turjagwali'],
            ['id_turjagwali', 'id'],
            ['katgiat', 'peristiwa', 'lokasi', 'status', 'keterangan'],
            ['lokasi', 'keterangan', 'status'],
            'tj_',
            ['fotogiat', 'fotolokasi', 'foto']
        );
    }

    /**
     * Pin Audit Arus.
     */
    private function getAuditarusMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['auditarus'],
            ['id_auditarus', 'id'],
            ['hasil_audit', 'lokasi', 'nama'],
            ['catatan', 'lokasi', 'keterangan'],
            'aa_',
            ['fotolokasi', 'fotosit', 'foto']
        );
    }

    private function getObjekWisataMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['objek_wisata', 'objekwisata'],
            ['id_tempatwisata', 'id'],
            ['nametempat', 'nama', 'namaobjek'],
            ['lokasi', 'alamat'],
            'wisata_',
            ['fotoobject', 'foto', 'fotogiat']
        );
    }

    private function getTempatIbadahMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['tempatibadah'],
            ['id_tempatibadah', 'id'],
            ['namatempatibadah', 'nama'],
            ['lokasi', 'alamat'],
            'ibadah_',
            ['fotoobject', 'foto']
        );
    }

    private function getDataFaskesMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['data_faskes', 'datafaskes'],
            ['id_faskes', 'id'],
            ['namafaskes', 'nama'],
            ['lokasi', 'alamat'],
            'faskes_',
            ['fotoobject', 'foto']
        );
    }

    /**
     * Pin PNP Angkutan.
     */
    private function getPnpangkutanMarkers(): array
    {
        return $this->safeModuleMarkers(
            ['pnp_angkutan', 'pnpangkutan', 'datapnp'],
            ['id_datapnp', 'id_pnpangkutan', 'id'],
            ['namaterminal', 'jenis_angkutan', 'nama'],
            ['lokasi', 'jenisterminal', 'keterangan'],
            'pa_',
            ['fotosit', 'fotogiat', 'foto']
        );
    }

    /**
     * Ambil marker modul dengan deteksi tabel/kolom dinamis + tangkap error skema.
     *
     * @param  list<string>  $tableCandidates
     * @param  list<string>  $pkCandidates
     * @param  list<string>  $namaCandidates
     * @param  list<string>  $lokasiCandidates
     * @param  list<string>  $fotoCandidates
     * @param  list<string>|null  $jenisCandidates
     * @param  list<string>|null  $statusCandidates
     */
    private function safeModuleMarkers(
        array $tableCandidates,
        array $pkCandidates,
        array $namaCandidates,
        array $lokasiCandidates,
        string $prefix,
        array $fotoCandidates = [],
        ?array $jenisCandidates = null,
        ?array $statusCandidates = null
    ): array {
        try {
            $schema = DatabaseConnections::operationalSchema();
            $table = $this->firstExistingTable($tableCandidates);
            if ($table === null) {
                return [];
            }

            $coordCol = $this->firstExistingColumn($table, ['koordinat', 'kordinat', 'coordinate']);
            if ($coordCol === null) {
                return [];
            }

            $pk = $this->firstExistingColumn($table, $pkCandidates);
            $namaCol = $this->firstExistingColumn($table, $namaCandidates);
            $lokasiCol = $this->firstExistingColumn($table, $lokasiCandidates);
            if ($pk === null || $namaCol === null) {
                return [];
            }

            $query = $this->scopedQuery($table, $pk)
                ->select([
                    $table.'.'.$pk.' as id',
                    $table.'.'.$namaCol.' as nama',
                    $table.'.'.$coordCol.' as koordinat',
                ])
                ->whereNotNull($table.'.'.$coordCol)
                ->where($table.'.'.$coordCol, '!=', '');

            if ($lokasiCol !== null) {
                $query->addSelect($table.'.'.$lokasiCol.' as lokasi');
            } else {
                $query->selectRaw("'' as lokasi");
            }

            $fotoCol = $fotoCandidates !== [] ? $this->firstExistingColumn($table, $fotoCandidates) : null;
            if ($fotoCol !== null) {
                $query->addSelect($table.'.'.$fotoCol.' as foto_giat');
            }

            $jenisKey = null;
            if ($jenisCandidates) {
                $jenisCol = $this->firstExistingColumn($table, $jenisCandidates);
                if ($jenisCol !== null) {
                    $query->addSelect($table.'.'.$jenisCol.' as jenis');
                    $jenisKey = 'jenis';
                }
            }

            $statusKey = null;
            if ($statusCandidates) {
                $statusCol = $this->firstExistingColumn($table, $statusCandidates);
                if ($statusCol !== null) {
                    $query->addSelect($table.'.'.$statusCol.' as status');
                    $statusKey = 'status';
                }
            }

            if ($schema->hasColumn($table, 'piket')) {
                // Alias beda agar tidak bentrok dengan subquery territory scope ke `usm`
                $query->leftJoin('usm as map_petugas', $table.'.piket', '=', 'map_petugas.id_usm')
                    ->addSelect('map_petugas.nama as petugas', 'map_petugas.fotoprof as fotoprof');
            }

            return $this->mapToMarkers($query->get(), $prefix, $jenisKey, $statusKey);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MapController markers failed: '.$prefix.' '.$e->getMessage());

            return [];
        }
    }

    /**
     * @param  list<string>  $candidates
     */
    private function firstExistingTable(array $candidates): ?string
    {
        $schema = DatabaseConnections::operationalSchema();
        foreach ($candidates as $table) {
            if ($schema->hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $candidates
     */
    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        $schema = DatabaseConnections::operationalSchema();
        foreach ($candidates as $column) {
            if ($schema->hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

