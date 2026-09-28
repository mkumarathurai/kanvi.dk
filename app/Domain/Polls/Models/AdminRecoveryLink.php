<?php

namespace App\Domain\Polls\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminRecoveryLink extends Model
{
    use HasUlids;

    protected $guarded = ['id'];

    protected $hidden = ['email', 'token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'immutable_datetime', 'consumed_at' => 'immutable_datetime', 'register_email' => 'boolean'];
    }

    public function access(): BelongsTo
    {
        return $this->belongsTo(AdminAccess::class, 'admin_access_id');
    }

    public function scopeUsable(Builder $query): void
    {
        $query->whereNull('consumed_at')->where('expires_at', '>', now())
            ->whereHas('access', fn (Builder $access) => $access->active()->whereHas('poll', fn (Builder $poll) => $poll->where('status', '!=', 'archived')));
    }

    public function emailStillValid(): bool
    {
        return $this->register_email || ($this->access->email_verified_at !== null && $this->access->email === $this->email);
    }
}
