<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Factories\Factory;

class MahasiswaFactory extends Factory
{
    protected $model = Mahasiswa::class;

    public function definition(): array
    {
        return [
            'id_mahasiswa' => $this->faker->uuid(),
            'nim' => $this->faker->numerify('##########'),
            'nama' => $this->faker->name(),
            'tempat_lahir' => $this->faker->city(),
            'tanggal_lahir' => $this->faker->date(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'alamat' => $this->faker->address(),
            'no_hp' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'program_studi_id' => ProgramStudi::factory(),
            'angkatan' => (string) $this->faker->numberBetween(2020, 2025),
            'status_mahasiswa' => 'A',
            'nama_ayah' => $this->faker->name('male'),
            'nama_ibu' => $this->faker->name('female'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn() => ['status_mahasiswa' => 'A']);
    }

    public function lulus(): static
    {
        return $this->state(fn() => ['status_mahasiswa' => 'L']);
    }
}
