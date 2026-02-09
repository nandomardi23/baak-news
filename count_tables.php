<?php

use App\Models\Krs;
use App\Models\Nilai;
use App\Models\Mahasiswa;

echo "Total Mahasiswa: " . Mahasiswa::count() . "\n";
echo "Total KRS: " . Krs::count() . "\n";
echo "Total Nilai: " . Nilai::count() . "\n";
