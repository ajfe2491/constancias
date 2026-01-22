<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

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
        return $this->belongsTo(DocumentConfiguration::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function history()
    {
        return $this->belongsTo(ConstancyGeneralHistory::class, 'history_id');
    }
}
