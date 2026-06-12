<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApPayment extends Model
{
    protected $table = 'ap_payments';

    protected $fillable = [
        'accounts_payable_id', 'amount', 'payment_date',
        'payment_method', 'notes', 'journal_id', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function accountPayable()
    {
        return $this->belongsTo(AccountPayable::class, 'accounts_payable_id');
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
