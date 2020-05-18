<?php
namespace DareOne\operations\bib;

use DareOne\operations\indices\BibIndex;
use DareOne\operations\utilities\Tools;
/**
 * List all bibliographic entries
 *
 */
class BibList
{
    /**
     * Gather Data for Bibliography Page
     * @param $selected : Array with selected params from request
     * @return $data : gathered data to feed page
     */
    public static function getData($selected)
    {
        $data["results"] = BibList::createResults($selected);
        $data["page"]=BibList::createPages($selected, $data["results"]["byDate"]);

        $data["selected"]=$selected;
        $data["facets"]=BibList::createFacets($data["results"]["byDate"]["aggregations"], $selected);
        if (isset($selected["freesearch"])) {
            $data["freesearch"]=BibList::createFreeSearch($selected);
        }
        else{

            $data["freesearch"]["url"]=BibList::createCurrentBaseUrl($selected);
        }
        return $data;

    }

    /**
     * create the query-url from selected params
     * @param $selected : Array with selected params from request
     * @return $baseUrl: current URL
     */
    private static function createCurrentBaseUrl ($selected)
    {
        $baseURL="?";
        if (isset($selected["types"])){
            foreach ($selected["types"] as $t) {
                $baseURL=$baseURL."&types[]=".$t;
            }
        }
        if (isset($selected["categories"])){
            foreach ($selected["categories"] as $t) {
                $baseURL=$baseURL."&categories[]=".$t;
            }
        }
        if (isset($selected["works"])){
            foreach ($selected["works"] as $t) {
                $baseURL=$baseURL."&works[]=".$t;
            }
        }
        if (isset($selected["authors"])){
            foreach ($selected["authors"] as $t) {
                $baseURL=$baseURL."&authors[]=".$t;
            }
        }
        if (isset($selected["dates"])){
            foreach ($selected["dates"] as $t) {
                $baseURL=$baseURL."&dates[]=".$t;
            }
        }

        if (isset($selected["freesearch"])){
            foreach ($selected["freesearch"] as $t) {
                $baseURL=$baseURL."&freesearch[]=".$t;
            }
        }

        return $baseURL;
    }

    /**
     * Create urls for pagination
     * @param $selected : Array with selected params from request
     * @param $result: current Result
     * @return $pageInfo : urls for pagination
     */
    private static function createPages($selected, $result)
    {
        $baseURL=BibList::createCurrentBaseUrl($selected);
        $quantity=$result["hits"]["total"]["value"];
        $pageInfo["pages"]=ceil($quantity/10);
        $pageInfo["first"]=$baseURL."&page="."1";
        $pageInfo["last"]=$baseURL."&page=".ceil($quantity/10);

        if (isset($selected["page"])){
            $pageInfo["current"]=$selected["page"];
        }
        else {
            $pageInfo["current"]=1;
        }

        if ($pageInfo["current"]!=1) {
            $page=$pageInfo["current"]-1;
            $pageInfo["back"]=$baseURL."&page=".$page;
        }
        else {
            $pageInfo["back"]=$pageInfo["first"]=$baseURL."&page="."1";
        }

        if ($pageInfo["current"]!=$pageInfo["pages"]) {
            $page=$pageInfo["current"]+1;
            $pageInfo["next"]=$baseURL."&page=".$page;
        }
        else {
            $pageInfo["next"]=$pageInfo["last_page"];
        }
        return $pageInfo;
    }
    /**
     * Gather for freesearch
     * @param $selected : Array with selected params from request
     * @return $freesearch : term and url
     */
    private static function createFreeSearch($selected)
    {
        $freesearch=array();
        $freesearch["terms"]=array();
        foreach ($selected["freesearch"] as $f){
            $baseURL="bib?";
            if (isset($selected["types"])){
                foreach ($selected["types"] as $t) {
                    $baseURL=$baseURL."&types[]=".$t;
                }
            }
            if (isset($selected["categories"])){
                foreach ($selected["categories"] as $t) {
                    $baseURL=$baseURL."&categories[]=".$t;
                }
            }
            if (isset($selected["works"])){
                foreach ($selected["works"] as $t) {
                    $baseURL=$baseURL."&works[]=".$t;
                }
            }
            if (isset($selected["authors"])){
                foreach ($selected["authors"] as $t) {
                    $baseURL=$baseURL."&authors[]=".$t;
                }
            }
            if (isset($selected["dates"])){
                foreach ($selected["dates"] as $t) {
                    $baseURL=$baseURL."&dates[]=".$t;
                }
            }
            foreach ($selected["freesearch"] as $f2){
                if ($f != $f2) {
                    $baseURL=$baseURL."&freesearch[]=".$f2;
                }
            }
            $term["url"]=$baseURL;
            $term["term"]=$f;
            array_push($freesearch["terms"], $term);
        }
        $freesearch["url"]=BibList::createCurrentBaseUrl($selected);;
        return $freesearch;
    }

    /**
     * Gets organized results
     * @param $selected : Array with selected params from request
     * @return $results : to different kinds of results (byDate, byTitle)
     */
    private static function createResults($selected)
    {
        if (isset($selected["page"])){
            $from=$selected["page"]*10-10;
        } else {
            $from=0;
        }
        $size=10;
        $sort["term"]="date";
        $sort["order"]="desc";

        $results["byDate"]=BibIndex::getIndexWithAggs($selected, $from, $size, $sort);

        $sort["term"]="entry_title";
        $sort["order"]="asc";
        $results["byTitle"]=BibIndex::getIndexWithAggs($selected, $from, $size, $sort);
        return $results;


    }

