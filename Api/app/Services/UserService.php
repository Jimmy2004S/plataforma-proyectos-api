<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Mail\MailableClass;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{

    public function __construct(protected UserRepository $userRepository) {}

    /*
        state's value must be ( 1 , 0)
    */
    public function getAll(Request $request)
    {
        $query = $this->userRepository->query();

        if ($request->has('filter')) {
            foreach ($request->filter as $key => $value) {
                if ($key == 'semester') {
                    $query = $this->applySemesterFilter($query, $value);
                } else if ($key == 'career') {
                    $query = $this->applyCareerFilter($query, $value);
                } else {
                    $query = $this->userRepository->applyFilter($query, $key, $value);
                }
            }
        }

        $perPage = ($request->has('perPage') ? $request->get('perPage') : 20);
        $query = $query->with(['student', 'teacher']);

        return $query->paginate($perPage);

    }

    public function insert(CreateUserRequest $request)
    {
        $code               = $request->input('data.attributes.code');
        $password           = Hash::make($request->input('data.attributes.password'));
        $userApi            = $this->getByApiCode($code);
        if ($userApi == null) {
            return null;
        }


        $userTipo           = $userApi['tipo'];
        $role_id            = ($userTipo == 'estudiante') ? 2 : ($userTipo == 'profesor' ?  3 : 1);

        return $this->userRepository->insert($userApi, $password, $role_id);
    }

    public function userState($id)
    {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            return null;
        }

        $oldState = $user->state;

        // Change user state
        $user->state = ($user->state == "1") ? "0" : "1";
        $user->save();

        // Verify if it was changed
        if ($user->state != $oldState) {
            $subject = ($oldState == "1") ? 'Tu cuenta ha sido suspendida' : 'Tu cuenta ha sido reactivada';
            $htmlContent = ($oldState == "1") ?
                'Tu cuenta ha sido suspendida.' :
                'Tu cuenta ha sido reactivada.';
            Mail::to($user->email)->queue(new MailableClass($subject, $htmlContent));
            return true;
        }
        return false;
    }


    public function filter($request, $filter)
    {

        $perPage = ($request->has('perPage') ? $request->get('perPage') : 20);

        return $this->userRepository->getByFilter($filter, $perPage);
    }

    public function getByCode($code)
    {
        return $this->userRepository->getByCode($code);
    }

    public function getById($id)
    {
        return  $this->userRepository->getById($id);
    }

    private function applySemesterFilter($query, $value)
    {
        return $query->whereHas('student', function ($q) use ($value) {
            $q->where('semester', $value);
        });
    }


    private function applyCareerFilter($query, $value)
    {
        return $query->whereHas('student', function ($q) use ($value) {
            $q->where('career', $value);
        });
    }

    public function getByApiCode($codigo)
    {
        $response = Controller::apiUserCodigo($codigo);
        if ($response->status() == 200) {
            $user = $response->json();
            return $user;
        }
        return null;
    }
}
