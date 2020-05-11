<?php
namespace DareOne\operations\bib;

use DareOne\models\bib\BibArticle;
use DareOne\models\bib\BibBook;
use DareOne\models\bib\BibBookSection;
use DareOne\models\bib\BibCategory;
use DareOne\models\bib\BibEntry;
use DareOne\models\bib\BibEntryCategory;
use DareOne\models\bib\BibEntryPerson;
use DareOne\models\person\Person;
use DareOne\operations\EloquentOperations;
use DareOne\operations\indices\BibIndex;
use DareOne\operations\Tools;
use DareOne\system\DareLogger;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * List all bibliographic entries
 *
 */
class BibManager
{
    /**
     * Gather Data for Bibliography Page
     * @param $selected : Array with selected params from request
     * @return $data : gathered data to feed page
     */
    public static function getAll()
    {
        $data=BibEntry::with("persons", "types")->get();
        return $data;
    }

    /**
     * @param string $freetext
     * @param string $order
     * @param string $dir
     * @return array
     */
    public static function getFiltered($freetext="", $order="title", $dir="asc"){
        $data=BibEntry::with("persons")
            ->orderBy($order, $dir)
            ->where("title", "like", '%'.$freetext.'%')
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getEntry($id=1)
    {
        $data=BibEntry::with("persons", "categories", "types", "book", "booksection", "article")
            ->where('id','=', $id)
            ->first()
            ->toArray();
        $allCategories=BibCategory::orderBy("category_name")
            ->get();
        $data["all_categories"]=BibTools::filterCategories($data["categories"], $allCategories);
        $data["all_persons"]=Person::orderBy("full_name")
            ->get();
        if($data["type"]==2){
            $data["all_bib"]=BibEntry::orderBy("title", "asc")->get()->toArray();
        }
        if ($data["is_inactive"]!=1){
            //$data["index_status"]=BibTools::indexStatusById($id);
        }
        return $data;
    }

    /**
     * @param Request $request
     */
    public static function updateEntry(Request $request)
    {
        DareLogger::logDbUpdate($request, "bib_entry", "DareOne\models\bib\BibEntry");
        BibEntry::find($request->getAttributes()["id"])->update($request->getParsedBody());
        if(isset($request->getParsedBody()["index"]) && $request->getParsedBody()["is_inactive"]!=1){
            BibIndex::indexDocument($request->getAttributes()["id"]);
        }
        if($request->getParsedBody()["is_inactive"]==1){
            BibIndex::deleteDocument($request->getAttributes()["id"]);
        }
    }

    /**
     * @param Request $request
     */
    public static function updateOrCreateBook(Request $request){
        if(BibBook::where("id", "=", $request->getAttributes()["id"])->exists()){
            DareLogger::logDbUpdate($request, "bib_book", "DareOne\models\bib\BibBook");
            BibBook::find($request->getAttributes()["id"])->update($request->getParsedBody());
        } else {
            $entry=new BibBook();
            $entry->id=$request->getAttributes()["id"];
            $entry->save();
            BibBook::find($request->getAttributes()["id"])->update($request->getParsedBody());
            DareLogger::logDbCreate($request, "bib_book", "DareOne\models\bib\BibBook", $request->getAttributes()["id"]);
        }
    }

    /**
     * @param Request $request
     */
    public static function updateOrCreateBookSection(Request $request){
        if(BibBookSection::where("id", "=", $request->getAttributes()["id"])->exists()){
            DareLogger::logDbUpdate($request, "bib_booksection", "DareOne\models\bib\BibBookSection");
            BibBookSection::find($request->getAttributes()["id"])->update($request->getParsedBody());
        } else {
            $entry=new BibBookSection();
            $entry->id=$request->getAttributes()["id"];
            $entry->save();
            BibBookSection::find($request->getAttributes()["id"])->update($request->getParsedBody());
            DareLogger::logDbCreate($request, "bib_booksection", "DareOne\models\bib\BibBookSection", $request->getAttributes()["id"]);
        }
    }

    /**
     * @param Request $request
     */
    public static function updateOrCreateArticle(Request $request){
        if(BibArticle::where("id", "=", $request->getAttributes()["id"])->exists()){
            DareLogger::logDbUpdate($request, "bib_article", "DareOne\models\bib\BibArticle");
            BibArticle::find($request->getAttributes()["id"])->update($request->getParsedBody());
        } else {
            $entry=new BibArticle();
            $entry->id=$request->getAttributes()["id"];
            $entry->save();
            BibArticle::find($request->getAttributes()["id"])->update($request->getParsedBody());
            DareLogger::logDbCreate($request, "bib_article", "DareOne\models\bib\Bibrticle", $request->getAttributes()["id"]);
        }
    }

    /**
     * @param Request $request
     */
    public static function addCategory(Request $request){
        $bibCategory=new BibEntryCategory();
        $bibCategory->category_id=$request->getParsedBody()["cat_id"];
        $bibCategory->bib_entry_id=$request->getAttributes()["id"];
        $bibCategory->save();
        DareLogger::logDbCreate($request, "bib_entry_category", "DareOne\models\bib\BibEntryCategory", $bibCategory->id);
    }

    /**
     * @param Request $request
     */
    public static function deleteCategory(Request $request){
        $bibCategory=BibEntryCategory::where("bib_entry_id", "=", $request->getAttributes()["id"])
            ->where("category_id", "=", $request->getAttributes()["cat_id"])
            ->first()
            ->toArray();
        DareLogger::logDbDelete($request, "bib_entry_category", "DareOne\models\bib\BibEntryCategory", $bibCategory["id"]);
        $bibCategory=BibEntryCategory::where("bib_entry_id", "=", $request->getAttributes()["id"])
            ->where("category_id", "=", $request->getAttributes()["cat_id"]);
        $bibCategory->delete();
    }

    /**
     * @param Request $request
     */
    public static function addPerson(Request $request){
        $bibPerson=new BibEntryPerson();
        $bibPerson->free_name=$request->getParsedBody()["free_name"];
        if (isset($request->getParsedBody()["first_free_name"])){
            $bibPerson->free_first_name=$request->getParsedBody()["first_free_name"];
        }
        if (isset($request->getParsedBody()["last_free_name"])){
            $bibPerson->free_last_name=$request->getParsedBody()["last_free_name"];
        }
        $bibPerson->role=$request->getParsedBody()["role_id"];
        $bibPerson->agent_type="person";
        if (isset($request->getParsedBody()["person_id"])){
            if ($request->getParsedBody()["person_id"]!="x"){
                $bibPerson->person_id=$request->getParsedBody()["person_id"];
                $bibPerson->is_normalised=1;
            }
        }
        $bibPerson->entry_id=$request->getAttributes()["id"];
        $bibPerson->save();
        DareLogger::logDbCreate($request, "bib_entry_person", "DareOne\models\bib\BibEntryPerson", $bibPerson->id);
    }


    public static function updatePerson(Request $request){
        DareLogger::logDbUpdate($request, "bib_entry_person", "DareOne\models\bib\BibEntryPerson");
        BibEntryPerson::find($request->getAttributes()["id"])->update($request->getParsedBody());

    }

    public static function deletePerson(Request $request){
        DareLogger::logDbDelete($request, "bib_entry_person", "DareOne\models\bib\BibEntryPerson", $request->getAttributes()["person_id"]);
        $bibPerson=BibEntryPerson::where("id", "=", $request->getAttributes()["person_id"]);
        $bibPerson->delete();
    }



}

