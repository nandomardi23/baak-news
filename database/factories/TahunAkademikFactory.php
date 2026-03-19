<?php

namespace Database\Factories;

use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Factories\Factory;

class TahunAkademikFactory extends Factory
{
    protected $model = TahunAkademik::class;

    public function definition(): array
    {
        $tahun = $this->faker->unique()->numberBetween(2000, 2050);
        $semester = $this->faker->randomElement(['ganjil', 'genap']);

        return [
            'id_semester' => $tahun . ($semester === 'ganjil' ? '1' : '2'),
            'nama_semester' => $tahun . '/' . ($tahun + 1) . ' ' . ucfirst($semester),
            'tahun' => $tahun,
            'semester' => $semester,
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn() => ['is_active' => true]);
    }
}
