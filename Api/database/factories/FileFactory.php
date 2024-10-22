<?php

namespace Database\Factories;

use App\Models\Post;
use Dompdf\Dompdf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    public function type($type)
    {
        $title = $this->faker->sentence(10);
        $fileName = Str::slug($title) . '-' . Str::random(4);
        $path = $type . '/' . $fileName;
        // Generate the file based on its type
        if (!app()->environment('testing')) {
            if ($type === 'pdf') {
                $path.= '.pdf';
                $this->generatePdf($path);
            } elseif ($type === 'cover_image') {
                $path .= '.jpg';
                $this->img($path);
            }
        }
        return $this->state(function (array $attributes) use ($title, $type, $path) {
            return [
                'name' => $title,
                'path' => 'public/' . $path,
                'type' => $type,
            ];
        });
    }

    private function img($path)
    {
        // Download the image
        $url = 'https://picsum.photos/400/200';
        $content = @file_get_contents($url);
        if ($content === false) {
            throw new \Exception("Could not download image from $url");
        }
        // Save the image to storage
        Storage::disk('public')->put($path, $content);
    }

    private function generatePdf($path)
    {
        // Generate content for the PDF
        $title = $this->faker->sentence();
        $paragraph = $this->faker->paragraph();
        $html = '
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f0f0f0;
                }
                h1 {
                    color: #333333;
                    text-align: center;
                }
                p {
                    color: #666666;
                    text-align: justify;
                }
            </style>
        </head>
        <body>
            <h1>' . $title . '</h1>
            <p>' . $paragraph . '</p>
        </body>
        </html>';

        // Create a PDF using Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();
        // Save the PDF to storage
        file_put_contents('public/storage/' . $path, $output);
    }
}
