<?php
namespace DareOne\operations\utilities;
use GuzzleHttp\Client;


class Tools
{

    /**
     * @param $value
     * @param $group
     * @param $selected : array of already selected facets
     * @param $area
     * @return string
     */
    public static function urlBuilder($value, $group, $selected, $area){
        /*
         * current $value / $group -> $value / $group for investigated $facet
         * $selected ->
         */

        $baseURL=$area."?";
        $selectedWithPage=$selected;

        //instead of manually unsetting non array params, we should investigate if the param is an array or not and unset then
        unset($selected["page"]);
        unset($selected["aw_id"]);
        unset($selected["work_id"]);
        unset($selected["term"]);
        unset($selected["order"]);
        //unset($selected["fulltexts"]);
        unset($selected["ic-request"]);
        unset($selected["ic-id"]);
        unset($selected["ic-target-id"]);
        unset($selected["ic-current-url"]);
        unset($selected["_method"]);
        unset($selected["load_results"]);
        unset($selected["work_filter"]);

        $selectedGroups = array_keys($selected);

        if (in_array($value, $selected[$group])){
            // if the value is already selected in its specific group, construct the custom url without the value to deselect
            $facetURL = $baseURL;
            foreach ($selectedGroups as $sg){
                // because there could be the same value for different facet types, we have to proof that the right value should be compared
                foreach ($selected[$sg] as $s){
                    if ($value != $s){
                        $facetURL=$facetURL."&".$sg."[]=".$s;
                    }
                    elseif ($group != $sg) {
                        $facetURL=$facetURL."&".$sg."[]=".$s;
                    }
                }
            }
        }
        else {
            $facetURL = $baseURL.$group."[]=".$value;
            foreach ($selectedGroups as $sg){
                // because there could be the same value for different facet types, we have to proof that the right value should be compared
                foreach ($selected[$sg] as $s){
                    $facetURL=$facetURL."&".$sg."[]=".$s;
                }
            }
        }
        if(isset($selected["fulltexts"])){
            $facetURL=$facetURL."&fulltexts=".$selected["fulltexts"];
        }
        return $facetURL;
    }

    /**
     * @param string $url
     * @return array
     */
    public static function getImageDimensions($url){
        $client = new Client(['verify' => false ]);
        $response = $client->request('GET', $url);
        $json=json_decode($response->getBody()->getContents());
        $dimension["height"]= $json->height;
        $dimension["width"]=$json->width;
        return $dimension;
    }
}

