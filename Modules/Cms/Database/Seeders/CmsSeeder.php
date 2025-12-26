<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cms\Entities\Page;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Exemple 1',
                'description' => 'Description de l\'exemple 1',
                'is_active' => true,
            ],
            [
                'name' => 'Exemple 2',
                'description' => 'Description de l\'exemple 2',
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Page::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }

        $this->command->info('Cms seeded successfully.');
    }
}
