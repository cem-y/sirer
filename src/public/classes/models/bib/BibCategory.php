<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;


class BibCategory extends BaseModel
{
    protected $fillable = [
        "id",
        "category_name"
    ];

    public function bibliography()
    {
        return $this->belongsToMany('DareOne\models\bib\BibEntry', 'bib_entry_category', 'category_id', 'bib_entry_id')->orderBy("date", "desc");
    }

    public $timestamps = false;
    protected $table = "bib_category";
    protected $primaryKey ="id";

}