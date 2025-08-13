<?php

namespace admin\categories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Kyslik\ColumnSortable\Sortable;

class WithdrawRequest extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'method_details',
        'status',
    ];

    protected $sortable = [
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'method_details' => 'array',
    ];

    /**
     * filter by status
     */
    public function scopeFilterByStatus($query, $status)
    {
        if (!is_null($status)) {
            return $query->where('status', $status);
        }

        return $query;
    }
    
    public static function getPerPageLimit(): int
    {
        return Config::has('get.admin_page_limit')
            ? Config::get('get.admin_page_limit')
            : 10;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}