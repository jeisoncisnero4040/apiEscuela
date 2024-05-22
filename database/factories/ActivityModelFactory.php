<?php

namespace Database\Factories;

use App\Models\ActivityModel;
use App\Models\CourseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ActivityModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'course_id' => CourseModel::factory(),
            'video_url' => $this->faker->optional()->url,
            'text' => $this->faker->paragraph,
            'calification' => $this->faker->randomFloat(2, 0, 10),
        ];
    }
}
