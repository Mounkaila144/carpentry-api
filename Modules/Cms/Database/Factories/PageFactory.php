<?php

namespace Modules\Cms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Cms\Entities\Page;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'slug' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'excerpt' => fake()->paragraph(),
            'template' => fake()->sentence(3),
            'meta_title' => fake()->sentence(3),
            'meta_description' => fake()->sentence(),
            'meta_keywords' => fake()->sentence(3),
            'featured_image' => fake()->sentence(3),
            'status' => fake()->randomElement(['draft', 'published', 'archived', 'draft']),
            'order' => fake()->randomNumber(3),
            'is_active' => fake()->boolean(80),
        ];
    }

    /**
     * Indicate that the model is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
