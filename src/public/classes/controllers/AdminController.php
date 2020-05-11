<?php
namespace DareOne\controllers;


use DareOne\operations\indices\BibIndex;
use DareOne\operations\indices\FulltextSectionIndex;
use DareOne\operations\indices\MsIndex;
use DareOne\operations\indices\WorkIndex;
use DareOne\operations\indices\FulltextIndex;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class AdminController extends BaseController {
    /*
     * Controller for administration only. The functions are deleting and adding full indices
     * Available with Admin Control
     */


    function prepareIndex(Request $request, Response $response){
        $params=$request->getAttributes();
        if ($params["index"] == "fulltexts"){
            $index=new FulltextIndex();
            $index->prepare();
        } elseif ($params["index"] == "bib") {
            $index=new BibIndex();
            $index->prepare();
        } elseif ($params["index"] == "manuscripts") {

             $index=new MsIndex();
             $index->prepare();


        } elseif ($params["index"] == "works") {
            $index=new WorkIndex();
            $index->prepare();

        }
        error_log(print_r("Successfully prepared Index", true));
        return $response->withRedirect('/', 301);
    }

    function deleteIndex(Request $request, Response $response){
        $params=$request->getAttributes();
        $index = new ElasticIndex();
        $index->delete($params["index"]);
        return $response->withRedirect('/', 301);

    }

    function indexBib(Request $request, Response $response){
        $index=new BibIndex();
        $index->indexAll();
        return $response->withRedirect('/', 301);
    }

    function indexMs(Request $request, Response $response){
        $index=new MsIndex();
        $index->indexAll();
        return $response->withRedirect('/', 301);
    }

    function indexWork(Request $request, Response $response){
        $index=new WorkIndex();
        $index->indexAll();
        return $response->withRedirect('/', 301);

    }

    function indexFulltexts(Request $request, Response $response){
        $index=new FulltextIndex();
        $index->indexAll();
        return ApiController::getFulltextIndex($request, $response);

    }


    function indexFulltextSections(Request $request, Response $response){
        $index=new FulltextSectionIndex;
        $index->index();
        return $response->withRedirect('/', 301);
    }


    function forceIndexFulltextSections(Request $request, Response $response){
        $index=new FulltextSectionIndex;
        $index->createIndex();
        return $response->withRedirect('/', 301);

    }








}
