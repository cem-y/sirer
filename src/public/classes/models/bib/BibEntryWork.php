<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;


class BibEntryWork extends BaseModel
{
    protected $fillable = [
        "id",
        "bib_entry",
        "representation_is_set",
        "work",
        "representation",
        "realization_type"
    ];

    /*public function works()
    {
        return $this->belongsTo("DareOne\models\works\work","work", "id")->with("work_averroes");
    }*/

    public function works()
    {
        return $this->belongsTo("DareOne\models\works\WorkAverroes","work", "id");
    }

    public $timestamps = false;
    protected $table = "bib_entry_work";
    protected $primaryKey ="id";
}