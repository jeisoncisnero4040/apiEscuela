<?php

namespace Database\Factories;

use App\Models\RolModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RolModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $roles = ['admin', 'teacher', 'student'];

        return [
            'description' => $this->faker->unique()->randomElement($roles),
        ];
    }
}
