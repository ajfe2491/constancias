<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Certificate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'folio',
        'folio_number',
        'recipient_name',
        'recipient_email',
        'recipient_data',
        'qr_path',
        'document_configuration_id',
        'event_id',
        'history_id',
    ];

    protected $casts = [
        'recipient_data' => 'array',
    ];

    public function documentConfiguration()
    {
        return $this->belongsTo(DocumentConfiguration::class)->withTrashed();
    }

    public function event()
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }

    public function history()
    {
        return $this->belongsTo(ConstancyGeneralHistory::class, 'history_id');
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'certificate_user')
            ->withTimestamps()
            ->withPivot('shared_by');
    }

    public function canBeViewedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->history && $this->history->user_id === $user->id) {
            return true;
        }

        return $this->sharedUsers()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user) {
            $builder->whereHas('history', function (Builder $history) use ($user) {
                $history->where('user_id', $user->id);
            })->orWhereHas('sharedUsers', function (Builder $shared) use ($user) {
                $shared->where('users.id', $user->id);
            });
        });
    }
}
