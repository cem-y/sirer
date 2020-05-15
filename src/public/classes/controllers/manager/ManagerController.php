<?php
namespace DareOne\controllers\manager;
use DareOne\auth\UserManager;
use DareOne\controllers\BaseController;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Site Controller class
 *
 */
class ManagerController extends BaseController {

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    function showManager(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);


        $this->view->render($response, 'manager/start.twig', ["webInfo" => $webInfo]);
    }



}
