<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::create([
            'url' => 'security_guides',
            'text_en' => 'test',
            'text_ru' => 'test'
        ]);
        Page::create([
            'url' => 'terms_of_use',
            'text_en' => 'test',
            'text_ru' => 'test'
        ]);
        Page::create([
            'url' => 'terms_of_pay',
            'text_en' => 'test',
            'text_ru' => 'test'
        ]);
        Page::create([
            'url' => 'privacy_policy',
            'text_en' => 'test',
            'text_ru' => 'test'
        ]);
        Page::create([
            'url' => 'licence',
            'text_en' => 'test',
            'text_ru' => 'test'
        ]);

    }
}
