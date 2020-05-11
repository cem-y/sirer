<?php
namespace DareOne\controllers;


use DareOne\models\bib\BibEntry;
use DareOne\models\fulltext\FulltextBase;
use DareOne\models\sources\Document;
use DareOne\operations\indices\BibIndex;


use DareOne\operations\EloquentOperations as EO;

use DareOne\operations\indices\FulltextIndex;
use DareOne\operations\indices\FulltextSectionIndex;
use DareOne\operations\indices\WorkIndex;
use DareOne\operations\indices\MsIndex;
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

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getFulltexts(Request $request, Response $response){
        $data=FulltextBase::all()
            ->toArray();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getFulltextById(Request $request, Response $response){
        $data=FulltextBase::where("idno", "=", $request->getAttributes()["id"])
            ->first()
            ->toArray();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getManuscripts(Request $request, Response $response){
        $data=Document::with("document_items", "languages", "repository", "pages")
            ->where("type", "=", "ms")
            ->get()
            ->toArray();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    function getManuscriptById(Request $request, Response $response){
        $data=Document::with("document_items", "languages", "repository", "pages")
            ->where("bilderberg_id", "=", $request->getAttributes()["id"])
            ->first()
            ->toArray();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    function getManuscriptsIndex(Request $request, Response $response){
        $data=MsIndex::getIndex();
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }

    function getManuscriptIndexById(Request $request, Response $response){
        Log::info("Manuscripts");
        $data=MsIndex::getDocument($request->getAttributes()["id"]);
        return $response->withHeader('Content-type', 'application/json')->withJson($data);
    }






    /////////OLD


    // Manuscripts

    function getMsEntries(Request $request, Response $response)
    {
        $data=EO::getMsEntries();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }

    function getMsIndex(Request $request, Response $response) {
        $index = new MsIndex();
        $data=$index->getIndex();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }

    function getMsIndexWithAggs(Request $request, Response $response) {
        $index = new MsIndex();
        $data=$index->getIndexWithAggs();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }




    // Works




    function getWorkEntries(Request $request, Response $response)
    {
        $data=EO::getAbstractWorkEntries();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }

    function getWorkIndex(Request $request, Response $response) {
        $index = new WorkIndex();
        $data=$index->getIndex();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }

    function getWorkIndexWithAggs(Request $request, Response $response) {
        $index = new WorkIndex();
        $data=$index->getIndexWithAggs();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }


    // Fulltexts


    function getFulltextsIndex(Request $request, Response $response) {
        $index = new FulltextIndex();
        $data=$index->getIndex();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }

    function getFulltextsIndexWithAggs(Request $request, Response $response) {
        $index = new FulltextIndex();
        $data=$index->getIndexWithAggs();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);
    }


    function getFulltextSectionsIndex (Request $request, Response $response) {
        $data=FulltextSectionIndex::getIndex();
        $j_response=$response->withHeader('Content-type', 'application/json');
        return $j_response->withJson($data);

    }





    //TEI Export
    public function getTeiXml(Request $request , Response $response, $args){

        $fulltextId = $args["fulltextId"];

        $data=EO::getFulltextForExport($fulltextId);

        $response = $this->view->render($response, 'xml/tei.xml.twig', [
            "data" => $data, "baseUrl" => $this->ci->settings['baseurl'] ]);
        return $response->withHeader('Content-Type', 'application/xml');


    }

    //DFG EXPORT

    public function getDfgXml(Request $request , Response $response, $args) {
        $params = $request->getQueryParams();
        $docID = (isset($args["bilderberg_id"])) ? $args["bilderberg_id"] : null;
        $type = (isset($params["type"])) ? $params["type"] : null;
        $data = Document::where('bilderberg_id', "=", $docID)->with("pages")->get()->toArray();
        $data["type"] = $type;
        $data["sv_link"]=$this->baseUrl."/sourceviewer?type=ms&docid=".$docID;
        $response = $this->view->render($response, 'xml/dfg.xml.twig', [
            "data" => $data, "baseUrl" => $this->ci->settings['baseurl'] ]);
        return $response->withHeader('Content-Type', 'application/xml');

    }

    public function exportToDfg(Request $request , Response $response) {
        $docID = $request->getAttributes()["bilderberg_id"];

        return $response->withRedirect('http://dfg-viewer.de/show/?set[mets]='.$this->baseUrl.'/api/dfg/xml/'.$docID, 301);
    }






}
