<?php
namespace DareOne\controllers;


use DareOne\models\bib\BibEntry;

use DareOne\operations\indices\BibIndex;
use DareOne\operations\EloquentOperations as EO;
use Illuminate\Support\Facades\Log;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Class ApiController
 * @package DareOne\controllers
 *
 * Controller Class for api calls. Requests will be handled, calls to operations classes will be made and responses
 * prepared.
 */
class ApiController extends BaseController {
    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getBib(Request $request, Response $response){
        $data=BibEntry::with("persons", "categories", "types", "works", "book", "booksection", "article")
            ->get()
            ->toArray();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getBibById(Request $request, Response $response){
        $data=BibEntry::with("persons", "categories", "types", "works", "book", "booksection", "article")
            ->where("id", "=", $request->getAttributes()["id"])
            ->first()
            ->toArray();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getBibIndex(Request $request, Response $response){
        $data=BibIndex::getIndex();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getBibIndexDocumentById(Request $request, Response $response){
        $data=BibIndex::getDocument($request->getAttributes()["id"]);
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }


}
