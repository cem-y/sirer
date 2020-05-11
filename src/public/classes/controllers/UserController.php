<?php
namespace DareOne\controllers;

use DareOne\auth\Authenticator;
use DareOne\auth\UserManager;
use DareOne\models\user\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class UserController extends BaseController {

    function showProfile(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=UserManager::getUserInfoById($_SESSION["userid"]);
        $this->view->render($response, 'user/profile.twig', ["webInfo" => $webInfo, "data" => $data]);
    }


    function updateProfile(Request $request, Response $response){
        $params=$request->getParsedBody();
        $user=User::find($_SESSION["userid"]);
        $user->firstname=$params["firstname"];
        $user->lastname=$params["lastname"];
        if ($params["password1"]!=null){
            $user->password=password_hash($params["password1"], PASSWORD_BCRYPT);
        }
        $user->save();
        $this->showProfile($request, $response);
    }




}
