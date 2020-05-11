<?php
namespace DareOne\controllers\manager;

use DareOne\controllers\BaseController;
use DareOne\auth\UserManager;
use DareOne\operations\categories\CategoryManager;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Site Controller class
 *
 */
class CategoryController extends BaseController {

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    function showCategories(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="categories";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=CategoryManager::getAll();
        if (isset($params["ic-request"])){
        $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/categories/list.categories.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function filterCategories(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="categories";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $filter=$request->getParsedBody();
        if(isset($filter["freetext"])){
            $freetext=$filter["freetext"];
        } else {
            $freetext="";
        }
        $order=$request->getQueryParams();
        if(isset($order["order"])){

        } else {
            $order["order"]="id";
            $order["dir"]="asc";
        }
        $data=CategoryManager::getFiltered($freetext, $order["order"], $order["dir"]);
        $data["order"]=$order;

        if (isset($params["ic-request"])){
            $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/categories/results.list.categories.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function showCategory(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="categories";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=CategoryManager::getEntry($request->getAttributes()["id"]);
        if (isset($params["ic-request"])){
            $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/categories/category.categories.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    function addCategory(Request $request, Response $response) {
        $id=CategoryManager::createEntry($request);
        return $response->withRedirect($this->baseUrl.'manager/categories/'.$id, 303);
    }


    function updateCategory(Request $request, Response $response)
    {
        CategoryManager::updateEntry($request);
        $this->showCategory($request, $response);
    }



}
