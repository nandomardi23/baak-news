<?php

namespace Database\Factories;

use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramStudiFactory extends Factory
{
    protected $model = ProgramStudi::class;

    public function definition(): array
    {
        return [
            'id_prodi' => $this->faker->uuid(),
            'kode_prodi' => $this->faker->numerify('####'),
            'nama_prodi' => 'Program Studi ' . $this->faker->words(2, true),
            'jenjang' => $this->faker->randomElement(['S1', 'S2', 'D3', 'D4']),
            'jenis_program' => 'reguler',
            'is_active' => true,
        ];
    }
}
