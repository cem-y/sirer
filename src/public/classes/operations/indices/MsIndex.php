<?php
namespace DareOne\operations\indices;
use DareOne\operations\EloquentOperations as EO;
use Elasticsearch\ClientBuilder as CB;
use Elasticsearch\ClientBuilder;


class MsIndex
{
    /*
     *
     */

    public static function prepare()
    {
        $params = ['index' => "manuscripts"];
        $client = CB::create()->build();
        if ($client->indices()->exists($params)) {
            $client->indices()->delete($params);
        };
        $client->indices()->create(MsIndex::configure());
    }


    private static function configure()
    {
        $analyzer = [
            'index' => "manuscripts",
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
                ], 'mappings' => MsIndex::map()
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
                    'language' => [
                        "type" => "keyword"
                    ],
                    'repository' => [
                        "type" => "keyword"
                    ],
                    "settlement" => [
                        "type" => "keyword"
                    ],
                    "country" => [
                        "type" => "keyword"
                    ],
                    "has_images" => [
                        "type" => "integer"
                    ]
                ]

            ];

        return $mapping;
    }

    public static function indexAll()
    {
        $client = CB::create()->build();
        $msEntries = EO::getMsEntries();
        error_log(print_r("Start Indexing Manuscripts"));
        $i=1;
        foreach ($msEntries as $entry) {
            $languages=array();
            foreach($entry["languages"] as $l)
            {
                array_push($languages, $l["language"]["name"]);
            }
            $items=array();
            foreach ($entry["document_items"] as $di)
            {
                $item["from_page_free"]=$di["from_page_free"];
                $item["to_page_free"]=$di["to_page_free"];
                $item["author_free_name"]=$di["author_free_name"];
                $item["work_free_title"]=$di["work_free_title"];

                array_push($items, $item);
            }
            $params = [
                'index' => "manuscripts",
                'id'=>$entry["bilderberg_id"],
                'body' => [
                    'id'=>$entry["id"],
                    'dare_id'=>$entry["ms_base_id"],
                    'language' => $languages,
                    'repository' => $entry["repository"]["repository_name"],
                    'settlement' => $entry["repository"]["settlement"],
                    'country' => $entry["repository"]["country"],
                    'shelfmark' => $entry["idno"],
                    'has_images' => $entry["has_images"],
                    'content_title' => $entry["content_title"],
                    'leaves_height' => $entry["leaves_height"],
                    'leaves_width' => $entry["leaves_width"],
                    'binding_date' => $entry["binding_date"],
                    'items' => $items




                ]
            ];
            $client->index($params);
            error_log(print_r("(Manuscript: ".$i." / ".count($msEntries).") indexed", TRUE));
            $i++;
        }
        error_log(print_r("All Manuscripts indexed", TRUE));
    }

    public static function getIndex()
    {
        $client = CB::create()->build();
        $params["index"] = "manuscripts";
        $params["body"] = [
            "size"=>5000,
            "query"=> ["match_all"=>new \stdClass]


        ];
        return $client->search($params);
    }

    public static function getDocument($id){
        $client=ClientBuilder::create()->build();
        $params=[
            'index'=>"manuscripts",
            'id'=>$id
        ];
        return $client->getSource($params);
    }



    public static function getIndexWithAggs($selected=array(), $from = 0, $size =10, $sort=array())
    {
        if (isset($sort["term"])){
            $client = CB::create()->build();
            $params["index"] = "manuscripts";
            $params["body"] = [
                "from" => $from,
                "size"=>$size,
                "sort"=> [
                    $sort["term"] => [
                        "order" => $sort["order"]
                    ]
                ],
                "query"=> MsIndex::createQuery($selected),
                "aggs"=> MsIndex::createAggs()
            ];
            return $client->search($params);
        } else {
            $client = CB::create()->build();
            $params["index"] = "manuscripts";
            $params["body"] = [
                "from" => $from,
                "size"=>$size,
                "query"=> MsIndex::createQuery($selected),
                "aggs"=> MsIndex::createAggs()
            ];
            return $client->search($params);
        }

    }



    private static function createQuery($selected) {
        if ($selected!=null) {
            $must=array();
            // Languages
            if (isset($selected["languages"])) {
                foreach ($selected["languages"] as $l) {
                    $param = [
                        "term" => [
                            "language" => $l
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            // Countries
            if (isset($selected["countries"])) {
                foreach ($selected["countries"] as $c) {
                    $param = [
                        "term" => [
                            "country" => $c
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            // Countries
            if (isset($selected["settlements"])) {
                foreach ($selected["settlements"] as $s) {
                    $param = [
                        "term" => [
                            "settlement" => $s
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            // Repositories
            if (isset($selected["repositories"])) {
                foreach ($selected["repositories"] as $r) {
                    $param = [
                        "term" => [
                            "repository" => $r
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            $query["bool"]["must"]=$must;
        } else {
            $query = ["match_all" => new \stdClass()];
        }
        return $query;
    }





    private static function createAggs() {
        $aggs = [

            "languages" => [
                "terms" => [
                    "field" => "language",
                    "order" => [
                        "_key" => "desc"
                    ]

                ]
            ],
            "repositories" => [
                "terms" => [
                    "field" => "repository",
                    "size" => 500,
                    "order" => [
                        "_key" => "asc"
                    ]

                ]
            ],
            "settlements" => [
                "terms" => [
                    "field" => "settlement",
                    "size" => 500,
                    "order" => [
                        "_key" => "asc"
                    ]
                ]
            ],
            "countries" => [
                "terms" => [
                    "field" => "country",
                    "size" => 500,
                    "order" => [
                        "_key" => "asc"
                    ]

                ]
            ],
            "has_images" => [
                "terms" => [
                    "field" => "has_images",
                    "size" => 2,
                    "order" => [
                        "_key" => "asc"
                    ]

                ]
            ]

        ];
        return $aggs;
    }


}