    /**
     * Create facets for page
     * @param $selected : Array with selected params from request
     * @param $aggs : Aggregations from Elastic Search
     * @return $facets : available facets in order and with url
     */
    private static function createFacets($aggs, $selected)
    {
        $facets=array();

        if (array_key_exists("types", $selected)){
        } else {
            $selected["types"]=array();
        }
        if (array_key_exists("categories", $selected)){
        } else {
            $selected["categories"]=array();
        }
        if (array_key_exists("works", $selected)){
        } else {
            $selected["works"]=array();
        }
        if (array_key_exists("authors", $selected)){
        } else {
            $selected["authors"]=array();
        }
        if (array_key_exists("dates", $selected)){
        } else {
            $selected["dates"]=array();
        }
        // BUILD TYPE FACETS
        if ($selected["types"]!=null) {
            $facets["types"]=[];
            foreach ($aggs["types"]["buckets"] as $type) {
                if (in_array($type["key"], $selected["types"])) {
                    array_push($facets["types"], array('id'=>$type["key"],
                            'selected'=>1,
                            'group'=>"types",
                            'url'=>Tools::urlBuilder($type["key"],'types', $selected, ""),
                            'count'=>$type["doc_count"]
                        )
                    );
                }
                else {
                    array_push($facets["types"], array('id'=>$type["key"],
                            'selected'=>0,
                            'group'=>"types",
                            'url'=>Tools::urlBuilder($type["key"],'types', $selected, ""),
                            'count'=>$type["doc_count"]
                        )
                    );
                }
            }
        }
        else {
            $facets["types"]=[];
            foreach ($aggs["types"]["buckets"] as $type) {
                array_push($facets["types"], array('id'=>$type["key"],
                    'selected'=>0,
                    'group'=>"types",
                    'url'=>Tools::urlBuilder($type["key"],'types', $selected, ""),
                    'count'=>$type["doc_count"]
                    )
                );
            }
        }
        // BUILD CAT FACETS
        if ($selected["categories"]!=null) {
            $facets["categories"]=[];
            foreach ($aggs["categories"]["categories"]["buckets"] as $c) {
                if (in_array($c["key"], $selected["categories"])) {
                    array_push($facets["categories"], array('id'=>$c["key"],
                            'selected'=>1,
                            'group'=>"categories",
                            'url'=>Tools::urlBuilder($c["key"],'categories', $selected, ""),
                            'count'=>$c["doc_count"]
                        )
                    );
                }
                else {
                    array_push($facets["categories"], array('id'=>$c["key"],
                            'selected'=>0,
                            'group'=>"categories",
                            'url'=>Tools::urlBuilder($c["key"],'categories', $selected, ""),
                            'count'=>$c["doc_count"]
                        )
                    );
                }
            }
        }
        else {
            $facets["categories"]=[];
            foreach ($aggs["categories"]["categories"]["buckets"] as $c) {
                array_push($facets["categories"], array('id'=>$c["key"],
                        'selected'=>0,
                        'group'=>"categories",
                        'url'=>Tools::urlBuilder($c["key"],'categories', $selected, ""),
                        'count'=>$c["doc_count"]
                    )
                );
            }
        }

        // BUILD AUTHORS
        if ($selected["authors"]!=null) {
            $facets["authors"]=[];
            foreach ($aggs["authors"]["authors"]["buckets"] as $c) {
                if (in_array($c["key"], $selected["authors"])) {
                    array_push($facets["authors"], array('id'=>$c["key"],
                            'selected'=>1,
                            'group'=>"authors",
                            'url'=>Tools::urlBuilder($c["key"],'authors', $selected, ""),
                            'count'=>$c["doc_count"]
                        )
                    );
                }
                else {
                    array_push($facets["authors"], array('id'=>$c["key"],
                            'selected'=>0,
                            'group'=>"authors",
                            'url'=>Tools::urlBuilder($c["key"],'authors', $selected, ""),
                            'count'=>$c["doc_count"]
                        )
                    );
                }
            }
        }
        else {
            $facets["authors"]=[];
            foreach ($aggs["authors"]["authors"]["buckets"] as $c) {
                array_push($facets["authors"], array('id'=>$c["key"],
                        'selected'=>0,
                        'group'=>"authors",
                        'url'=>Tools::urlBuilder($c["key"],'authors', $selected, ""),
                        'count'=>$c["doc_count"]
                    )
                );
            }
        }
        // BUILD DATES
        if ($selected["dates"]!=null) {
            $facets["dates"]=[];
            foreach ($aggs["dates"]["buckets"] as $c) {
                if (in_array($c["key"], $selected["dates"])) {
                    array_push($facets["dates"], array('id'=>$c["key"],
                            'selected'=>1,
                            'group'=>"dates",
                            'url'=>Tools::urlBuilder($c["key"],'dates', $selected, ""),
                            'count'=>$c["doc_count"]
                        )
                    );
                }
                else {
                    array_push($facets["dates"], array('id'=>$c["key"],
                            'selected'=>0,
                            'group'=>"dates",
                            'url'=>Tools::urlBuilder($c["key"],'dates', $selected, ""),
                            'count'=>$c["doc_count"]
                        )
                    );
                }
            }
        }
        else {
            $facets["dates"]=[];
            foreach ($aggs["dates"]["buckets"] as $c) {
                array_push($facets["dates"], array('id'=>$c["key"],
                        'selected'=>0,
                        'group'=>"dates",
                        'url'=>Tools::urlBuilder($c["key"],'dates', $selected, ""),
                        'count'=>$c["doc_count"]
                    )
                );
            }
        }
        return $facets;
    }
}

