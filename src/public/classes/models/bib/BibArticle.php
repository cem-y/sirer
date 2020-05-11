<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;


class BibArticle extends BaseModel
{
    protected $fillable = [
        "id",
        "journal_id",
        "journal_name",
        "volume",
        "issue",
        "pages"
    ];


    public $timestamps = false;
    protected $table = "bib_article";
    protected $primaryKey ="id";
}