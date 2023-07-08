<?php

namespace Vuetik\VuetikLaravel\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class VuetikImages extends Model
{
    use HasUlids;

    public $table;

    protected $fillable = ['file_name', 'status'];

    public const ACTIVE = 'A';
    public const PENDING = 'P';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('vuetik-laravel.table');
    }
}
