<?php
namespace DareOne\operations\indices;
use DareOne\operations\EloquentOperations as EO;
use Elasticsearch\ClientBuilder as CB;



class WorkIndex
{
    /*
     *
     */

    public static function prepare()
    {
        $params = ['index' => "works"];
        $client = CB::create()->build();
        if ($client->indices()->exists($params)) {
            $client->indices()->delete($params);
        };
        $client->indices()->create(WorkIndex::configure());
    }


    private static function configure()
    {
        $analyzer = [
            'index' => "works",
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
                ], 'mappings' => WorkIndex::map()
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
                    'translator' => [
                        "type" => "keyword"
                    ],
                    "category" => [
                        "type" => "keyword"
                    ],
                    "aw_title" => [
                        "type" => "keyword"
                    ]
                ]

            ];
         return $mapping;
    }

    public static function indexAll()
    {
        $client = CB::create()->build();
        $workEntries = EO::getWorkEntries();

        $i=1;
        foreach ($workEntries as $entry) {



            $params = [
                'index' => 'works',
                'id'=>$entry["id"],
                'body' => [
                    'id'=>$entry["id"],
                    'rep_id' => $entry["rep_id"],
                    'rep_title'=>$entry["rep_title"],
                    'language' => $entry["language"],
                    'category' => $entry["averroes_work"]["category"]["category_name"],
                    'translator' => $entry["translator"],
                    'aw_id' => $entry["averroes_work"]["aw_id"],
                    'aw_title' => $entry["averroes_work"]["aw_title"],
                    'aw_order' => $entry["averroes_work"]["aw_order"]
                ]
            ];
            $client->index($params);
            error_log(print_r("(Work: ".$i." / ".count($workEntries).") indexed", TRUE));
            $i++;

        }
    }

    public static function getIndex()
    {
        $client = CB::create()->build();
        $params["index"] = "works";
        $params["body"] = [
            "size"=>5000,
            "query"=> ["match_all"=>new \stdClass]


        ];
        return $client->search($params);
    }

    public static function getIndexWithAggs($selected=array(), $from = 0, $size =9999, $sort=array())
    {
        $client = CB::create()->build();
        $params["index"] = "works";
        $params["body"] = [
            "from" => $from,
            "size"=>$size,
            "query"=> WorkIndex::createQuery($selected),
            "aggs"=> WorkIndex::createAggs()
        ];
        return $client->search($params);

    }


    private static function createQuery($selected) {
        if ($selected!=null) {
            $must=array();
            // Categories
            if (isset($selected["categories"])) {
                foreach ($selected["categories"] as $c) {
                    $param = [
                        "term" => [
                            "category" => $c
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            // Translators
            if (isset($selected["translators"])) {
                foreach ($selected["translators"] as $t) {
                    $param = [
                        "term" => [
                            "translator" => $t
                        ]
                    ];
                    array_push($must, $param);
                }
            }
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
                    "size" => 500,
                    "order" => [
                        "_key" => "desc"
                    ]

                ]
            ],
            "categories" => [
                "terms" => [
                    "field" => "category",
                    "size" => 500,
                    "order" => [
                        "_count" => "desc"
                    ]

                ]
            ],
            "translators" => [
                "terms" => [
                    "field" => "translator",
                    "size" => 500,
                    "order" => [
                        "_key" => "asc"
                    ]
                ]
            ],
            "aw_titles" => [
                "terms" => [
                    "field" => "aw_title",
                    "size" => 500,
                    "order" => [
                        "_key" => "asc"
                    ]
                ]
            ]

        ];
        return $aggs;
    }


}

