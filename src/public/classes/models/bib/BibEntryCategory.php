<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;


class BibEntryCategory extends BaseModel
{
    protected $fillable = [
        "id",
        "bib_entry_id",
        "category_id"
    ];

    public function categories()
    {
        return $this->belongsTo("DareOne\models\bib\BibCategory",category_id, id);
    }

    public $timestamps = false;
    protected $table = "bib_entry_category";
    protected $primaryKey ="id";
}