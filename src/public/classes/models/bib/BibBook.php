<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;

class BibBook extends BaseModel
{
    protected $fillable = [
        "id",
        "pubplace",
        "publisher",
        "series",
        "volume",
        "edition_no",
        "valid_from",
        "valid_until"
    ];


    public $timestamps = false;
    protected $table = "bib_book";
    protected $primaryKey ="id";
}