<?php

namespace App\Domain\Polls\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAccess extends Model
{
    use HasUlids;

    protected $table = 'poll_admin_access';

    protected $fillable = ['token_hash', 'email', 'email_verified_at', 'last_used_at', 'expires_at', 'revoked_at'];

    protected $hidden = ['token_hash', 'email'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'immutable_datetime', 'last_used_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'revoked_at' => 'immutable_datetime'];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('revoked_at')->where(function (Builder $query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
