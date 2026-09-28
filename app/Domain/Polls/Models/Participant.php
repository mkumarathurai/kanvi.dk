<?php

namespace App\Domain\Polls\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = ['poll_id', 'display_name', 'edit_token_hash'];

    protected $hidden = ['edit_token_hash'];

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function activeResponses(): HasMany
    {
        return $this->responses()->whereHas('option');
    }

    public function snapshot(): array
    {
        return [
            'name' => $this->display_name,
            'answers' => $this->activeResponses()->pluck('value', 'poll_option_id')->all(),
        ];
    }
}
