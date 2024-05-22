<?php

namespace Database\Factories;

use App\Models\StudentModel;
use App\Models\CourseModel;
use App\Models\Usermodel;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => CourseModel::factory(),
            'user_id' => Usermodel::factory(),
        ];
    }
}