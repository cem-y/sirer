<?php
namespace DareOne\operations\manuscripts;


use DareOne\models\sources\DocCatalog;
use DareOne\models\sources\DocPage;
use DareOne\models\sources\Document;
use DareOne\operations\utilities\Tools;
use DareOne\operations\manuscripts\ManuscriptTools;


class ManuscriptViewer
{
    /*
     *
     */


    /**
     * @param string $bilderbergId
     * @param int $pageNumber
     * @return array|mixed
     */
    public static function getData($bilderbergId, $pageNumber=0)
    {
        $data=Document::with("languages", "repository", "pages", "document_items", "material")
            ->where("bilderberg_id", "=", $bilderbergId)
            ->first()
            ->toArray();

        if (isset($data["document_items"])){
            $data=ManuscriptTools::convertColumns($data);
        }
        if ($data["has_images"]){
            $data["currentPage"]=ManuscriptViewer::getCurrentPage($pageNumber, $data);
        }

        $data["bib"]=ManuscriptViewer::getBibliography($data["id"]);

        return $data;
    }

    private static function getCurrentPage($pageNumber, $data)
    {
        if ($pageNumber!=0){
            $currentPage=DocPage::where("page_number", "=", $pageNumber)
                ->where("doc_id", "=", $data["id"])
                ->first()
                ->toArray();
        } else {
            $currentPage=DocPage::where("page_number", "=", $data["pages"][0]["page_number"])
                ->where("doc_id", "=", $data["id"])
                ->first()
                ->toArray();

        }
        $currentPage["iiif"]='https://bilderberg.uni-koeln.de/iipsrv/iipsrv.fcgi?IIIF=books/'.$data["bilderberg_id"].'/pyratiff/'.$currentPage["bilderberg_id"].'.tif/info.json';
        $currentPage["dimensions"]=Tools::getImageDimensions('https://134.95.80.121/iipsrv/iipsrv.fcgi?IIIF=books/'.$data["bilderberg_id"].'/pyratiff/'.$currentPage["bilderberg_id"].'.tif/info.json');
        $currentPage["dfg"]='api/dfg/export/'.$data["bilderberg_id"];
        $currentPage["pages"]=ManuscriptViewer::getPages($currentPage, $data);


        return $currentPage;
    }

    private static function getBibliography($docId)
    {
        return $bibliography=DocCatalog::where("ms_id", "=", $docId)->with("bib_entries")->get()->toArray();
    }


    private static function getPages($currentPage, $data)
    {
        $baseUrl="manuscripts/".$data["bilderberg_id"]."/page/";

        $pages["first"]=$baseUrl.$data["pages"][0]["page_number"];
        $last=end($data["pages"]);
        if ($currentPage["page_number"]!==$last["page_number"]){
            $next=$currentPage["page_number"]+1;
        }
        else {
            $next=$last["page_number"];
        }

        if ($currentPage["page_number"]!=$data["pages"][0]["page_number"]){
            $back=$currentPage["page_number"]-1;
        }
        else {
            $back=$data["pages"][0]["page_number"];
        }
        $pages["lastId"]=$last["page_number"];
        $pages["nextId"]=$next;
        $pages["backId"]=$back;
        $pages["firstId"]=$data["pages"][0]["page_number"];


        $pages["last"]=$baseUrl.$last["page_number"];
        $pages["next"]=$baseUrl.$next;
        $pages["back"]=$baseUrl.$back;

        return $pages;
    }



}

