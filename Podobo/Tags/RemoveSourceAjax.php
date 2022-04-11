<?php
	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; };
    if(isset($_GET["source"])) { $source = html_entity_decode($_GET["source"]); } else { $source = ''; };
	$output = "";
    //echo $source;
	
	if($id != -1 and $source != '') 
    {
        $sources = GetSourceList($id);
        //echo $sources;
        $sources = str_replace($source, "", $sources);
        //echo $sources;
        UpdateSourceList($id, $sources);
        echo json_encode($source);
    }
    else
    {
        echo json_encode("Error");
    }
    
	$db = null;

    function GetSourceList($id)
	{
		global $db;
		$sql = $db->prepare("SELECT sources FROM files where ID=:id");
        $sql->bindValue(':id', $id, SQLITE3_INTEGER);
        $sources = $sql->execute()->fetchArray()[0] ?? '';
        //$source_array = array_filter(explode(" ", $sources));
        return $sources;
	}
	
	function UpdateSourceList($id, $sourcelist)
	{
		global $db;
		$sql = $db->prepare("update files set sources = :sourcelist where id = :id");
		$sql->bindValue(':sourcelist', $sourcelist, SQLITE3_TEXT);
		$sql->bindValue(':id', $id, SQLITE3_INTEGER);
		$result = $sql->execute();
	}
?>