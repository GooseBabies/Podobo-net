<?php
	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["name"])) { $name = html_entity_decode($_GET["name"]); } else { $name = ""; };
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 
	if(isset($_GET["time"])) { $time = $_GET["time"]; } else { $time = 0; };
    if(isset($_GET["hk"])) { $hk = $_GET["hk"]; } else { $hk = 0; }; 
	
	try{
		if($name != "" and $id != -1)
		{
            if(!CheckDuplicateEvents($name, $id)){
                $db->exec('BEGIN;');
                $db->enableExceptions(true);

                switch($hk){
                    case 0:
                        $hk1 = 0;
                        $hk2 = 0;
                        break;
                    case 1:
                        if(CheckDuplicatehk1($id)){
                            $hk1 = 0;
                        }
                        else{
                            $hk1 = 1;
                        }                        
                        $hk2 = 0;
                        break;
                    case 2:
                        $hk1 = 0;
                        if(CheckDuplicatehk2($id)){
                            $hk2 = 0;
                        }
                        else{
                            $hk2 = 1;
                        } 
                        break;
                    default:
                        $hk1 = 0;
                        $hk2 = 0;
                        break;
                }

                $sql = $db->prepare("INSERT INTO events (media_id, name, time, hotkey1, hotkey2) VALUES (:media_id, :name, :time, :hotkey1, :hotkey2);");
                $sql->bindValue(':media_id', $id, SQLITE3_INTEGER);
                $sql->bindValue(':name', $name, SQLITE3_TEXT);
                $sql->bindValue(':time', $time, SQLITE3_INTEGER);
                $sql->bindValue(':hotkey1', $hk1, SQLITE3_INTEGER);
                $sql->bindValue(':hotkey2', $hk2, SQLITE3_INTEGER);
                $result = $sql->execute();
                
                $db->exec('COMMIT;');
                echo json_encode($name);
            }
            else{
                echo json_encode("Event name already exists");
            }
			
		}
	}catch(exception $e){
		$output = $db->lastErrorMsg();
		$db->exec('ROLLBACK;');
	}
	
	$db = null;
	
	function CheckDuplicateEvents($name, $id)
	{
		try{
			global $db;
			$sql = $db->prepare("SELECT COUNT(*) FROM events WHERE name = :name AND media_id = :media_id");
			$sql->bindValue(':media_id', $id, SQLITE3_INTEGER);
			$sql->bindValue(':name', $name, SQLITE3_TEXT);
			$eventcount = $sql->execute()->fetchArray()[0] ?? 1;
			if($eventcount > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex){
			
		}
	}

    function CheckDuplicatehk1($id)
	{
		try{
			global $db;
			$sql = $db->prepare("SELECT COUNT(*) FROM events WHERE hotkey1 = 1 AND media_id = :media_id");
			$sql->bindValue(':media_id', $id, SQLITE3_INTEGER);
			$hkcount = $sql->execute()->fetchArray()[0] ?? 1;
			if($hkcount > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex){
			
		}
	}

    function CheckDuplicatehk2($id)
	{
		try{
			global $db;
			$sql = $db->prepare("SELECT COUNT(*) FROM events WHERE hotkey2 = 1 AND media_id = :media_id");
			$sql->bindValue(':media_id', $id, SQLITE3_INTEGER);
			$hkcount = $sql->execute()->fetchArray()[0] ?? 1;
			if($hkcount > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex){
			
		}
	}
?>