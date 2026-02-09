<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefAgama extends Model { protected $table = 'ref_agama'; protected $guarded = ['id']; }
class RefJenisTinggal extends Model { protected $table = 'ref_jenis_tinggal'; protected $guarded = ['id']; }
class RefAlatTransportasi extends Model { protected $table = 'ref_alat_transportasi'; protected $guarded = ['id']; }
class RefPekerjaan extends Model { protected $table = 'ref_pekerjaan'; protected $guarded = ['id']; }
class RefPenghasilan extends Model { protected $table = 'ref_penghasilan'; protected $guarded = ['id']; }
class RefKebutuhanKhusus extends Model { protected $table = 'ref_kebutuhan_khusus'; protected $guarded = ['id']; }
class RefPembiayaan extends Model { protected $table = 'ref_pembiayaan'; protected $guarded = ['id']; }
class RefWilayah extends Model { protected $table = 'ref_wilayah'; protected $guarded = ['id']; }
