<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArPayment extends Model
{
    protected $table = 'ar_payments';

    protected $fillable = [
        'accounts_receivable_id', 'amount', 'payment_date',
        'payment_method', 'notes', 'journal_id', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function accountReceivable()
    {
        return $this->belongsTo(AccountReceivable::class, 'accounts_receivable_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
