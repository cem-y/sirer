<?php


namespace DareOne\models\bib;
use DareOne\models\BaseModel;


class BibRole extends BaseModel
{
    protected $fillable = [
        "id",
        "role_name",
    ];


    protected $table = "bib_role";
    protected $primaryKey ="id";
}