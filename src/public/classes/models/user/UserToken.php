<?php


namespace DareOne\models\user;
use DareOne\models\BaseModel;

class UserToken extends BaseModel
{
    protected $guarded = [
    ];


    protected $table = "ap_tokens";
    protected $primaryKey ="id";
    public $timestamps = false;







}