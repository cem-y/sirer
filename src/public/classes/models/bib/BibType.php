<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;


class BibType extends BaseModel
{
    protected $fillable = [
        "id",
        "bib_type"
    ];





    public $timestamps = false;


    protected $table = "bib_type";
    protected $primaryKey ="id";
}