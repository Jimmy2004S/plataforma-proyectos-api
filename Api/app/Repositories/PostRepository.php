<?php

namespace App\Repositories;

use App\Models\Post;

class PostRepository
{
    function __construct(protected Post $post)
    {
    }

    function insert($title, $description, $user_id)
    {
        return $this->post::create([
            'title' => $title,
            'description' => $description,
            'user_id' => $user_id
        ]);
    }

    function getAll()
    {
        return $this->post::withCount('likes')
            ->with(['likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw('posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like', [auth()->id()])
            ->get();
    }

    function getById($id)
    {
        return $this->post::withCount('likes')
            ->with(['likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw('posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like', [auth()->id()])->where('id', $id)->first();
    }

    function getByUserId($user_id)
    {
        return $this->post::withCount('likes')
            ->with(['likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw('posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like', [auth()->id()])
            ->where('user_id', $user_id)->get();
    }

    function getByUsersCodes($codes)
    {
        return $this->post::withCount('likes')
            ->with(['likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw(
                'posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like',
                [auth()->id()]
            )
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->whereIn('users.code', $codes)
            ->get();
    }


    function getByDate($year, $month, $day)
    {
        //Bluid the consultation
        $query = $this->post::query();
        $query->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);
        //The day was specified
        if ($day !== null) {
            $query->whereDay('created_at', $day);
        }
        $query->withCount('likes')
            ->with(['likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw('posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like', [auth()->id()]);
        return $query->get();
    }

    function getByFilter($filter)
    {
        return $this->post::withCount('likes')
            ->with(['likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw('posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like', [auth()->id()])->where('title', 'LIKE', '%' . $filter . '%')->get();
    }

    function delete($post)
    {
        $exist = $this->post::find($post);
        return $exist->delete() ? true : false;
    }

    function getTrending()
    {
        return $this->post::withCount('likes')
            ->with(['likes' => function ($query) {
                // Filtrar los likes por el usuario autenticado
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw('posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like', [auth()->id()])
            ->orderBy('likes_count', 'DESC')
            ->limit(3)
            ->get();
    }

    public function query()
    {
        $query = $this->post::query();
        return $query->withCount('likes')
            ->with(['likes' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->selectRaw('posts.*, EXISTS(SELECT 1 FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS dio_like', [auth()->id()]);
    }

    function getByUsersIds($query, $users_ids)
    {
        return $query->whereIn('user_id', $users_ids);
    }

    function getByQuery($query, $key, $value)
    {
        return $query->where($key, $value);
    }

    function getByUsersCodesQuery($query, $codes){
        return $query->join('users' , 'users.id' , '=' , 'posts.user_id')
        ->whereIn('users.code' , $codes);
    }
}
