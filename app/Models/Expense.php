<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    
    protected $primaryKey = 'expense_id';
    public $timestamps = false;

    protected $fillable = ['title', 'amount', 'entry_date', 'description', 'category_id'];


    protected $casts = [
        'entry_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
