<?php


namespace DareOne\models;


class Language extends BaseModel
{
    protected $fillable = [
        "id",
        "short_ident",
        "name"
    ];


    protected $table = "language";
    protected $primaryKey ="id";
    public $timestamps = false;




}