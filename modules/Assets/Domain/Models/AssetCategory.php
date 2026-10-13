<?php

namespace Modules\Assets\Domain\Models;

use Modules\Assets\Domain\Contracts\AssetCategory as AssetCategoryContract;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model implements AssetCategoryContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'asset_categories';

    protected $fillable = [
        'tenant_id',
        'name',
        'depreciation_rate',
        'method', // straight_line, written_down_value
    ];

    protected $casts = [
        'depreciation_rate' => 'decimal:2',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
