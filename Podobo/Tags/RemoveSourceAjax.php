<?php
	//$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
    //$db = new SQLite3("Y:\\Database\\nevada.db");
    $db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; };
    if(isset($_GET["source"])) { $source = html_entity_decode($_GET["source"]); } else { $source = ''; };
	$output = "";
	
    try{
        if($id != -1 and $source != '') 
        {
            $db->exec('BEGIN;');
            $db->enableExceptions(true);
        
            $sources = GetSourceList($id);
            $sources = str_replace($source, "", $sources);
            UpdateSourceList($id, $sources);

            $db->exec('COMMIT;');
            echo json_encode($source);
        }
        else
        {
            echo json_encode("Error");
        }
    }catch(Exception $e){
        $output = $db->lastErrorMsg();
        $db->exec('ROLLBACK;');
    }
    
	$db = null;

    function GetSourceList($id)
	{
        try{
            global $db;
            $sql = $db->prepare("SELECT sources FROM files where ID=:id");
            $sql->bindValue(':id', $id, SQLITE3_INTEGER);
            $sources = $sql->execute()->fetchArray()[0] ?? '';
            return $sources;
        }catch(exception $e){

        }
	}
	
	function UpdateSourceList($id, $sourcelist)
	{
        try{
            global $db;
            $sql = $db->prepare("update files set sources = :sourcelist where id = :id");
            $sql->bindValue(':sourcelist', $sourcelist, SQLITE3_TEXT);
            $sql->bindValue(':id', $id, SQLITE3_INTEGER);
            $result = $sql->execute();
        }catch(exception $e){
            
        }
	}
?>