<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    //Inject UserService
    public function __construct(protected UserService $userService) {}

    function index(Request $request)
    {
        $users = $this->userService->getAll($request);
        if (!$users) {
            return response()->json([], 204);
        }
        return UserCollection::make($users);
    }



    function create(CreateUserRequest $request)
    {
        //Request  extern api
        $code = $request->input('data.attributes.code');

        // Check if user is already created
        if ($this->userService->getByCode($code)) {
            $exception = ValidationException::withMessages([
                "data.attributes.code" => "The user has already been created"
            ]);
            $exception->status = 409;
            throw $exception;
        }

        $user = $this->userService->insert($request);

        if ($user) {
            return AuthResource::make($user);
        }

        //Response extern api - code not found
        if ($user == null) {
            $exception = ValidationException::withMessages([
                "data.attributes.code" => "The user code provided is not valid"
            ]);
            $exception->status = 403;
            throw $exception;
        }
    }

    function show($id)
    {
        $user = $this->userService->getById($id);
        if (!$user) {
            return response()->json([], 204);
        }
        return UserResource::make($user);
    }

    public function filterUser(Request $request, $filter)
    {
        $users = $this->userService->filter($request, $filter);

        if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            if ($users->total() === 0) {
                return response()->json([], 204);
            }
        }

        return UserCollection::make($users);
    }
}
