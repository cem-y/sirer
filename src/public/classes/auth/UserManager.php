<?php

/*
 *  Copyright (C) 2019 Universität zu Köln
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace DareOne\auth;

use DareOne\models\user\User;
use Psr\Http\Message\ServerRequestInterface as Request;

/**

 */
class UserManager
{
    public static function getUserInfoById($id){
        $user=User::where("id", "=", $id)->first();
        $userInfo["id"]=$user->id;
        $userInfo["username"]=$user->username;
        $userInfo["firstname"]=$user->firstname;
        $userInfo["lastname"]=$user->lastname;
        $userInfo["role"]=$user->role;
        return $userInfo;
    }

    public static function updateUser(Request $request)
    {
        //DareLogger::logDbUpdate($request, "ap_users", "DareOne\models\bib\BibCategory");
        $params=$request->getParsedBody();
        $user=User::find($request->getAttributes()["id"]);
        $user->firstname=$params["firstname"];
        $user->lastname=$params["lastname"];
        $user->role=$params["role"];
        if ($params["password1"]!=null){
            $user->password=password_hash($params["password1"], PASSWORD_BCRYPT);
        }
        $user->save();
    }

    public static function createUser(Request $request){
        $params=$request->getParsedBody();
        $user=new User();
        $user->username=$params["username"];
        $user->firstname=$params["firstname"];
        $user->lastname=$params["lastname"];
        if ($params["password1"]!=null){
            $user->password=password_hash($params["password1"], PASSWORD_BCRYPT);
        }
        $user->save();
    }



}