<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        if (Storage::disk('public')->exists('reports')) {
            Storage::disk('public')->deleteDirectory('reports');
        }
        if (Storage::disk('public')->exists('cover_image')) {
            Storage::disk('public')->deleteDirectory('cover_image');
        }
        if (Storage::disk('public')->exists('pdf')) {
            Storage::disk('public')->deleteDirectory('pdf');
        }
        Storage::disk('public')->makeDirectory('reports');
        Storage::disk('public')->makeDirectory('cover_image');
        Storage::disk('public')->makeDirectory('pdf');


        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PostSeed::class,
            FileSeed::class,
            LikeSeed::class,
            ReportSeed::class,
            CommentSeed::class,
        ]);
    }
}
