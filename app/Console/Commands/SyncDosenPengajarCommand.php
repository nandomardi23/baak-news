<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\KrsDetail;
use App\Services\NeoFeederService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDosenPengajarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baak:sync-dosen-pengajar {--limit=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Dosen Pengajar (Pembina) for KRS Details';

    /**
     * Execute the console command.
     */
    public function handle(NeoFeederService $neoFeeder)
    {
        $limit = $this->option('limit');
        
        $this->info("Checking for KrsDetails with Missing Dosen...");

        $kelasIds = KrsDetail::whereNotNull('id_kelas_kuliah')
            ->whereNull('nama_dosen')
            ->distinct()
            ->pluck('id_kelas_kuliah');

        $total = $kelasIds->count();
        $this->info("Found {$total} unique classes to sync.");

        if ($total === 0) {
            $this->info("Nothing to sync.");
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $synced = 0;
        $failed = 0;
        $dosenMap = Dosen::pluck('id', 'id_dosen')->toArray();

        foreach ($kelasIds as $idKelas) {
            try {
                if ($this->processKelas($neoFeeder, $idKelas, $dosenMap)) {
                    $synced++;
                }
            } catch (\Exception $e) {
                $failed++;
                sleep(1);
            }

            $bar->advance();
            usleep(100000); // 100ms
        }

        $bar->finish();
        $this->newLine();
        $this->info("Sync completed. Classes Synced: {$synced}. Failed: {$failed}.");
    }

    private function processKelas(NeoFeederService $neoFeeder, string $idKelas, array $dosenMap): bool
    {
        $response = $neoFeeder->getDosenPengajarKelasKuliah($idKelas);
        
        if (!$response || empty($response['data'])) {
            return false;
        }

        $data = $response['data'][0];
        $idDosen = $data['id_dosen'] ?? null;
        $namaDosen = $data['nama_dosen'] ?? null;
        $namaKelas = $data['nama_kelas_kuliah'] ?? null;

        if (!$namaDosen) {
            return false;
        }

        $updateData = [
            'nama_dosen' => $namaDosen,
        ];
        
        if ($idDosen && isset($dosenMap[$idDosen])) {
            $updateData['dosen_id'] = $dosenMap[$idDosen];
        }

        if ($namaKelas) {
            $updateData['nama_kelas'] = $namaKelas;
        }

        KrsDetail::where('id_kelas_kuliah', $idKelas)->update($updateData);
        return true;
    }
}
