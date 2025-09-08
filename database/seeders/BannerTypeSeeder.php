<?php

namespace Database\Seeders;

use App\Enums\BannerType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bannerTypes = [
            [
                'name' => BannerType::PRIMARY_HOME,
                'description' => 'Homepage banner, main banner of the website when users access (can only set 1)'
            ],
            [
                'name' => BannerType::SIDEBAR_HOME,
                'description' => 'Banner on the left side of the homepage (set at least 6 banners and maximum 12 banners)'
            ],

            [
                'name' => BannerType::CONTENT_HOME,
                'description' => 'Banner in the body of the homepage (set at least 6 banners and maximum 12 banners)'
            ],

            [
                'name' => BannerType::PRIMARY_NEWS,
                'description' => 'News page main banner (main banner when users access the news page, can only set 1)'
            ],
            [
                'name' => BannerType::SIDEBAR_ARTICLE,
                'description' => 'Banner small right sidebar of the article (can only set 1)'
            ],
        ];

        foreach ($bannerTypes as $bannerType) {
            DB::table('banner_types')->insert($bannerType);
        }
    }
}
