<?php
class DataAccess{

    private static function Connection(){
        try{
            $db = json_decode(file_get_contents(__DIR__ .'/database.json'));
            return new PDO('mysql:host='. $db->host .';dbname='. $db->name .';charset='. $db->charset, $db->user, $db->pass);
        }catch(Exception $e){
            return null;
        }
    }

    public function GetAll($table){
        try{
            $ret = array();
            $pdo = DataAccess::Connection();
            if($pdo != null){
                $sentence = $pdo->prepare('SELECT * FROM '.$table);
                $sentence->execute();
                while($dataRow = $sentence->fetchObject()){
                    array_push($ret, $dataRow);
                }
            }
        }catch(Exception $e){
            ///
        }finally{
            unset($pdo);
            return $ret;
        }
    }

    public static function AddOne($table, $obj){
        try{
            $ret = false;
            $pdo = DataAccess::Connection();
            if(isset($pdo)){
                $struc = $obj->GetStructure();
                $fieldsString = '(';
                $valuesString = '(';
                ///creates the parameter lists for the petition
                foreach($struc as $item){
                    $fieldsString .= $item['name'].',';
                    $valuesString .= ':'.$item['name'].',';
                }
                $fieldsString = substr($fieldsString,0,strlen($fieldsString)-1).')';
                $valuesString = substr($valuesString,0,strlen($valuesString)-1).')';
                $sentence = $pdo->prepare('INSERT INTO '.$table.$fieldsString.' VALUES'.$valuesString);
                ///asingns values to their respective parameter
                foreach($struc as $item){
                    $sentence->BindValue(':'.$item['name'], $item['value'], $item['type']);
                }
                $sentence->execute();
                $ret = true;
            }
        }catch(Exception $e){
            ///
        }finally{
            unset($pdo);
            return $ret;
        }
    }
    public static function GetWhereParam($table, $param){
        try{
            $obj = false;
            $pdo = DataAccess::Connection();
            if(isset($pdo) && isset($param)){
                $sentenceString = 'SELECT * FROM '. $table .' WHERE '. $param['name'] .'= ?';
                $sentence = $pdo->prepare($sentenceString);
                $sentence->bindValue(1, $param['value'], $param['type']);
                $sentence->execute();
                $obj = $sentence->FetchObject();
            }
        }catch(Exception $e){
            ///
        }finally{
            unset($pdo);
            return $obj;
        }
    }
    public static function GetIndex($table, $param){
        try{
            $index = -1;
            $pdo = DataAccess::Connection();
            if(isset($pdo) && isset($param)){
                $sentenceString = 'SELECT id FROM '. $table .' WHERE '. $param['name'] .'= ?';
                $sentence = $pdo->prepare($sentenceString);
                $sentence->bindValue(1, $param['value'], $param['type']);
                $sentence->execute();
                $index = $sentence->FetchObject()->id + 0;//el +0 es para castear a int
            }
        }catch(Exception $e){
            ///
        }finally{
            unset($pdo);
            return $index;
        }
    }
    public static function Update($table, $obj, $where){
        try{
            $ret = false;
            $pdo = DataAccess::Connection();
            if(isset($pdo)){
                $struc = $obj->GetStructure();
                $fieldsString = '';
                ///creates the parameter lists for the petition
                foreach($struc as $item){
                    $fieldsString .= $item['name'].'=:' . $item['name'] . ',';
                }
                $fieldsString = substr($fieldsString, 0, strlen($fieldsString)-1);
                $sentence = $pdo->prepare('UPDATE '.$table. ' SET ' . $fieldsString . ' WHERE '. $where['name'] .'=:'. $where['name']);
                ///asingns values to their respective parameter
                foreach($struc as $item){
                    $sentence->BindValue(':'.$item['name'], $item['value'], $item['type']);
                }
                $sentence->BindValue(':'.$where['name'], $where['value'], $where['type']);
                $sentence->execute();
                $ret = true;
            }
        }catch(Exception $e){
            ///
        }finally{
            unset($pdo);
            return $ret;
        }
    }

    public static function Delete($table, $param){
        try{
            $ok = false;
            $pdo = DataAccess::Connection();
            if(isset($pdo)){
                $sentenceString = 'DELETE FROM '. $table .' WHERE '. $param['name'] .'=:' .$param['name'];
                $sentence = $pdo->prepare($sentenceString);
                $sentence->bindValue(':'. $param['name'], $param['value'], $param['type']);
                $ok = $sentence->execute();
            }
        }catch(Exception $e){
            $ok = false;
        }finally{
            unset($pdo);
            return $ok;
        }
    }
}