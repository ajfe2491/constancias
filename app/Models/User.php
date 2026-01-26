<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === 'admin@siice.com';
    }

    public function sharedCertificates()
    {
        return $this->belongsToMany(Certificate::class, 'certificate_user')
            ->withTimestamps()
            ->withPivot('shared_by');
    }

    public function sharedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->withTimestamps()
            ->withPivot('shared_by');
    }

    public function sharedDocumentConfigurations()
    {
        return $this->belongsToMany(DocumentConfiguration::class, 'document_configuration_user')
            ->withTimestamps()
            ->withPivot('shared_by');
    }
}
