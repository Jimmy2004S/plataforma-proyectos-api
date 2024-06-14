<?php

namespace App\Repositories;

use App\Models\Comment;

class CommentRepository
{
    public function __construct(protected Comment $comment)
    {
    }

    public function select($perPage, $post)
    {
        return $this->comment::where('post_id', $post)
            ->with(['user:id,user_name', 'post:id'])
            ->selectRaw('comments.* , EXISTS (SELECT * FROM comments WHERE comments.user_id = ? AND comments.post_id = ?) AS auth_commented', [auth()->id(), $post])
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }

    public function insert($content, $post_id, $user_id)
    {
        return $this->comment::create([
            'post_id' => $post_id,
            'user_id' => $user_id,
            'content' => $content
        ]);
    }

    public function delete($comment)
    {
        return $this->comment::where('id', $comment)->delete();
    }
}
