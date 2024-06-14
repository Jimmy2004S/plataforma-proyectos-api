<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'post',
            'id' => (string) $this->resource->getRouteKey(),
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'created_at' => Carbon::parse($this->resource->created_at)->format('Y-m-d H:i:s'),
                'user_id' => $this->resource->user->getRouteKey(),
                'likes_count' => $this->resource->likes_count,
                'dio_like' => $this->resource->dio_like
            ],
            'relationships' => [
                'user' => [
                    'links' => [
                        'related' => route('api.user.show', $this->resource->user->getRouteKey())
                    ]
                ],
                'file' => [
                    'links' => [
                       'related' => route('api.post.files', $this->resource->getRouteKey())
                    ],
                'comments' => [
                    'links' => [
                       'related' => route('api.comment.index', $this->resource->getRouteKey())
                    ]
                ]
                ]
            ],
            'links' => [
                'self' => route('api.post.show', $this->resource->getRouteKey())
            ]
        ];
    }
}
