<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class ConstancyGeneralHistory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'constancy_general_history';

    protected $fillable = [
        'total_registros',
        'procesados_exitosos',
        'procesados_fallidos',
        'qrs_generados',
        'errores',
        'user_id',
        'csv_file_path',
        'document_configuration_id',
    ];

    protected $casts = [
        'errores' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function documentConfiguration()
    {
        return $this->belongsTo(DocumentConfiguration::class)->withTrashed();
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'history_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user) {
            $builder->where('user_id', $user->id)
                ->orWhereHas('certificates.sharedUsers', function (Builder $shared) use ($user) {
                    $shared->where('users.id', $user->id);
                });
        });
    }
}
