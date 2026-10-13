<?php

namespace Modules\Inventory\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Modules\Inventory\Contracts\UnitOfMeasure as UnitOfMeasureContract;

class UnitOfMeasure extends Model implements UnitOfMeasureContract
{
    use HasFactory, BelongsToTenant;

    protected $table = 'units_of_measure';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'symbol',
    ];
}
