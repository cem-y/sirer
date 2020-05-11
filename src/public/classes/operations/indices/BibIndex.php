<?php
namespace DareOne\operations\indices;
use DareOne\models\bib\BibEntry;
use DareOne\operations\EloquentOperations as EO;
use DareOne\system\DareLogger;
use Elasticsearch\ClientBuilder as CB;
use Elasticsearch\ClientBuilder;


class BibIndex
{
    /*
     *
     */

    public static function prepare()
    {
        $params = ['index' => "bib"];
        $client = CB::create()->build();
        if ($client->indices()->exists($params)) {
            $client->indices()->delete($params);
        };
        $client->indices()->create(BibIndex::configure());
    }


    private static function configure()
    {
        $analyzer = [
            'index' => 'bib',
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
                ], 'mappings' => BibIndex::map()
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
                    'title' => [
                        'type' => 'keyword',
                    ],
                    'btype' => [
                        'type' => 'integer',
                    ],
                    'volume' => [
                        'type' => 'text',
                    ],
                    'author_id' => [
                        'type' => 'integer',
                    ],
                    'author_name' => [
                        'type' => 'keyword',
                    ],
                    'author_free_name' => [
                        'type' => 'text',
                    ],
                    'date' => [
                        'type' => 'integer',
                    ],
                    'categories' => [
                        'type' => 'nested',
                        'properties' => [
                            "category_name" => [
                                "type" => "keyword"
                            ],
                            "id" => [
                                "type" => "integer"
                            ]
                        ]
                    ],
                    'authors' => [
                        'type' => 'nested',
                        'properties' => [
                            "full_name" => [
                                "type" => "keyword"
                            ],
                            "id" => [
                                "type" => "integer"
                            ]
                        ]
                    ],
                    'works' => [
                        'type' => 'nested',
                        'properties' => [
                            "aw_title" => [
                                "type" => "keyword"
                            ],
                            "id" => [
                                "type" => "integer"
                            ]
                        ]
                    ],
                    'abstract' => [
                        'type' => 'text',
                    ],
                    'bib_type' => [
                        'type' => 'keyword',
                    ],
                    'edited_title' => [
                        'type' => 'text',
                    ],
                    'pub_place' => [
                        'type' => 'text',
                    ],
                    'publisher' => [
                        'type' => 'text',
                    ],
                    'series' => [
                        'type' => 'text',
                    ],
                    'series_volume' => [
                        'type' => 'text',
                    ],
                    'journal_name' => [
                        'type' => 'text',
                    ],
                    'journal_volume' => [
                        'type' => 'text',
                    ],
                    'journal_issue' => [
                        'type' => 'text',
                    ],
                    'pages' => [
                        'type' => 'text',
                    ],
                    "is_catalog" => [
                        "type" => "boolean"
                    ]
                ]
            ];
        return $mapping;
    }

    public static function indexAll()
    {
        $client = CB::create()->build();
        $bibEntries = BibEntry::with("persons", "categories", "types", "works", "book", "article", "booksection")->get();
        $i=1;

        foreach ($bibEntries as $bibEntry) {
            $categories=array();

            foreach ($bibEntry["categories"] as $c){
                $category["id"]=$c["id"];
                $category["category_name"]=$c["category_name"];
                array_push($categories, $category);
            }

            $authors=array();
            $authorsFree=array();
            foreach ($bibEntry["persons"] as $p) {
                if (($p["role"]==1 or $p["role"]==2) and $p["norm_person"]!=null){
                    $author["id"]=$p["norm_person"]["id"];
                    $author["full_name"]=$p["norm_person"]["full_name"];
                    $author["role"]=$p["role"];
                    array_push($authors, $author);
                    array_push($authorsFree, $p["norm_person"]["full_name"]);
                }
            }

            $works=array();
            foreach ($bibEntry["works"] as $w) {
                $work["id"]=$w["id"];
                $work["aw_title"]=$w["aw_title"];
                array_push($works, $work);
            }



            $params = [
                'index' => 'bib',
                'id'=>$bibEntry["id"],
                'body' => [
                    'id'=>$bibEntry["id"],
                    'authors_free'=> $authorsFree,
                    'title'=>$bibEntry["title"],
                    'btype' => $bibEntry["type"],
                    'date' => $bibEntry["date"],
                    'categories' =>$categories,
                    'authors' =>$authors,
                    'works' => $works,
                    'book' => $bibEntry["book"],
                    'booksection' => $bibEntry["booksection"],
                    'article' => $bibEntry["article"]
                ]
            ];

            $client->index($params);
            error_log(print_r("(Bibliographic Entry: ".$i." / ".count($bibEntries).") indexed", TRUE));
            $i++;
        }
    }

    public static function getIndex()
    {
        $client = CB::create()->build();
        $params["index"] = "bib";
        $params["body"] = [
            "size"=>5000,
            "query"=> ["match_all"=>new \stdClass]


        ];
        return $client->search($params);
    }

    public static function getIndexWithAggs($selected=array(), $from = 0, $size =10, $sort)
    {
        $client = CB::create()->build();
        $params["index"] = "bib";
        $params["body"] = [
            "from" => $from,
            "size"=>$size,
            "sort"=> [
                $sort["term"] => [
                    "order" => $sort["order"]
                ]
            ],
            "query"=> BibIndex::createQuery($selected),
            "aggs"=> BibIndex::createAggs()
        ];
        return $client->search($params);

    }

    private static function createQuery($selected) {
        if ($selected!=null) {
            $must=array();
            // TYPES
            if (isset($selected["types"])) {
                foreach ($selected["types"] as $t){
                    $param=[
                        "term" => [
                            "btype" => $t
                        ]
                            ];
                    array_push($must, $param);
                }
            }

            // FREE SEARCH

            if (isset($selected["freesearch"])) {
                foreach ($selected["freesearch"] as $t){

                    // the "match" works, but it should be "match_phrase_prefix"
                    // https://stackoverflow.com/questions/57383975/match-phrase-prefix-query-not-working-with-nested-aggregation
                    $param=[
                        "bool" => [
                            "should" => [
                                [
                                "multi_match" => [
                                    "query" => $t,
                                    "type" => "phrase_prefix",
                                    ]
                                ],
                                [
                                "nested" =>  [
                                    "path" => "authors",
                                    "query" => [
                                        "match" => [
                                            "authors.full_name" => [
                                                "query" => $t
                                                            ]
                                                        ]
                                                ]
                                            ]

                                        ]
                                    ]
                                ]
                             ];

                    array_push($must, $param);
                }
            }
            // CATEGORIES
            if (isset($selected["categories"])) {
                foreach ($selected["categories"] as $c){
                    $param=[
                        "nested" =>  [
                            "path" => "categories",
                            "query" => [
                                "bool" => [
                                    "must" => [
                                        "term" => [
                                            "categories.category_name" => $c
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            // WORKS
            if (isset($selected["works"])) {
                foreach ($selected["works"] as $c){
                    $param=[
                        "nested" =>  [
                            "path" => "works",
                            "query" => [
                                "bool" => [
                                    "must" => [
                                        "term" => [
                                            "works.aw_title" => $c
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    array_push($must, $param);
                }
            }
            // AUTHORS
            if (isset($selected["authors"])) {
                foreach ($selected["authors"] as $c){
                    $param=[
                        "nested" =>  [
                            "path" => "authors",
                            "query" => [
                                "bool" => [
                                    "must" => [
                                        "term" => [
                                            "authors.full_name" => $c
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    array_push($must, $param);
                }
            }

            // DATES
            if (isset($selected["dates"])) {
                foreach ($selected["dates"] as $d){
                    $range=array();
                    if ($d=="before 1800") {
                        $range["lt"]=1800;
                    }
                    if ($d=="1801 - 1900") {
                        $range["gte"]=1801;
                        $range["lte"]=1900;
                    }
                    if ($d=="1901 - 1950") {
                        $range["gte"]=1901;
                        $range["lte"]=1950;
                    }
                    if ($d=="1951 - 1970") {
                        $range["gte"]=1951;
                        $range["lte"]=1970;
                    }
                    if ($d=="1971 - 1990") {
                        $range["gte"]=1971;
                        $range["lte"]=1990;
                    }
                    if ($d=="1991 - 2000") {
                        $range["gte"]=1991;
                        $range["lte"]=2000;
                    }
                    if ($d=="2001 - today") {
                        $range["gte"]=2001;
                    }
                    $param=[
                            "range" => [
                                "date" => [
                                        $range
                                    ]
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
            "categories" => [
                "nested" => [
                    "path" => "categories"
                ],
                "aggs" => [
                    "categories" => [
                        "terms" => [
                            "field" => "categories.category_name",
                            "size" => 200,
                            "order" => [
                                "_key" => "asc"
                            ]
                        ]
                    ]
                ]
            ],
            "types" => [
                "terms" => [
                    "field" => "btype",
                    "order" => [
                        "_key" => "asc"
                    ]

                ]
            ],
            "authors" => [
                "nested" => [
                    "path" => "authors"
                ],
                "aggs" => [
                    "authors" => [
                        "terms" => [
                            "field" => "authors.full_name",
                            "size" => 5000,
                            "order" => [
                                "_key" => "asc"
                            ]


                        ]
                    ]
                ]
            ],
            "works" => [
                "nested" => [
                    "path" => "works"
                ],
                "aggs" => [
                    "works" => [
                        "terms" => [
                            "field" => "works.aw_title",
                            "size" => 500,
                            "order" => [
                                "_key" => "asc"
                            ]
                        ]
                    ]
                ]
            ],
            "dates" => [
                "range" => [
                    "field" => "date",
                    "ranges" => [
                        ["key"=>"before 1800", "from"  =>  0, "to" => 1800], ["key"=>"1801 - 1900", "from" => 1801,"to" => 1900], ["key"=>"1901 - 1950", "from" => 1901,"to" => 1950],
                        ["key"=>"1951 - 1970", "from" => 1951,"to" => 1970], ["key"=>"1971 - 1990", "from" => 1971,"to" => 1990], ["key"=>"1991 - 2000", "from" => 1991,"to" => 2000],
                        ["key"=>"2001 - today", "from" => 2001]
                    ]
                ]
            ]
        ];
        return $aggs;
    }

    public static function getDocument($id){

        $client=ClientBuilder::create()->build();

        if(self::existsDocument($id)){
            $params=[
                'index'=>"bib",
                'id'=>$id
            ];
            return $client->getSource($params);
        } else {
            return false;
        }
    }


    public static function existsDocument($id){
        $client=ClientBuilder::create()->build();
        $params=[
            'index'=>"bib",
            'body'=>['size' => 1,
                "query" =>
                    ["match"=>
                        ["id" => $id
                        ]
                    ]
            ]];
        if ($client->search($params)["hits"]["total"]["value"]==1){
            return true;
        } else {
            return false;
        }
    }

    public static function deleteDocument($id){
        if(self::existsDocument($id)) {
            $client = ClientBuilder::create()->build();
            $params = [
                'index' => "bib",
                'id' => $id
            ];
            $client->delete($params);
        }

    }

    public static function indexDocument($id){
        $client=ClientBuilder::create()->build();
        $bibEntry=BibEntry::where("id", "=", $id)
            ->with("persons", "categories", "types", "works", "book", "article", "booksection")
            ->first();
        $categories=array();
        foreach ($bibEntry["categories"] as $c){
            $category["id"]=$c["id"];
            $category["category_name"]=$c["category_name"];
            array_push($categories, $category);
        }
        $authors=array();
        $authorsFree=array();
        foreach ($bibEntry["persons"] as $p) {
            if (($p["role"]==1 or $p["role"]==2) and $p["norm_person"]!=null){
                $author["id"]=$p["norm_person"]["id"];
                $author["full_name"]=$p["norm_person"]["full_name"];
                $author["role"]=$p["role"];
                array_push($authors, $author);
                array_push($authorsFree, $p["norm_person"]["full_name"]);
            }
        }
        $works=array();
        foreach ($bibEntry["works"] as $w) {
            $work["id"]=$w["id"];
            $work["aw_title"]=$w["aw_title"];
            array_push($works, $work);
        }
        $params = [
            'index' => 'bib',
            'id'=>$bibEntry["id"],
            'body' => [
                'id'=>$bibEntry["id"],
                'authors_free'=> $authorsFree,
                'title'=>$bibEntry["title"],
                'btype' => $bibEntry["type"],
                'date' => $bibEntry["date"],
                'categories' =>$categories,
                'authors' =>$authors,
                'works' => $works,
                'book' => $bibEntry["book"],
                'booksection' => $bibEntry["booksection"],
                'article' => $bibEntry["article"]
            ]
        ];
        $client->index($params);
        DareLogger::logDebug("INDEX: bibentry (".$bibEntry["id"].") indexed");
        error_log("Indexing complete...");
    }




}

