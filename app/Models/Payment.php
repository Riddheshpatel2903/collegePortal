<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_fee_id',
        'student_id',
        'receipt_number',
        'amount',
        'payment_date',
        'payment_mode',
        'transaction_id',
        'remarks',
        'collected_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    // Boot method for auto-generating receipt number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = 'RCT-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(6));
            }
        });
    }
}
