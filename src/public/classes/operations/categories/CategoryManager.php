<?php
namespace DareOne\operations\categories;


use DareOne\models\bib\BibCategory;
use DareOne\system\DareLogger;
use \Psr\Http\Message\ServerRequestInterface as Request;


class CategoryManager
{

    public static function getAll()
    {
        $data=BibCategory::all();
        return $data;
    }

    /**
     * @param string $freetext
     * @param string $order
     * @param string $dir
     * @return array
     */
    public static function getFiltered($freetext="", $order="title", $dir="asc"){
        $data=BibCategory::orderBy($order, $dir)
            ->where("category_name", "like", '%'.$freetext.'%')
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public static function createEntry(Request $request){
        $category=new BibCategory();
        $category->save();
        BibCategory::find($category->id)->update($request->getParsedBody());
        DareLogger::logDbCreate($request, "bib_category", "DareOne\models\bib\BibCategory", $category->id);
        return $category->id;
    }


    /**
     * @param int $id
     * @return array
     */
    public static function getEntry($id=1)
    {
        $data=BibCategory::with("bibliography")->where('id','=', $id)
            ->first()
            ->toArray();
        return $data;
    }

    /**
     * @param Request $request
     */
    public static function updateEntry(Request $request)
    {
        DareLogger::logDbUpdate($request, "bib_category", "DareOne\models\bib\BibCategory");
        BibCategory::find($request->getAttributes()["id"])->update($request->getParsedBody());
    }



}

