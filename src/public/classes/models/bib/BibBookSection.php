<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;

class BibBookSection extends BaseModel
{
    protected $fillable = [
        "id",
        "section_of",
        "pages",
        "is_catalog"
    ];

    public function book()
    {
        return $this->hasOne("DareOne\models\bib\BibEntry","id", "section_of")->with("book");
    }


    public $timestamps = false;
    protected $table = "bib_booksection";
    protected $primaryKey ="id";
}