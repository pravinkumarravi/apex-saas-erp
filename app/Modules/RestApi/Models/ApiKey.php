<?php

namespace Modules\RestApi\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'api_keys';

    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'tenant_id',
        'name',
        'key_prefix',
        'key_hash',
        'abilities',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function findByPlainKey(string $plainKey): ?self
    {
        $hash = hash('sha256', $plainKey);
        return static::where('key_hash', $hash)->first();
    }

    public static function createKey(string $name, array $abilities = ['*'], ?string $expiresAt = null): array
    {
        $prefix = 'apk_live_';
        $random = Str::random(32);
        $plainKey = $prefix . $random;
        $hash = hash('sha256', $plainKey);

        $apiKey = static::create([
            'name' => $name,
            'key_prefix' => substr($plainKey, 0, 12),
            'key_hash' => $hash,
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return [
            'api_key' => $apiKey,
            'plain_text_token' => $plainKey,
        ];
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function hasAbility(string $ability): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $abilities = $this->abilities ?? ['*'];

        if (in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }
}
