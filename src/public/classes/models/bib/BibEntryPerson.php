<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;

class BibEntryPerson extends BaseModel
{
    protected $fillable = [
        "id",
        "entry_id",
        "person_id",
        "role",
        "free_name",
        "free_first_name",
        "free_last_name"
    ];

    public function norm_person()
    {
        return $this->hasOne("DareOne\models\person\Person","id", "person_id");
    }

    public function role() {
        return $this->hasOne("DareOne\models\bib\BibRole","id", "role");
    }

    public $timestamps = false;
    protected $table = "bib_entry_person";
    protected $primaryKey ="id";
}