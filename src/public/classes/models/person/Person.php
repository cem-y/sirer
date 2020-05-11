<?php


namespace DareOne\models\person;
use DareOne\models\BaseModel;


class Person extends BaseModel
{
    protected $fillable = [
        "id",
        "first_name",
        "last_name",
        "full_name",
        "short_ident",
        "is_classical_name",
        "dnb_url",
        "viaf_url",
        "db_url",
        "from_claudius"
    ];

    protected $table = "person_normalised";
    protected $primaryKey ="id";
    public $timestamps = false;

    public function bibliography()
    {
        return $this->belongsToMany('DareOne\models\bib\BibEntry', 'bib_entry_person', 'person_id', 'entry_id')->orderBy("date", "desc");
    }

    public function role($role_id){
        return $this->hasMany('DareOne\models\bib\BibEntryPerson', 'person_id', 'id')->where('role', '=', $role_id);
    }


}