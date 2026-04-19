<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Batik Wanita', 'slug' => 'batik-wanita', 'description' => 'Koleksi batik untuk wanita — dress, blouse, gamis, dan outer dengan motif klasik nusantara.', 'sort_order' => 1],
            ['name' => 'Batik Pria',   'slug' => 'batik-pria',   'description' => 'Kemeja dan atasan batik pria dengan potongan regular maupun slim fit.', 'sort_order' => 2],
            ['name' => 'Aksesoris',    'slug' => 'aksesoris',    'description' => 'Scarf, selendang, tas, dan aksesoris batik pelengkap penampilan.', 'sort_order' => 3],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
