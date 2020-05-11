<?php
namespace DareOne\operations\persons;


use DareOne\models\person\Person;
use DareOne\system\DareLogger;
use \Psr\Http\Message\ServerRequestInterface as Request;


class PersonManager
{
    /**
     * Gather Data for Bibliography Page
     * @param $selected : Array with selected params from request
     * @return $data : gathered data to feed page
     */
    public static function getAll()
    {
        $data=Person::all();
        return $data;
    }

    /**
     * @param string $freetext
     * @param string $order
     * @param string $dir
     * @return array
     */
    public static function getFiltered($freetext="", $order="title", $dir="asc"){
        $data=Person::orderBy($order, $dir)
            ->where("full_name", "like", '%'.$freetext.'%')
            ->get()
            ->toArray();
        return $data;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public static function createPerson(Request $request){
        $person=new Person();
        $person->save();
        Person::find($person->id)->update($request->getParsedBody());
        DareLogger::logDbCreate($request, "person_normalised", "DareOne\models\person\Person", $person->id);
        return $person->id;
    }


    /**
     * @param int $id
     * @return array
     */
    public static function getPerson($id=1)
    {
        $data=Person::with("bibliography")
            ->where('id','=', $id)
            ->first()
            ->toArray();

        return $data;
    }

    /**
     * @param Request $request
     */
    public static function updatePerson(Request $request)
    {
        DareLogger::logDbUpdate($request, "person_normalised", "DareOne\models\person\Person");
        Person::find($request->getAttributes()["id"])->update($request->getParsedBody());
    }



}

