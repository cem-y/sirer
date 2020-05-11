<?php
namespace DareOne\operations\indices;
use DareOne\models\fulltext\FulltextSection;
use DareOne\operations\EloquentOperations as EO;
use Elasticsearch\ClientBuilder as CB;



class FulltextSectionIndex
{
    /*
     *
     */


    public static function index()
    {
        FulltextSectionIndex::prepare();
        FulltextSectionIndex::createIndex();

        return;
    }

    public static function prepare()
    {
        $params = ['index' => 'fulltext_sections'];
        $client = CB::create()->build();
        if ($client->indices()->exists($params)) {
            $client->indices()->delete($params);
        };
        return $client->indices()->create(FulltextSectionIndex::configure());
    }


    private static function configure()
    {
        $analyzer = [
            'index' => 'fulltext_sections',
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        "analyzer" => [
                            "dare_analyzer" => [
                                "tokenizer" => "standard",
                                "filter" => ["lowercase", "asciifolding"]
                            ]
                        ]
                    ]
                ], 'mappings' => FulltextSectionIndex::map()
            ]];
        return $analyzer;
    }

    private static function map()
    {

                    $mapping = [
                        'properties' => [
                            'free_text' => [
                                'type' => 'text',
                                "analyzer" => "dare_analyzer"
                            ],
                            'id' => [
                                "type" => "integer"
                            ],
                            'fulltext_id' => [
                                "type" => "integer"
                            ],
                            'text' => [
                                "type" => "text"
                            ],
                            'author' => [
                                "type" => "keyword"
                            ],
                            'translator' => [
                                "type" => "keyword"
                            ],
                            'language' => [
                                "type" => "integer"
                            ],
                            'rep_title' => [
                                "type" => "keyword"
                            ],
                            'aw_title' => [
                                "type" => "keyword"
                            ],
                            'aw_id' => [
                                "type" => "keyword"
                            ],
                            'idno' => [
                                "type" => "keyword"
                            ]


                        ]




                    ];
        return $mapping;
    }

    public static function createIndex(){
        $client = CB::create()->build();
        $sections =  FulltextSection::with("items", "fulltextBase")
            ->get()
            ->toArray();
        error_log(print_r($sections[0], true));
        foreach ($sections as $s){
            $sectionText="";
            foreach ($s["items"] as $item){
                if ($item["item_type"] == "text") {
                    $textLine=str_replace("-", "", $item["item_text"]);
                    $sectionText = $sectionText.$textLine;
                }
                if ($item["item_type"] == "linebreak") {
                    $sectionText = $sectionText." ";
                }
            }
            $params = [
                'index' => 'fulltext_sections',
                'body' => [
                    'id'=>$s["id"],
                    'section_order'=>$s["section_order"],
                    'page_label'=>$s["page_label"],
                    'text'=>$sectionText,
                    'fulltext_id'=>$s["fulltext_base"]["id"],
                    'idno'=>$s["fulltext_base"]["idno"],
                    'short_title'=>$s["fulltext_base"]["short_title"],
                    'display_title'=>$s["fulltext_base"]["display_title"],
                    'is_active'=>$s["fulltext_base"]["is_active"],
                    'fulltext_type'=>$s["fulltext_base"]["fulltext_type"],
                    'language'=>$s["fulltext_base"]["language"],
                    'rep_title'=>$s["fulltext_base"]["work"]["work"]["rep_title"],
                    'aw_id'=>$s["fulltext_base"]["work"]["work"]["aw_id"],
                    'aw_title' => $s["fulltext_base"]["work"]["work"]["averroes_work"]["aw_title"],
                    'category' => $s["fulltext_base"]["work"]["work"]["averroes_work"]["category"]["category_name"],
                    'translator'=>$s["fulltext_base"]["work"]["work"]["translator"],
                    'author'=>$s["fulltext_base"]["work"]["work"]["author"],
                    'repository' => $s["fulltext_base"]["work"]["work"]["repository"]["repository_name"],
                    'settlement' => $s["fulltext_base"]["work"]["work"]["repository"]["settlement"],
                    'country' => $s["fulltext_base"]["work"]["work"]["repository"]["country"]
                ]
            ];
            error_log(print_r("(Section: ".$s["id"]." / Fulltext: ".$s["fulltext_base"]["idno"].") indexed", TRUE));
            $client->index($params);

        }
        return "Done";

    }


    public static function getIndex($freetext="etiam")
    {
        $client = CB::create()->build();
        $params["index"] = "fulltext_sections";
        $params["body"] = [
            "size"=>20,
            "sort"=> [
                "id" => ["order" => "asc"]
            ],
            "query"=> [
                "match_phrase"=> [
                    "text" => [
                        "query" => $freetext
                    ]
                ]
            ],
            "highlight"=> [
                "fields" => [
                    "text" => new \stdClass()
                ]
            ]


        ];

        return $client->search($params);
    }

    public static function getIndexWithAggs($selected=array(), $from=0, $size=5000)
    {
        $client = CB::create()->build();
        $params["index"] = "fulltext_sections";
        $params["body"] = [
            "from"=>$from,
            "size"=>$size,
            "sort"=> [
                "id" => ["order" => "asc"]
            ],
            "query"=> FulltextSectionIndex::createQuery($selected),
            "highlight"=> [
                "fields" => [
                    "text" => new \stdClass()
                ]
            ],
            "aggs"=>FulltextSectionIndex::createAggs()


        ];
        return $client->search($params);
    }

    private static function createQuery($selected){
        if ($selected!=null){
            $must=array();
            $param=[
                "bool" => [
                    "must" => [
                        [
                            "match_phrase"=> [
                                "text" =>  $selected["freetext"][0]

                            ]
                        ]
                    ]
                ]

            ];
            array_push($must, $param);
            if (isset($selected["aw"])) {
                foreach ($selected["aw"] as $aw){
                    $param = [
                        "term" => [
                            "aw_title" => $aw
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            if (isset($selected["fulltext"])) {

                    $param = [
                        "term" => [
                            "idno" => $selected["fulltext"]
                        ]
                    ];
                    array_push($must, $param);
            }
            if (isset($selected["translator"])) {
                foreach ($selected["translator"] as $l){
                    $param = [
                        "term" => [
                            "translator" => $l
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            if (isset($selected["language"])) {
                foreach ($selected["language"] as $l){
                    $param = [
                        "term" => [
                            "language" => $l
                        ]
                    ];
                    array_push($must, $param);
                }
            }


            $query["bool"]["must"]=$must;

        } else {
            $query = ["match_all" => new \stdClass()];
        }
        //error_log(print_r($must, true));

        return $query;


    }

    private static function createAggs() {
        $aggs = [
            "aw" => [
                "terms" => [
                    "field" => "aw_title",
                    "order" => [
                        "_key" => "asc"
                    ]

                ]
            ],
            "translator" => [
                "terms" => [
                    "field" => "translator",
                    "order" => [
                        "_key" => "asc"
                    ]

                ]
            ],
            "language" => [
                "terms" => [
                    "field" => "language",
                    "order" => [
                        "_key" => "desc"
                    ]
                ]
            ],
        ];
        return $aggs;
    }




}

