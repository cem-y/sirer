<?php
namespace DareOne\system;
use DareOne\models\DbLog;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class DareLogger
{
    public static function defaultLogger(){
        $logger = new Logger('LOG');
        $logger->pushHandler(new StreamHandler('logs/default/debug_dare.log', logger::DEBUG));
        $logger->pushHandler(new StreamHandler('logs/default/error_dare.log', logger::ERROR));
        $logger->pushHandler(new StreamHandler('logs/default/dare.log', logger::INFO));
        $logger->pushHandler(new FirePHPHandler());
        return $logger;
    }

    public static function DBLogger(){
        $logger = new Logger('DB-LOG');
        $logger->pushHandler(new StreamHandler('logs/db/debug_db.log', logger::DEBUG));
        $logger->pushHandler(new StreamHandler('logs/db/error_db.log', logger::ERROR));
        $logger->pushHandler(new StreamHandler('logs/db/db.log', logger::INFO));
        $logger->pushHandler(new FirePHPHandler());
        return $logger;
    }


    public static function logDebug($msg){
        $user="USER[".$_SESSION["userid"]."]";
        $logger=DareLogger::defaultLogger();
        $logger->debug($msg);
    }

    public static function logDbUpdate($request, $table, $modelClass){
        $user="USER[".$_SESSION["userid"]."]";
        $logger=DareLogger::defaultLogger();
        $dbLogger=DareLogger::DBLogger();
        $logger->info("UPDATE ENTRY: ".$table."[".$request->getAttributes()["id"]."] | USER: ".$user." | DETAILS: /logs/db/db.log");
        $dbLogger->info("UPDATE ENTRY: ".$table."[".$request->getAttributes()["id"]."] | USER: ".$user);
        $oldValues=$modelClass::where("id", "=", $request->getAttributes()["id"])
            ->first()
            ->toArray();
        $newValues=self::unsetRequestParams($request->getParsedBody());
        foreach($newValues as $key => $value){
            if($oldValues[$key]!=$value){
                $dbLogger->info(print_r("TABLE: ".$table."| COLUMN: ".$key." | OLD_VALUE:".$oldValues[$key]." | NEW_VALUE:".$value, true));
                $dbLog=new DbLog();
                $dbLog->db_table=$table;
                $dbLog->entry_id=$request->getAttributes()["id"];
                $dbLog->column=$key;
                $dbLog->old_value=$oldValues[$key];
                $dbLog->new_value=$value;
                $dbLog->user_id=$_SESSION["userid"];
                $dbLog->mode="update";
                $dbLog->save();
            }
        }

    }

    public static function logDbCreate($request, $table, $modelClass, $id){
        $user="USER[".$_SESSION["userid"]."]";
        $logger=DareLogger::defaultLogger();
        $dbLogger=DareLogger::DBLogger();
        $logger->info("CREATE ENTRY: ".$table."[".$id."] | USER: ".$user." | DETAILS: /logs/db/db.log");
        $dbLogger->info("CREATE ENTRY: ".$table."[".$id."] | USER: ".$user);
        $oldValues=$modelClass::where("id", "=", $id)
            ->first()
            ->toArray();
        foreach($oldValues as $key => $value){
            $dbLogger->info(print_r("TABLE: ".$table."| COLUMN: ".$key." | OLD_VALUE: - | NEW_VALUE:".$value, true));
            $dbLog=new DbLog();
            $dbLog->db_table=$table;
            $dbLog->entry_id=$id;
            $dbLog->column=$key;
            $dbLog->new_value=$value;
            $dbLog->user_id=$_SESSION["userid"];
            $dbLog->mode="create";
            $dbLog->save();
        }
    }

    public static function logDbDelete($request, $table, $modelClass, $id){
        $user="USER[".$_SESSION["userid"]."]";
        $logger=DareLogger::defaultLogger();
        $dbLogger=DareLogger::DBLogger();
        $logger->info("DELETE ENTRY: ".$table."[".$id."] | USER: ".$user." | DETAILS: /logs/db/db.log");
        $dbLogger->info("DELETE ENTRY: ".$table."[".$id."] | USER: ".$user);
        $oldValues=$modelClass::where("id", "=", $id)
            ->first()
            ->toArray();
        foreach($oldValues as $key => $value){
            $dbLogger->info(print_r("TABLE: ".$table."| COLUMN: ".$key." | OLD_VALUE: ".$value." | NEW_VALUE: -", true));

            $dbLog=new DbLog();
            $dbLog->db_table=$table;
            $dbLog->entry_id=$id;
            $dbLog->column=$key;
            $dbLog->old_value=$value;
            $dbLog->user_id=$_SESSION["userid"];
            $dbLog->mode="delete";
            $dbLog->save();
        }
    }


    private static function unsetRequestParams($newValues){
        unset($newValues["_METHOD"]);
        unset($newValues["_method"]);
        unset($newValues["ic-request"]);
        unset($newValues["ic-element-id"]);
        unset($newValues["ic-id"]);
        unset($newValues["ic-target-id"]);
        unset($newValues["ic-trigger-id"]);
        unset($newValues["ic-current-url"]);
        unset($newValues["index"]);
        return $newValues;
    }



}