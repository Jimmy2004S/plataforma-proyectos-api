<?php

namespace App\Services;

use App\Http\Requests\CreatePostRequest;
use App\Repositories\PostRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;


class PostService
{

    function __construct(protected PostRepository $postRepository, protected UserService $userService) {}

    function insert(CreatePostRequest $request)
    {
        $title = $request->input('data.attributes.title');
        $description = $request->input('data.attributes.description');
        $user_id = $request->user()->id;

        if ($request->hasFile('data.attributes.cover_image')) {
            $coverImage = $request->file('data.attributes.cover_image');
            $coverImageName = $coverImage->getClientOriginalName();
            // $coverImagePath = $coverImage->storeAs('public/cover_image', $coverImageName);
            $coverImagePath = $this->storeFile($coverImage, 'public/cover_image');
        }

        if ($request->hasFile('data.attributes.pdf')) {
            $file = $request->file('data.attributes.pdf');
            $fileName = $file->getClientOriginalName();
            //$filePath = $file->storeAs('public/file', $fileName);
            $filePath = $this->storeFile($file, 'public/pdf');
        }

        $post =  $this->postRepository->insert($title, $description, $user_id);

        if (isset($coverImagePath)) {
            $post->files()->create([
                'name' => $coverImageName,
                'path' => $coverImagePath,
                'type' => 'cover_image'
            ]);
        }

        if (isset($filePath)) {
            $post->files()->create([
                'name' => $fileName,
                'path' => $filePath,
                'type' => 'pdf'
            ]);
        }

        return $post;
    }

    function getAll($request)
    {
        $query = $this->postRepository->query();
        if ($request->has('filter')) {
            foreach ($request->get('filter') as $key => $value) {
                if ($key == 'career') {
                    $query = $this->applyFilterPostByCareer($query, $value);
                } else if ($key == 'semester') {
                    $query = $this->applyFilterPostBySemester($query, $value);
                } else {
                    $query = $this->postRepository->getByQuery($query, $key, $value);
                }
            }
        }

        $perPage = ($request->has('perPage')) ? $request->get('perPage') : 10; //Check perPage value
        $posts = $query->paginate($perPage);

        if (!$posts) {
            return null;
        }

        return $posts;
    }

    function getById($id)
    {
        return $this->postRepository->getById($id);
    }

    function getByUserId($userId)
    {
        return $this->postRepository->getByUserId($userId);
    }

    function getRelevant($user)
    {

        $career     = $user->student->career; // Acceder a la propiedad 'semester'
        $query      = $this->postRepository->query();

        $posts      = $this->postRepository->getByUsersCareerQuery($query, $career)->paginate(10);

        if(!$posts){
            return null;
        }
        return $posts;
    }

    function getByDate($year, $month, $day)
    {
        return $this->postRepository->getByDate($year, $month, $day);
    }

    function getByFilter($filter)
    {
        return $this->postRepository->getByFilter($filter);
    }

    function delete($post)
    {
        $files = $this->getFilesPost($post);
        if ($this->postRepository->delete($post)) {
            foreach ($files as $file) {
                $this->deleteFile($file->path);
            }
            return true;
        };
        return false;
    }

    function getTrending()
    {
        $posts = $this->postRepository->getTrending();
        if (!$posts) {
            return null;
        }
        return $posts;
    }

    //Methods
    public function deleteFile($path)
    {
        if (Storage::delete($path)) {
            return true;
        }
        return false;
    }

    public function getFilesPost($id)
    {
        $post = $this->postRepository->getById($id);
        return $post->files;
    }

    private function storeFile(UploadedFile $file, $path)
    {
        if (app()->environment('testing')) {
            return 'fake_path';
        } else {
            return $file->store($path);
        }
    }

    private function applyFilterPostByCareer($query, $value)
    {
        return $this->postRepository->getByUsersCareerQuery($query, $value);
    }

    private function applyFilterPostBySemester($query, $value)
    {
        return $this->postRepository->getByUsersSemesterQuery($query, $value);
    }

}
