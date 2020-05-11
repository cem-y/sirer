<?php
namespace DareOne\controllers\viewer;
use DareOne\controllers\BaseController;
use DareOne\operations\bib\BibList as BL;
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
        $post_params=$request->getParsedBody();
        $params=$request->getQueryParams();
        if (isset($post_params["freesearch"])){
            $params["freesearch"] = array($post_params["freesearch"]);
        }
        $data=BL::getData($params);
        $data["url"]=$request->getUri()->getPath()."?".$request->getUri()->getQuery();

        if (isset($params["ic-request"])){
        $webInfo["header"]=false;
        } else {
            $webInfo["header"]=true;
        }
        $this->view->render($response, 'viewer/bibliography/bib.twig', ["webInfo" => $webInfo, "data" => $data]);
    }



}
