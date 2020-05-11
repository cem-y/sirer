<?php
namespace DareOne\controllers;

use DareOne\auth\Authenticator;
use DareOne\controllers\BaseController;
use DareOne\models\user\User;
use Dflydev\FigCookies\FigResponseCookies;
use Illuminate\Support\Facades\Log;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AuthController extends BaseController {

    /**
     * @var Authenticator
     */
    protected $auth;




    function createUser(Request $request, Response $response){
    $this->auth = $this->ci["auth"];
        User::create([
                "username" => "mark",
                "password" => password_hash("Averroes2020", PASSWORD_BCRYPT)
            ]
        );

    User::create([
                "username" => "christoph",
                "password" => password_hash("Averroes2020", PASSWORD_BCRYPT)
            ]
    );

        User::create([
                "username" => "cem",
                "password" => password_hash("Averroes2020", PASSWORD_BCRYPT)
            ]
        );
    }

    function getLogin(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $this->view->render($response, 'auth/login.twig', ["webInfo" => $webInfo]);

    }

    function login(Request $request, Response $response){
        $this->auth = $this->ci["auth"];
        $params = $request->getParsedBody();
        $user = $params["user"];
        $pwd = $params["pwd"];
        $userAgent = $request->getHeader('User-Agent')[0];
        $ipAddress = $request->getServerParams()['REMOTE_ADDR'];
        if($this->auth->login($user, $pwd, $userAgent, $ipAddress)){
            return $response->withRedirect($this->baseUrl.'manager', 301);
        } else {
            return $response->withRedirect($this->baseUrl.'denied', 301);
        }

        //$response = FigResponseCookies::set($response, $cookie);
    }

    function logout(Request $request, Response $response){
        $this->auth = $this->ci["auth"];
    }


}
