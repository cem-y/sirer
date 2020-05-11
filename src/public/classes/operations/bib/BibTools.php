<?php


namespace DareOne\operations\bib;


use DareOne\models\bib\BibEntry;
use DareOne\operations\indices\BibIndex;

class BibTools
{
    /**
     * @param $selected
     * @param $allCategories
     * @return array
     */
    public static function filterCategories($selected, $allCategories){
        if (count($selected)!=0){
            $filteredCategories=array();
            for ($i=0; $i<count($allCategories); $i++){
                $isInArray=0;
                for ($j=0; $j<count($selected); $j++){

                    if ($allCategories[$i]["id"]==$selected[$j]["id"]){
                        $isInArray=1;
                    }
                }
                if ($isInArray==0){
                    array_push($filteredCategories, $allCategories[$i]);
                }
            }
            return $filteredCategories;
        }
        else {
            return $allCategories;
        }
    }

    public static function indexStatusById($id){
        $index=BibIndex::getDocument($id);
        if ($index!=false){
        $db=BibEntry::with("persons", "categories", "types", "works", "book", "booksection", "article")
            ->where('id','=', $id)
            ->first()
            ->toArray();
        $indexStatus=array();
        $indexStatus["all"]=1;
        if ($index["title"]==$db["title"]){
            $indexStatus["title"]["status"]=1;
            $indexStatus["title"]["msg"]="OK";
        } else {
            $indexStatus["title"]["status"]=0;
            $indexStatus["title"]["msg"]=$index["title"];
            $indexStatus["all"]=0;
        }
        if ($index["btype"]==$db["type"]){
            $indexStatus["type"]["status"]=1;
            $indexStatus["type"]["msg"]="OK";
            if ($index["btype"]==1 || $index["btype"]==4){
                if ($index["book"]["pubplace"]==$db["book"]["pubplace"]){
                    $indexStatus["pubplace"]["status"]=1;
                    $indexStatus["pubplace"]["msg"]="OK";
                } else {
                    $indexStatus["pubplace"]["status"]=0;
                    $indexStatus["pubplace"]["msg"]=$index["pubplace"];
                    $indexStatus["all"]=0;
                }
                if ($index["book"]["publisher"]==$db["book"]["publisher"]){
                    $indexStatus["publisher"]["status"]=1;
                    $indexStatus["publisher"]["msg"]="OK";
                } else {
                    $indexStatus["publisher"]["status"]=0;
                    $indexStatus["publisher"]["msg"]=$index["publisher"];
                    $indexStatus["all"]=0;
                }
                if ($index["book"]["series"]==$db["book"]["series"]){
                    $indexStatus["series"]["status"]=1;
                    $indexStatus["series"]["msg"]="OK";
                } else {
                    $indexStatus["series"]["status"]=0;
                    $indexStatus["series"]["msg"]=$index["series"];
                    $indexStatus["all"]=0;
                }
                if ($index["book"]["volume"]==$db["book"]["volume"]){
                    $indexStatus["volume"]["status"]=1;
                    $indexStatus["volume"]["msg"]="OK";
                } else {
                    $indexStatus["volume"]["status"]=0;
                    $indexStatus["volume"]["msg"]=$index["volume"];
                    $indexStatus["all"]=0;
                }
                if ($index["book"]["edition_no"]==$db["book"]["edition_no"]){
                    $indexStatus["edition_no"]["status"]=1;
                    $indexStatus["edition_no"]["msg"]="OK";
                } else {
                    $indexStatus["edition_no"]["status"]=0;
                    $indexStatus["edition_no"]["msg"]=$index["edition_no"];
                    $indexStatus["all"]=0;
                }
            }
            elseif ($index["btype"]==2){
                if ($index["booksection"]["section_of"]==$db["booksection"]["section_of"]){
                    $indexStatus["section_of"]["status"]=1;
                    $indexStatus["section_of"]["msg"]="OK";
                } else {
                    $indexStatus["section_of"]["status"]=0;
                    $indexStatus["section_of"]["msg"]=$index["section_of"];
                    $indexStatus["all"]=0;
                }
                if ($index["booksection"]["pages"]==$db["booksection"]["pages"]){
                    $indexStatus["pages"]["status"]=1;
                    $indexStatus["pages"]["msg"]="OK";
                } else {
                    $indexStatus["pages"]["status"]=0;
                    $indexStatus["pages"]["msg"]=$index["section_of"];
                    $indexStatus["all"]=0;
                }
            }
            elseif ($index["btype"]==3){
                if ($index["article"]["journal_name"]==$db["article"]["journal_name"]){
                    $indexStatus["journal_name"]["status"]=1;
                    $indexStatus["journal_name"]["msg"]="OK";
                } else {
                    $indexStatus["journal_name"]["status"]=0;
                    $indexStatus["journal_name"]["msg"]=$index["journal_name"];
                    $indexStatus["all"]=0;
                }
                if ($index["article"]["volume"]==$db["article"]["volume"]){
                    $indexStatus["volume"]["status"]=1;
                    $indexStatus["volume"]["msg"]="OK";
                } else {
                    $indexStatus["volume"]["status"]=0;
                    $indexStatus["volume"]["msg"]=$index["volume"];
                    $indexStatus["all"]=0;
                }
                if ($index["article"]["issue"]==$db["article"]["issue"]){
                    $indexStatus["issue"]["status"]=1;
                    $indexStatus["issue"]["msg"]="OK";
                } else {
                    $indexStatus["issue"]["status"]=0;
                    $indexStatus["issue"]["msg"]=$index["issue"];
                    $indexStatus["all"]=0;
                }
                if ($index["article"]["pages"]==$db["article"]["pages"]){
                    $indexStatus["pages"]["status"]=1;
                    $indexStatus["pages"]["msg"]="OK";
                } else {
                    $indexStatus["pages"]["status"]=0;
                    $indexStatus["pages"]["msg"]=$index["pages"];
                    $indexStatus["all"]=0;
                }

            }
        } else {
            $indexStatus["type"]["status"]=0;
            $indexStatus["type"]["msg"]=$index["btype"];
            $indexStatus["all"]=0;
        }
        if ($index["date"]==$db["date"]){
            $indexStatus["date"]["status"]=1;
            $indexStatus["date"]["msg"]="OK";
        } else {
            $indexStatus["date"]["status"]=0;
            $indexStatus["date"]["msg"]=$index["date"];
            $indexStatus["all"]=0;
        }

        return $indexStatus;
        } else {
            return "No Index";
        }


    }
}