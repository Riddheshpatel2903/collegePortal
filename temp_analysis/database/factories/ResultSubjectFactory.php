<?php

namespace Database\Factories;

use App\Models\ResultSubject;
use App\Models\Result;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultSubjectFactory extends Factory
{
    protected $model = ResultSubject::class;

    public function definition(): array
    {
        $internal = $this->faker->numberBetween(15, 30);
        $final = $this->faker->numberBetween(25, 70);

        return [
            'result_id' => Result::inRandomOrder()->first()?->id ?? Result::factory(),
            'subject_id' => Subject::inRandomOrder()->first()?->id ?? Subject::factory(),
            'internal_marks' => $internal,
            'final_marks' => $final,
            'total_marks' => $internal + $final,
            'grade' => null, // Boot logic handles this
            'status' => null, // Boot logic handles this
        ];
    }
}
