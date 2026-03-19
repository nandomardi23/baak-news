<?php

namespace Database\Factories;

use App\Models\SuratPengajuan;
use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuratPengajuanFactory extends Factory
{
    protected $model = SuratPengajuan::class;

    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'jenis_surat' => $this->faker->randomElement(['aktif_kuliah', 'krs', 'khs', 'transkrip']),
            'keperluan' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn() => ['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(fn() => [
            'status' => 'approved',
            'processed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn() => [
            'status' => 'rejected',
            'catatan' => 'Ditolak: data tidak lengkap',
            'processed_at' => now(),
        ]);
    }
}
