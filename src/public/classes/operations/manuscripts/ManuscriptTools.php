<?php
namespace DareOne\operations\manuscripts;




use DareOne\models\sources\DocPage;
use DareOne\models\sources\Document;

class ManuscriptTools
{
    /*
     *
     */
    /*
     * Converting json-columns of one $document to array structure. Overwrites keys of $document
     */
    public static function convertColumns ($document) {
        $document=ManuscriptTools::convertDocumentColumns($document);
        $document["document_items"]=ManuscriptTools::convertDocumentItemsColumns($document["document_items"]);
        return $document;
    }

    private static function convertDocumentColumns ($document)
    {

        $document["foliation"]=ManuscriptTools::createDbRelations(json_decode($document["foliation"], true));
        $document["collation"]=ManuscriptTools::createDbRelations(json_decode($document["collation"], true));
        $document["condition_description"]=ManuscriptTools::createDbRelations(json_decode($document["condition_description"], true));
        $document["decoration"]=ManuscriptTools::createDbRelations(json_decode($document["decoration"], true));
        $document["layout"]=ManuscriptTools::createDbRelations(json_decode($document["layout"], true));
        $document["hand_description"]=ManuscriptTools::createDbRelations(json_decode($document["hand_description"], true));
        $document["binding_description"]=ManuscriptTools::createDbRelations(json_decode($document["binding_description"], true));
        $document["acquisition"]=ManuscriptTools::createDbRelations(json_decode($document["acquisition"], true));
        $document["provenance"]=ManuscriptTools::createDbRelations(json_decode($document["provenance"], true));
        $document["additions"]=ManuscriptTools::createDbRelations(json_decode($document["additions"], true));

        return $document;
    }

    private static function convertDocumentItemsColumns ($documentItems)
    {
        for ($i=0; $i < count($documentItems); $i++)
        {
            $documentItems[$i]["incipit"]=ManuscriptTools::createDbRelations(json_decode($documentItems[$i]["incipit"], true));
            $documentItems[$i]["explicit"]=ManuscriptTools::createDbRelations(json_decode($documentItems[$i]["explicit"], true));
            $documentItems[$i]["note"]=ManuscriptTools::createDbRelations(json_decode($documentItems[$i]["note"], true));
            $documentItems[$i]["colophon"]=ManuscriptTools::createDbRelations(json_decode($documentItems[$i]["colophon"], true));

        }
        return $documentItems;
    }

    /**
     * @param array $json
     * @return array $json
     */
    private static function createDbRelations($json){
        if (isset($json)){
            for ($i=0; $i < count($json); $i++){
                if ($json[$i]["type"]=="locus_multi"){
                    if ( $json[$i]["to_page"]!=0){
                        $json[$i]["to_page"]=DocPage::where("id", "=", $json[$i]["to_page"])
                            ->first()
                            ->toArray();
                    }
                    if ( $json[$i]["from_page"]!=0) {
                        $json[$i]["from_page"] = DocPage::where("id", "=", $json[$i]["from_page"])
                            ->first()
                            ->toArray();
                    }

                }
            }
        }
        return $json;
    }


    public static function getBilderbergId($docid){
        $document = Document::where("id", "=", $docid)
            ->first()
            ->toArray();

        return $document["bilderberg_id"];
    }


    public static function getPageNumberByPageid($pageId){
        $page = DocPage::where("id", "=", $pageId)
            ->first()
            ->toArray();
        return $page["page_number"];
    }
}

