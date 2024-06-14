<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'comment',
            'id' => (string) $this->resource->getRouteKey(),
            'attributes' =>  [
                'content' => $this->resource->content,
                'created_at' => Carbon::parse($this->resource->createdAt)->format('Y-m-d H:i:s'),
                'auth_commented' => $this->resource->auth_commented
            ],
            'relationships' => [
                'user' => [
                    'attributes' => [
                        'user_name' => $this->resource->user->user_name
                    ],
                    'links' => [
                        'related' => route('api.user.show', $this->resource->user->getRouteKey()),
                    ]
                ],
                'post' => [
                    'id' => $this->resource->post->getRouteKey(),
                    'attributes' => [
                    ],
                    'links' => [
                        'related' => route('api.post.show', $this->resource->post->getRouteKey()),
                    ]
                ]
            ]
        ];
    }
}
