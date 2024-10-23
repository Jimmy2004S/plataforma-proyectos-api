<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    function __construct(protected User $user) {}

    public function insert($userApi, $password, $role_id)
    {

        $user = $this->user::create([
            'user_name'         => $userApi['nombre'] . '_' . $userApi['apellidos'],
            'code'              => $userApi['codigo'],
            'email'             => $userApi['email'],
            'role_id'           => $role_id,
            'state'             => '1',
            'password'          => $password
        ]);

        if ($role_id == 2) {
            $user->student()->create([
                'semester'      => $userApi['semestre'],
                'career'        => $userApi['carrera'],
                'user_code'     =>  $userApi['codigo']
            ]);
        } else if ($role_id == 3) {
            $user->teacher()->create([
                'department'    => $userApi['departamento'],
                'user_code'     =>  $userApi['codigo']
            ]);
        }

        return $user;
    }

    public function getByCodes($codes)
    {
        return $this->user::whereIn('code', $codes)->get(); // All the users where codes
    }

    public function getByCode($code)
    {
        return $this->user::where('code', $code)->first(); // User where code
    }

    public function getById($id)
    {
        return $this->user::with(['student', 'teacher'])->where('id', $id)->first();
    }

    public function getByFilter($filter, $perPage)
    {

        $filter = strtolower($filter);

        $query = $this->user::with(['student', 'teacher']);

        $query->where(function ($subQuery) use ($filter) {
            $subQuery->whereRaw('LOWER(user_name) LIKE ?', ['%' . $filter . '%'])
                ->orWhereRaw('LOWER(code) LIKE ?', [$filter . '%'])
                ->orWhere('role_id', 'LIKE', ($filter == "estudiante") ? 2 : (($filter == "profesor") ? 3 : 0))
                ->orWhere('state', 'LIKE', ($filter == "activo") ? 1 : (($filter == "inactivo") ? 0 : 3));
        });

        $query->where('role_id', '!=', 1);

        // students filter
        $query->orWhereHas('student', function ($q) use ($filter) {
            $q->whereRaw('LOWER("semestre " ||  semester) LIKE ?', [$filter . '%'])
                ->orWhereRaw('LOWER(career) LIKE ?', ['%' . $filter . '%']);
        });

        // teacher filter
        $query->orWhereHas('teacher', function ($q) use ($filter) {
            $q->whereRaw('LOWER(department) LIKE ?', ['%' . $filter . '%']);
        });

        return $query->paginate($perPage);
    }




    public function query()
    {
        return $this->user::query();
    }

    public function applyFilter($query, $key, $value)
    {
        // Aquí puedes aplicar filtros genéricos, por ejemplo:
        return $query->where($key, $value)->where('role_id', '!=', 1);
    }
}
