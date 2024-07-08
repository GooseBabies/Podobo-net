<?php

	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->exec('PRAGMA foreign_keys = ON;');
	$db->busyTimeout(100);
	
	if(isset($_GET["id"])) { $id = $_GET["id"]; } else { $id = -1; }; 

    $output = 0;

    switch($id){
        case 0:
            $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=0");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 1:
            $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=1");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 2:
            $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=2");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 3:
            $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=6");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 4:
            $sql = $db->prepare("select count(id) from booru_proc where processed=0");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 5:
            $sql = $db->prepare("select count(distinct media_id) from media_tags");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 6:
            $sql = $db->prepare("select count(id) from files where sources is not null and sources != ''");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 7:
            $sql = $db->prepare("select count(id) from files where booru_tagged = 1 or hydrus_tagged = 1");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 8:
            $sql = $db->prepare("select count(id) from files where IQDB = 1");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 9:
            $sql = $db->prepare("select count(id) from files where ext in ('.jpg', '.jpeg', '.png')");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 10:
            $sql = $db->prepare("select count(id) from files where ext ='.gif'");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 11:
            $sql = $db->prepare("select count(id) from files where ext in ('.mp4', '.wmv', 'webm')");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
        case 12:
            $sql = $db->prepare("select count(id) from booru_proc where processed=0 and booru_source=3");
            $output = $sql->execute()->fetchArray()[0] ?? 0;
            break;
    }
	
	echo $output;
    
	$db = null;
?>