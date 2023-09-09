<?php

namespace Modules\Lists\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ListModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Lists\Models\ListModel::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {

        $listNameComponents = [
            'Marketing', 'Sales', 'Promotion', 'Customer', 'Target', 'Lead',
            'Engagement', 'Campaign', 'Audience', 'Conversion', 'Prospect', 'Retention'
        ];
        
        return [
            'name' => $this->faker->randomElement($listNameComponents),
            'description' => $this->faker->paragraph()
        ];
    }
}
