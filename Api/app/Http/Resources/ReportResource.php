<?php

namespace App\Http\Resources;

use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $fileUrl = Storage::url($this->path_file);
        return [
            'type' => 'report',
            'id' => (string) $this->resource->getRouteKey(),
            'attributes' => [
                'title' => $this->resource->title,
                'description' => $this->resource->description,
                'file' => $fileUrl,
                'created_at' => Carbon::parse($this->resource->created_at)->format('Y-m-d H:i:s'),
            ],
            'relationships' => [
                'user' => [
                    'id' => (string) $this->resource->user->getRouteKey(),
                    'attributes' => [
                    ],
                    'links' => [
                        'related' => route('api.user.show', $this->resource->user->getRouteKey()),

                    ]
                ],
                'post' => [
                    'links' => [
                        'related' => route('api.post.show', $this->resource->post->getRouteKey()),
                        'id' => (string) $this->resource->post->getRouteKey()
                    ]
                ]
            ]
        ];
    }
}
