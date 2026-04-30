<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'key',
        'type',
        'start_date',
        'end_date',
        'description',
        'is_active',
        'logo',
        'email_subject',
        'email_template_html',
        'email_template_mjml',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function documentConfigurations()
    {
        return $this->hasMany(DocumentConfiguration::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withTimestamps()
            ->withPivot('shared_by');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($user) {
            $builder->where($this->getTable() . '.user_id', $user->id)
                ->orWhereHas('sharedUsers', function (Builder $shared) use ($user) {
                    $shared->where('users.id', $user->id);
                });
        });
    }

    public function canBeViewedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->sharedUsers()
            ->where('users.id', $user->id)
            ->exists();
    }
}
