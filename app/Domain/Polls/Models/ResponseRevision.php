<?php

namespace App\Domain\Polls\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ResponseRevision extends Model
{
    use HasUlids;

    protected $fillable = ['participant_id', 'editor_id', 'field_key', 'revision', 'payload_hash'];

    protected function casts(): array
    {
        return ['revision' => 'integer'];
    }
}
