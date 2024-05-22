<?php

namespace Database\Factories;

use App\Models\Usermodel;
use App\Models\RolModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsermodelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Usermodel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'id_rol' => RolModel::factory(),
            'password' => bcrypt('password'), 
        ];
    }
}
