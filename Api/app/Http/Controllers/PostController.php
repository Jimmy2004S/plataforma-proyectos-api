<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Http\Resources\FileCollection;
use App\Http\Resources\FileResource;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    function __construct(protected PostService $postService)
    {
    }

    function index(Request $request)
    {
        $posts = $this->postService->getAll($request);
        if ($posts == null) {
            return response()->json([], 204);
        }
        return PostCollection::make($posts);
    }


    function create(CreatePostRequest $request)
    {
        $post = $this->postService->insert($request);
        return PostResource::make($post);
    }

    function show($id)
    {
        $post = $this->postService->getById($id);
        if (!$post) {
            return response()->json([], 204);
        }
        return PostResource::make($post);
    }


    public function postsOfUser($userId){
        $posts = $this->postService->getByUserId($userId);
        if(!$posts){
            return response()->json([], 204);
        }
        return PostCollection::make($posts);
    }

    function filterPosts($filter)
    {
        $filter = SettingsController::transl_to_en_carbon($filter);
        $dateComponents = explode(' ', $filter);
        try {
            $date = Carbon::parse($filter);
            $month = $date->month;
            $year = $date->year;
            $day = null;
            if (count($dateComponents) >= 3 && is_numeric($dateComponents[1])) {
                $day = $dateComponents[1];
            }
            $posts = $this->postService->getByDate($year, $month, $day);
        } catch (\Exception $e) {
            $posts = $this->postService->getByFilter($filter);
        }
        if ($posts->isEmpty()) {
            return response()->json([], 204);
        }
        return PostCollection::make($posts);
    }

    public function relevant()
    {

        $user = Auth::user();

        $posts = $this->postService->getRelevant($user);

        if ($posts->isEmpty()) {
            return response()->json([], 204);
        }
        return PostCollection::make($posts);
    }

    function filesPost($post_id)
    {
        $files = $this->postService->getFilesPost($post_id);
        return FileCollection::make($files);
    }

    function destroy($post){
        if($this->postService->delete($post)){
            return response()->json([], 204);
        }
        return response()->json([], 500);
    }

    public function trending(){
        $posts = $this->postService->getTrending();
        if ($posts === null) {
            return response()->json([], 204);
        }
        return PostCollection::make($posts);
    }
}
