<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a realistic title for the report
        $title = $this->faker->sentence();
        $fileName = Str::slug($title) . '-' . Str::random(4) . '.jpg';

        // Download the image
        $url = 'https://picsum.photos/400/200';
        $contents = @file_get_contents($url);

        if ($contents === false) {
            throw new \Exception("Could not download image from $url");
        }

        $path = 'reports/' . $fileName;

        // Store the image only if not in a testing environment
        if (!app()->environment('testing')) {
            Storage::disk('public')->put($path, $contents);
        }
        return [
            'title' => $title,
            'description' => $this->faker->paragraph(2),
            'path_file' => 'public/' . $path,
        ];
    }
}
