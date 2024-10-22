<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    private static $endpoint = "http://localhost/factories-laravel-proaula-main/api_uni/api.php?action=";

    static function apiUserCodigo($codigo)
    {
        return Http::get("http://localhost/Plataforma-Proaula/api_uni/api.php?action=get_user_by_codigo&codigo=$codigo");
    }

    static function apiUsers()
    {
        $response = Http::get("http://localhost/Plataforma-Proaula/api_uni/api.php?action=list_users");
        return $response;
    }

    static function apiUserId($id)
    {
        $response = Http::get(self::$endpoint."get_user_by_id&id=$id");
        return $response;
    }

    static function apiUsersFilter($filter)
    {
        return Http::get("http://localhost/Plataforma-Proaula/api_uni/api.php?action=filtrar_usuarios&busqueda=$filter");
    }

    static function apiUsersbyCarrera($carrera)
    {
        return Http::get("http://localhost/Plataforma-Proaula/api_uni/api.php?action=get_students_by_carrera&carrera=$carrera");
    }
    static function apiUsersbySemestre($semestre)
    {
        return Http::get("http://localhost/Plataforma-Proaula/api_uni/api.php?action=get_students_by_semestre&semestre=$semestre");
    }
}
