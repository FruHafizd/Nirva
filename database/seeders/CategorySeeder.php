<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan Ringan',     'slug' => 'makanan-ringan',     'icon' => '🍿', 'sort_order' => 1,  'description' => 'Snack, keripik, dan camilan'],
            ['name' => 'Minuman',             'slug' => 'minuman',            'icon' => '🥤', 'sort_order' => 2,  'description' => 'Air mineral, teh, kopi, dan jus'],
            ['name' => 'Mie Instan',          'slug' => 'mie-instan',         'icon' => '🍜', 'sort_order' => 3,  'description' => 'Mie instan goreng dan kuah'],
            ['name' => 'Bahan Pokok',         'slug' => 'bahan-pokok',        'icon' => '🌾', 'sort_order' => 4,  'description' => 'Beras, gula, minyak, tepung'],
            ['name' => 'Bumbu Dapur',         'slug' => 'bumbu-dapur',        'icon' => '🧂', 'sort_order' => 5,  'description' => 'Kecap, sambal, penyedap rasa'],
            ['name' => 'Susu & Dairy',        'slug' => 'susu-dairy',         'icon' => '🥛', 'sort_order' => 6,  'description' => 'Susu, yogurt, keju'],
            ['name' => 'Roti & Kue',          'slug' => 'roti-kue',           'icon' => '🍞', 'sort_order' => 7,  'description' => 'Roti tawar, roti isi, biskuit'],
            ['name' => 'Frozen Food',         'slug' => 'frozen-food',        'icon' => '🧊', 'sort_order' => 8,  'description' => 'Nugget, sosis, bakso frozen'],
            ['name' => 'Perawatan Tubuh',     'slug' => 'perawatan-tubuh',    'icon' => '🧴', 'sort_order' => 9,  'description' => 'Sabun, shampo, pasta gigi'],
            ['name' => 'Kebersihan Rumah',    'slug' => 'kebersihan-rumah',   'icon' => '🧹', 'sort_order' => 10, 'description' => 'Detergen, pembersih, pengharum'],
            ['name' => 'Rokok',               'slug' => 'rokok',              'icon' => '🚬', 'sort_order' => 11, 'description' => 'Rokok kretek dan filter'],
            ['name' => 'Alat Tulis Kantor',   'slug' => 'atk',               'icon' => '✏️', 'sort_order' => 12, 'description' => 'Pulpen, buku, penghapus'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
