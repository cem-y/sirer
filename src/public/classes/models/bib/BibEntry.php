<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;


class BibEntry extends BaseModel
{
    protected $fillable = [
        "id",
        "bilderberg_idno",
        "dare_idno",
        "catalog_idno",
        "entry_type",
        "type",
        "language",
        "title",
        "title_transcript",
        "title_translation",
        "short_title",
        "has_no_author",
        "volume",
        "date",
        "edition_no",
        "free_date",
        "abstract",
        "republication_of",
        "online_url",
        "online_resources",
        "translation_of",
        "new_edition_of",
        "is_catalog",
        "in_bibliography",
        "is_inactive",
        "notes"
    ];


    public function persons()
    {
        return $this->hasMany('DareOne\models\bib\BibEntryPerson', "entry_id", "id")->with('norm_person', 'role');
    }

    public function norm_persons()
    {
        return $this->belongsToMany('DareOne\models\person\Person', "bib_entry_person", "entry_id", "person_id");
    }

    public function types()
    {
        return $this->hasOne('DareOne\models\bib\BibType', "id", "type");
    }

    public function categories()
    {
        return $this->belongsToMany('DareOne\models\bib\BibCategory', "bib_entry_category", "bib_entry_id", "category_id");
    }
        // WE REALLY HAVE TO CHECK WHAT THE RELATIONS IS IN BIB_ENTRY_WORKS

    public function roles()
    {
        return $this->belongsToMany('DareOne\models\bib\BibRole', "bib_entry_person", "entry_id", "role" );
    }

    public function book()
    {
            return $this->hasOne('DareOne\models\bib\BibBook', "id", "id");
    }

    public function booksection()
    {
        return $this->hasOne('DareOne\models\bib\BibBookSection', "id", "id")->with("book");
    }

    public function article()
    {
        return $this->hasOne('DareOne\models\bib\BibArticle', "id", "id");
    }


    public $timestamps = false;


    protected $table = "bib_entry";
    protected $primaryKey ="id";
}