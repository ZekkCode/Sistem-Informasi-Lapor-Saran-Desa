<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Fasilitas Desa' => ['Lampu dan Penerangan Jalan', 'Fasilitas Umum', 'Lainnya'],
            'Lingkungan' => ['Sampah', 'Drainase', 'Pencemaran', 'Lainnya'],
            'Infrastruktur' => ['Jalan', 'Jembatan', 'Saluran Air', 'Lainnya'],
            'Pelayanan Desa' => ['Administrasi', 'Informasi Publik', 'Lainnya'],
            'Aspirasi Masyarakat' => ['Usulan Kegiatan', 'Saran Pembangunan', 'Lainnya'],
        ];

        foreach ($categories as $categoryIndex => $subcategories) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($categoryIndex)],
                [
                    'name' => $categoryIndex,
                    'sort_order' => array_search($categoryIndex, array_keys($categories), true) + 1,
                    'is_active' => true,
                ],
            );

            foreach ($subcategories as $subcategoryIndex => $subcategory) {
                $category->subcategories()->updateOrCreate(
                    ['slug' => Str::slug($subcategory)],
                    [
                        'name' => $subcategory,
                        'sort_order' => $subcategoryIndex + 1,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
