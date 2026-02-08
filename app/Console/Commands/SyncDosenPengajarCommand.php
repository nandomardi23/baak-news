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

        // 1. Get unique id_kelas_kuliah that has missing nama_dosen
        // We group by id_kelas_kuliah to minimize API calls
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

        foreach ($kelasIds as $idKelas) {
            try {
                $response = $neoFeeder->getDosenPengajarKelasKuliah($idKelas);
                
                if ($response && !empty($response['data'])) {
                    // Take the first lecturer mainly, or loop if needed.
                    // Usually 1 course has 1 main lecturer in this context.
                    $data = $response['data'][0];
                    $idDosen = $data['id_dosen'] ?? null;
                    $namaDosen = $data['nama_dosen'] ?? null;
                    $namaKelas = $data['nama_kelas_kuliah'] ?? null;

                    if ($namaDosen) {
                        // Find local dosen ID if exists
                        $localDosen = null;
                        if ($idDosen) {
                            $localDosen = Dosen::where('id_dosen', $idDosen)->first();
                        }

                        // Update ALL KrsDetail with this id_kelas_kuliah
                        $updateData = [
                            'nama_dosen' => $namaDosen,
                        ];
                        
                        if ($localDosen) {
                            $updateData['dosen_id'] = $localDosen->id;
                        }

                        // Also look into KrsDetail to see if nama_kelas needs update?
                        // User asked "harus ada data kelasnya juga". 
                        // But KrsDetail already has 'nama_kelas' from syncKrs. 
                        // We can update it just in case if provided.
                        if ($namaKelas) {
                             $updateData['nama_kelas'] = $namaKelas;
                        }

                        KrsDetail::where('id_kelas_kuliah', $idKelas)
                            ->update($updateData);
                            
                        $synced++;
                    }
                }
            } catch (\Exception $e) {
                $failed++;
                 // Rate limit protection
                sleep(1);
            }

            $bar->advance();
            
            // Simple rate limiting to avoid overwhelming Feeder
            usleep(100000); // 100ms
        }

        $bar->finish();
        $this->newLine();
        $this->info("Sync completed. Classes Synced: {$synced}. Failed: {$failed}.");
    }
}
