<?php

namespace Vuetik\VuetikLaravel\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $created_at
 * @property string $status
 * @property string $file_name
 * @property array $props
 */
class VuetikImages extends Model
{
    use HasUlids, HasFactory;

    public $table;

    protected $fillable = ['file_name', 'status', 'props'];

    public const ACTIVE = 'A';

    public const PENDING = 'P';

    protected $casts = [
        'props' => 'json',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('vuetik-laravel.table');
    }
}
