<?php


namespace DareOne\models\user;
use DareOne\models\BaseModel;

class User extends BaseModel
{
    protected $guarded = [
    ];


    protected $table = "ap_users";
    protected $primaryKey ="id";
    public $timestamps = false;


    public static function verifyUserPassword(string $userName, string $givenPassword)
    {
        $user=User::where("username", "=", $userName)->first();
        if ($user==null){
            return false;
        }
        if (!isset($user['password']) || $user['password'] === '') {
            return false;
        }
        return password_verify($givenPassword, $user['password']);
    }

    public static function getUserByUserName(string $userName){
        return User::where("username", "=", $userName)->first();
    }




}