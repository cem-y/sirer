<?php
namespace DareOne\controllers\manager;
use DareOne\models\bib\BibEntry as BibEntry;
use DareOne\controllers\BaseController;
use DareOne\auth\UserManager;
use DareOne\models\bib\BibEntryPerson;
use DareOne\operations\bib\BibManager;



use DareOne\system\DareLogger;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Site Controller class
 *
 */
class BibController extends BaseController {

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    function showBibliography(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=BibManager::getAll();
        if (isset($params["ic-request"])){
        $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/bibliography/list.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function filterBibliography(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
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
        $data=BibManager::getFiltered($freetext, $order["order"], $order["dir"]);
        $data["order"]=$order;

        if (isset($params["ic-request"])){
            $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/bibliography/results.list.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function showEntry(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        $webInfo["user"]=UserManager::getUserInfoById($_SESSION["userid"]);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        if (isset($params["ic-request"])){
            $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'manager/bibliography/entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    function addEntry(Request $request, Response $response) {
        $entry=new BibEntry();
        $entry->title=$request->getParsedBody()["title"];
        $entry->entry_type=$request->getParsedBody()["entry_type"];
        $entry->type=$request->getParsedBody()["type"];
        $entry->is_inactive=1;
        $entry->save();
        DareLogger::logDbCreate($request, "bib_entry", "DareOne\models\bib\BibEntry", $entry["id"]);
        return $response->withRedirect($this->baseUrl.'manager/bibliography/'.$entry["id"], 303);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function updateEntry(Request $request, Response $response)
    {
        BibManager::updateEntry($request);
        $this->showEntry($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function updateBook(Request $request, Response $response)
    {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::updateOrCreateBook($request);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        $this->view->render($response, 'manager/bibliography/book.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function updateBooksection(Request $request, Response $response)
    {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::updateOrCreateBookSection($request);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        $this->view->render($response, 'manager/bibliography/booksection.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function updateArticle(Request $request, Response $response)
    {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::updateOrCreateArticle($request);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        $this->view->render($response, 'manager/bibliography/article.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function addCategory(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::addCategory($request);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        $this->view->render($response, 'manager/bibliography/cat.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function deleteCategory(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::deleteCategory($request);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        $this->view->render($response, 'manager/bibliography/cat.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function addPerson(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::addPerson($request);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        $this->view->render($response, 'manager/bibliography/persons.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }



    function updatePerson(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::updatePerson($request);
        $data=BibManager::getEntry($request->getAttributes()["bid"]);
        $this->view->render($response, 'manager/bibliography/persons.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    function deletePerson(Request $request, Response $response) {
        $webInfo["baseurl"]= $this->baseUrl;
        $webInfo["area"]="bibliography";
        BibManager::deletePerson($request);
        $data=BibManager::getEntry($request->getAttributes()["id"]);
        $this->view->render($response, 'manager/bibliography/persons.entry.bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }





}
