<?php
namespace DareOne\controllers;


use DareOne\auth\UserManager;
use DareOne\models\DbLog;
use DareOne\models\user\User;
use DareOne\operations\indices\BibIndex;


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AdminController extends BaseController {
    /*
     * Controller for administration only. The functions are deleting and adding full indices
     * Available with Admin Control
     */


    /**
     * @param Request $request
     * @param Response $response
     *
     * GET-Request
     */
    function showOverview(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="start";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        //$data=BibManager::getAll();
        $data=array();
        if (isset($params["ic-request"])){
            $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'admin/start.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * GET-Request
     */
    function showUser(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="user";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=User::all()->toArray();
        for ($i=0; $i < count($data); $i++){
            unset($data[$i]["password"]);
        }
        $this->view->render($response, 'admin/user.admin.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * POST-Request
     */
    function createUser(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="user";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);

        error_log(print_r($request->getParsedBody(), true));
        UserManager::createUser($request);
        $data=User::all()->toArray();
        for ($i=0; $i < count($data); $i++){
            unset($data[$i]["password"]);
        }
        $this->view->render($response, 'admin/list.user.admin.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * PUT-Request
     */
    function updateUser(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="user";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        UserManager::updateUser($request);
        $data=User::all()->toArray();
        for ($i=0; $i < count($data); $i++){
            unset($data[$i]["password"]);
        }
        $this->view->render($response, 'admin/list.user.admin.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    function showLogs(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="logs";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=DbLog::orderBy("id", "desc")->get()->toArray();
        $this->view->render($response, 'admin/logs.admin.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    function showTests(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="tests";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=array();
        $this->view->render($response, 'admin/tests.admin.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    function showIndices(Request $request, Response $response){
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="indices";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=array();
        $this->view->render($response, 'admin/indices.admin.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function resetBibIndex(Request $request, Response $response){
        BibIndex::prepare();
        BibIndex::indexAll();
    }








}
