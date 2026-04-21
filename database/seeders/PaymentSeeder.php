<?php

namespace Database\Seeders;

use App\Models\StudentFee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        StudentFee::select('id', 'student_id', 'total_amount', 'due_date')
            ->chunk(250, function ($fees) {
                $payments = [];
                $updates = [];

                foreach ($fees as $fee) {
                    $scenario = fake()->randomElement(['unpaid', 'partial', 'full']);

                    if ($scenario === 'unpaid') {
                        $updates[$fee->id] = ['paid_amount' => 0, 'status' => now()->gt($fee->due_date) ? 'overdue' : 'pending'];

                        continue;
                    }

                    $amount = $scenario === 'full'
                        ? (float) $fee->total_amount
                        : round((float) $fee->total_amount * fake()->randomFloat(2, 0.2, 0.8), 2);

                    $payments[] = [
                        'student_fee_id' => $fee->id,
                        'student_id' => $fee->student_id,
                        'receipt_number' => 'RCT-'.now()->format('Ymd-His').'-'.strtoupper(\Illuminate\Support\Str::random(6)),
                        'amount' => $amount,
                        'payment_date' => now()->subDays(rand(0, 90))->toDateString(),
                        'payment_mode' => fake()->randomElement(['cash', 'card', 'upi', 'netbanking']),
                        'transaction_id' => null,
                        'remarks' => null,
                        'collected_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $updates[$fee->id] = [
                        'paid_amount' => $amount,
                        'status' => $scenario === 'full' ? 'paid' : 'partial',
                    ];
                }

                if (! empty($payments)) {
                    DB::table('payments')->insert($payments);
                }

                foreach ($updates as $id => $payload) {
                    DB::table('student_fees')
                        ->where('id', $id)
                        ->update(array_merge($payload, ['updated_at' => now(), 'last_payment_date' => now()->toDateString()]));
                }
            });
    }
}
