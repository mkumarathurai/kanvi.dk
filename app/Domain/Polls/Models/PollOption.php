<?php

namespace App\Domain\Polls\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PollOption extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = ['kind', 'date_value', 'sort_order'];

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    protected function casts(): array
    {
        return ['date_value' => 'immutable_date'];
    }
}
