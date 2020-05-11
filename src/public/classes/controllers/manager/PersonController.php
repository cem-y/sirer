<?php
namespace DareOne\controllers\manager;
use DareOne\models\bib\BibArticle;
use DareOne\models\bib\BibEntry as BibEntry;
use DareOne\controllers\BaseController;
use DareOne\auth\UserManager;
use DareOne\models\bib\BibEntryCategory;
use DareOne\models\bib\BibEntryPerson;
use DareOne\models\person\Person;
use DareOne\operations\bib\BibManager;
use DareOne\operations\indices\BibIndex;


use DareOne\operations\persons\PersonManager;
use DareOne\system\DareLogger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Site Controller class
 *
 */
class PersonController extends BaseController {

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    function showPersons(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="persons";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=PersonManager::getAll();
        if (isset($params["ic-request"])){
        $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/persons/list.persons.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function filterPersons(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="persons";
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
        $data=PersonManager::getFiltered($freetext, $order["order"], $order["dir"]);
        $data["order"]=$order;

        if (isset($params["ic-request"])){
            $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/persons/results.list.persons.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function showPerson(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=PersonManager::getPerson($request->getAttributes()["id"]);
        if (isset($params["ic-request"])){
            $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/persons/person.persons.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    function addPerson(Request $request, Response $response) {
        $id=PersonManager::createPerson($request);
        return $response->withRedirect($this->baseUrl.'manager/persons/'.$id, 303);
    }


    function updatePerson(Request $request, Response $response)
    {
        PersonManager::updatePerson($request);
        $this->showPerson($request, $response);
    }



}
