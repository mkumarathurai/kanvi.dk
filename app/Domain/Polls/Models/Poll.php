<?php

namespace App\Domain\Polls\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = ['public_id', 'title', 'type', 'status', 'timezone', 'locale'];

    protected function casts(): array
    {
        return ['finalized_at' => 'immutable_datetime', 'management_version' => 'integer'];
    }

    public function finalOption(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'final_option_id');
    }

    public function finalDateLabel(): ?string
    {
        return $this->finalOption
            ? ucfirst($this->finalOption->date_value->locale('da')->translatedFormat('l j. F Y'))
            : null;
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    public function adminAccess(): HasMany
    {
        return $this->hasMany(AdminAccess::class);
    }
}
