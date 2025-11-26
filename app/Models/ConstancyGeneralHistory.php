<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConstancyGeneralHistory extends Model
{
    use HasFactory;

    protected $table = 'constancy_general_history';

    protected $fillable = [
        'total_registros',
        'procesados_exitosos',
        'procesados_fallidos',
        'qrs_generados',
        'errores',
        'user_id',
        'csv_file_path',
    ];

    protected $casts = [
        'errores' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
