<?php
// Start the session
session_start();
$thumbs_source = "thumbs/";
$columncount = 12;
$rowcount = 6;
$itemcount = $columncount * $rowcount;
$TagColors=array("#FFFFFF", "#6495ED", "#FF4500", "#FF4500", "#FF8C00", "#7FFFD4", "#BA55D3", "#228B22", "#8A2BE2", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF", "#DAA520", "#8FBC8F", "#FFFFFF");
$files = [];
$file_page_data = [];
$tags = [];
$tag_counts = [];

$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");

if(isset($_GET["search"])) { $search = html_entity_decode($_GET["search"]); } else { $search = ""; }
if(isset($_GET["page"])) { $page = $_GET["page"]; } else { $page = 1; }
if(isset($_SESSION["all_ids"]) and $search == "")
{
	$sql = $db->prepare("SELECT name, overall_rating, video, sound FROM files order by ID desc limit :limit offset :offset");
	$sql->bindValue(':limit', $itemcount, SQLITE3_INTEGER);
	$sql->bindValue(':offset', $itemcount * ($page - 1), SQLITE3_INTEGER);
	//echo "<!--" . $sql->getSQL() . "-->";
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
		array_push($file_page_data, $row);
	}

	$_SESSION["search"] = $search;
	$files = $_SESSION["all_ids"];
	unset($_SESSION["filtered_ids"]);
	$filtered = false;
}
else
{
	if($search == "")
	{
		$sql = $db->prepare("SELECT ID FROM files order by ID desc");
		$result = $sql->execute();
		while ($row = $result->fetchArray()) {
			array_push($files, $row[0]);
		}

		$sql = $db->prepare("SELECT name, overall_rating, video, sound FROM files order by ID desc limit :limit offset :offset");
		$sql->bindValue(':limit', $itemcount, SQLITE3_INTEGER);
		$sql->bindValue(':offset', $itemcount * ($page - 1), SQLITE3_INTEGER);
		echo "<!--" . $sql->getSQL() . "-->";
		$result = $sql->execute();
		while ($row = $result->fetchArray()) {
			array_push($file_page_data, $row);
		}
		
		$_SESSION["search"] = $search;
		$_SESSION["all_ids"] = $files; //only store session data for full sql call
		unset($_SESSION["filtered_ids"]);
		$filtered = false;
	}
	else
	{			
		$orarray = [];
		$orarray_final = [];
		$andarray = [];
		$andarray_final = [];
		unset($_SESSION["filtered_ids"]);
		$_SESSION["search"] = $search;
		// echo $search;
		$searchtags = array_filter(explode(" ", $search));
		//$sql_preposition = "";
		for($tag_index = 0; $tag_index<count($searchtags); $tag_index++){
			$searchtags[$tag_index] = str_replace("_", " ", $searchtags[$tag_index]);
			if(str_contains($searchtags[$tag_index], "||")){
				$ortags = array_filter(explode("||", $searchtags[$tag_index]));
				
				for($or_index = 0; $or_index<count($ortags); $or_index++){
					if(str_starts_with($ortags[$or_index], "!")){	
						$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
						$sqla->bindValue(':tag', substr($ortags[$or_index], 1), SQLITE3_TEXT);
						$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	

						$sql = $db->prepare("select distinct media_id from media_tags where media_id not in (select media_id from media_tags where tag_id = :tagid)");
						$sql->bindValue(':tagid', $searchtagid, SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$ext:')){
						$sql = $db->prepare("SELECT id from files where ext = :ext");
						$sql->bindValue(':ext', substr($ortags[$or_index],5), SQLITE3_TEXT);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$dur>')){
						$sql = $db->prepare("SELECT id from files where duration > :dur");
						$sql->bindValue(':dur', substr($ortags[$or_index],5), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}						
					else if(str_starts_with($ortags[$or_index], '$dur<')){
						$sql = $db->prepare("SELECT id from files where duration < :dur");
						$sql->bindValue(':dur', substr($ortags[$or_index],5), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$name:')){
						$sql = $db->prepare("SELECT id from files where name like :name");
						$sql->bindValue(':name', "%" . str_replace('\'', '_', substr($ortags[$or_index],6)) . "%", SQLITE3_TEXT);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$!name:')){
						$sql = $db->prepare("SELECT id from files where name not like :name");
						$sql->bindValue(':name', "%" . str_replace('\'', '_', substr($ortags[$or_index],6)) . "%", SQLITE3_TEXT);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$start:')){

						$sql = $db->prepare("SELECT id from files where name like :name");
						$sql->bindValue(':name', str_replace('\'', '_', substr($ortags[$or_index],7)) . "%", SQLITE3_TEXT);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$rating>')){
						$sql = $db->prepare("SELECT id from files where overall_rating > :rating");
						$sql->bindValue(':rating', substr($ortags[$or_index],8), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}						
					else if(str_starts_with($ortags[$or_index], '$rating<')){
						$sql = $db->prepare("SELECT id from files where overall_rating < :rating");
						$sql->bindValue(':rating', substr($ortags[$or_index],8), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$height>')){
						$sql = $db->prepare("SELECT id from files where height > :height");
						$sql->bindValue(':height', substr($ortags[$or_index],8), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}						
					else if(str_starts_with($ortags[$or_index], '$height<')){
						$sql = $db->prepare("SELECT id from files where height < :height");
						$sql->bindValue(':height', substr($ortags[$or_index],8), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$sound')){
						$sql = $db->prepare("SELECT id from files where sound = 1");
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$video')){
						$sql = $db->prepare("SELECT id from files where video = 1");
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$rev')){
						$sql = $db->prepare("SELECT id from files where review = 1 and hash not in (select hash from booru_proc where ignored = 0 and processed = 0)");
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$tags=0') or str_starts_with($ortags[$or_index], '$!tagged')){
						$sql = $db->prepare("select * from files left join media_tags on id = media_id where media_id is null");
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$tags=')){
						$sql = $db->prepare("select media_id from (select media_id, count(tag_id) as tag_count from media_tags group by media_id) where tag_count = :tag_count");
						$sql->bindValue(':tag_count', substr($ortags[$or_index],6), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$tags>')){
						$sql = $db->prepare("select media_id from (select media_id, count(tag_id) as tag_count from media_tags group by media_id) where tag_count > :tag_count");
						$sql->bindValue(':tag_count', substr($ortags[$or_index],6), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$tags<')){
						$sql = $db->prepare("select media_id from (select media_id, count(tag_id) as tag_count from media_tags group by media_id) where tag_count < :tag_count");
						$sql->bindValue(':tag_count', substr($ortags[$or_index],6), SQLITE3_INTEGER);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$source:')){
						$sql = $db->prepare("select * from files where sources like :source");
						$sql->bindValue(':source', "%" . str_replace('\'', '_', substr($ortags[$or_index],8)) . "%", SQLITE3_TEXT);
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else if(str_starts_with($ortags[$or_index], '$!source')){
						$sql = $db->prepare("select * from files where sources = '' or sources is null");
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}	
					else if(str_starts_with($ortags[$or_index], '$set:')){
						$sql = $db->prepare("select set_list from sets where id = :id");
						$sql->bindValue(':id', substr($searchtags[$tag_index],5), SQLITE3_INTEGER);
						$liststring = $sql->execute()->fetchArray()[0];
						$orarray = explode(",", $liststring);

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}	
					else if(str_starts_with($ortags[$or_index], '$event')){
						$sql = $db->prepare("select distinct media_id from events");
						$result = $sql->execute();
						while ($row = $result->fetchArray()) {
							array_push($orarray, $row[0]);
						}

						if(!empty($orarray_final)){
							$orarray_final = array_unique(array_merge($orarray_final, $orarray));
						}
						else{
							$orarray_final = $orarray;
						}
						$orarray = [];
					}
					else{
						if(str_starts_with($ortags[$or_index], '~')){
							$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
							$sqla->bindValue(':tag', substr($ortags[$or_index], 1), SQLITE3_TEXT);
							$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	

							$sql = $db->prepare("SELECT id from files where name like :name");
							$sql->bindValue(':name', "%" . str_replace('\'', '_', substr($ortags[$or_index], 1)) . "%", SQLITE3_TEXT);
							$result = $sql->execute();
							while ($row = $result->fetchArray()) {
								array_push($orarray, $row[0]);
							}

							if(!empty($orarray_final)){
								$orarray_final = array_unique(array_merge($orarray_final, $orarray));
							}
							else{
								$orarray_final = $orarray;
							}
							$orarray = [];

							$sql = $db->prepare("SELECT media_id from media_tags where tag_id = :tagid");
							$sql->bindValue(':tagid', $searchtagid, SQLITE3_INTEGER);
							$result = $sql->execute();
							while ($row = $result->fetchArray()) {
								array_push($orarray, $row[0]);
							}

							if(!empty($orarray_final)){
								$orarray_final = array_unique(array_merge($orarray_final, $orarray));
							}
							else{
								$orarray_final = $orarray;
							}
							$orarray = [];


						}
						else{
							$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
							$sqla->bindValue(':tag', $ortags[$or_index], SQLITE3_TEXT);
							$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	

							$sql = $db->prepare("SELECT media_id from media_tags where tag_id = :tagid");
							$sql->bindValue(':tagid', $searchtagid, SQLITE3_INTEGER);
							$result = $sql->execute();
							while ($row = $result->fetchArray()) {
								array_push($orarray, $row[0]);
							}

							if(!empty($orarray_final)){
								$orarray_final = array_unique(array_merge($orarray_final, $orarray));
							}
							else{
								$orarray_final = $orarray;
							}
							$orarray = [];

							//PRINT_R($orarray_final);
						}
					}
					//echo "<!--" . count($orarray_final) . "-->";						
				}				
			}
			else if(str_starts_with($searchtags[$tag_index], "!")){		
				$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
				$sqla->bindValue(':tag', substr($searchtags[$tag_index], 1), SQLITE3_TEXT);
				$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;	

				$sql = $db->prepare("select distinct media_id from media_tags where media_id not in (select media_id from media_tags where tag_id = :tagid)");
				$sql->bindValue(':tagid', $searchtagid, SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$ext:')){
				$sql = $db->prepare("SELECT id from files where ext = :ext");
				$sql->bindValue(':ext', substr($searchtags[$tag_index], 5), SQLITE3_TEXT);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$dur>')){
				$sql = $db->prepare("SELECT id from files where duration > :dur");
				$sql->bindValue(':dur', substr($searchtags[$tag_index], 5), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}						
			else if(str_starts_with($searchtags[$tag_index], '$dur<')){
				$sql = $db->prepare("SELECT id from files where duration < :dur");
				$sql->bindValue(':dur', substr($searchtags[$tag_index], 5), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$name:')){
				$sql = $db->prepare("SELECT id from files where name like :name");
				$sql->bindValue(':name', "%" . str_replace('\'', '_', substr($searchtags[$tag_index], 6)) . "%", SQLITE3_TEXT);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$!name:')){
				$sql = $db->prepare("SELECT id from files where name not like :name");
				$sql->bindValue(':name', "%" . str_replace('\'', '_', substr($searchtags[$tag_index], 7)) . "%", SQLITE3_TEXT);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$start:')){
				$sql = $db->prepare("SELECT id from files where name like :name");
				$sql->bindValue(':name', str_replace('\'', '_', substr($searchtags[$tag_index], 7)) . "%", SQLITE3_TEXT);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$rating>')){
				$sql = $db->prepare("SELECT id from files where overall_rating > :rating");
				$sql->bindValue(':rating', substr($searchtags[$tag_index], 8), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}						
			else if(str_starts_with($searchtags[$tag_index], '$rating<')){
				$sql = $db->prepare("SELECT id from files where overall_rating < :rating");
				$sql->bindValue(':rating', substr($searchtags[$tag_index], 8), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$height>')){
				$sql = $db->prepare("SELECT id from files where height > :height");
				$sql->bindValue(':height', substr($searchtags[$tag_index], 8), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}						
			else if(str_starts_with($searchtags[$tag_index], '$height<')){
				$sql = $db->prepare("SELECT id from files where height < :height");
				$sql->bindValue(':height', substr($searchtags[$tag_index], 8), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$sound')){
				$sql = $db->prepare("SELECT id from files where sound = 1");
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$video')){
				$sql = $db->prepare("SELECT id from files where video = 1");
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$rev')){
				$sql = $db->prepare("SELECT id from files where review = 1 and hash not in (select hash from booru_proc where ignored = 0 and processed = 0)");
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags=0') or str_starts_with($searchtags[$tag_index], '$!tagged')){
				$sql = $db->prepare("select id from files where id not in (select media_id from media_tags)");
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags=')){
				$sql = $db->prepare("select media_id from (select media_id, count(tag_id) as tag_count from media_tags group by media_id) where tag_count = :tag_count");
				$sql->bindValue(':tag_count', substr($searchtags[$tag_index], 6), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags>')){
				$sql = $db->prepare("select media_id from (select media_id, count(tag_id) as tag_count from media_tags group by media_id) where tag_count > :tag_count");
				$sql->bindValue(':tag_count', substr($searchtags[$tag_index], 6), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$tags<')){
				$sql = $db->prepare("select media_id from (select media_id, count(tag_id) as tag_count from media_tags group by media_id) where tag_count < :tag_count");
				$sql->bindValue(':tag_count', substr($searchtags[$tag_index], 6), SQLITE3_INTEGER);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$set:')){
				$sql = $db->prepare("select set_list from sets where id = :id");
				$sql->bindValue(':id', substr($searchtags[$tag_index], 5), SQLITE3_INTEGER);
				$liststring = $sql->execute()->fetchArray()[0];
				$andarray = explode(",", $liststring);

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$source:')){
				$sql = $db->prepare("select id from files where sources like :source");
				$sql->bindValue(':source', "%" . str_replace('\'', '_', substr($searchtags[$tag_index], 8)) . "%", SQLITE3_TEXT);
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else if(str_starts_with($searchtags[$tag_index], '$!source')){
				$sql = $db->prepare("select id from files where sources = '' or sources is null");
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}			
			else if(str_starts_with($searchtags[$tag_index], '$event')){
				$sql = $db->prepare("select distinct media_id from events");
				$result = $sql->execute();
				while ($row = $result->fetchArray()) {
					array_push($andarray, $row[0]);
				}

				if(!empty($andarray_final)){
					$andarray_final = array_intersect($andarray_final, $andarray);
				}
				else{
					$andarray_final = $andarray;
				}
				$andarray = [];
			}
			else{
				if(str_starts_with($searchtags[$tag_index], '~')){
					$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
					$sqla->bindValue(':tag', substr($searchtags[$tag_index], 1), SQLITE3_TEXT);
					$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;

					$andarray1 = [];
					$andarray2 = [];

					$sql = $db->prepare("SELECT id from files where name like :name");
					$sql->bindValue(':name', "%" . str_replace('\'', '_', substr($searchtags[$tag_index], 1)) . "%", SQLITE3_TEXT);
					$result = $sql->execute();
					while ($row = $result->fetchArray()) {
						array_push($andarray1, $row[0]);
					}

					$sql = $db->prepare("SELECT media_id from media_tags where tag_id = :tagid");
					$sql->bindValue(':tagid', $searchtagid, SQLITE3_INTEGER);
					$result = $sql->execute();
					while ($row = $result->fetchArray()) {
						array_push($andarray2, $row[0]);
					}

					if(empty($andarray1)){
						$andarray = $andarray2;
					}
					else if (empty($andarray2)){
						$andarray = $andarray1;
					}
					else{
						$andarray = array_unique(array_merge($andarray1, $andarray2));
					}
	
					if(!empty($andarray_final)){
						$andarray_final = array_intersect($andarray_final, $andarray);
					}
					else{
						$andarray_final = $andarray;
					}
					$andarray = [];
				}
				else{
					$sqla = $db->prepare("select tagid from tags where tag_name=:tag COLLATE NOCASE");
					$sqla->bindValue(':tag', $searchtags[$tag_index], SQLITE3_TEXT);
					$searchtagid = $sqla->execute()->fetchArray()[0] ?? -1;

					$sql = $db->prepare("SELECT media_id from media_tags where tag_id = :tagid");
					$sql->bindValue(':tagid', $searchtagid, SQLITE3_INTEGER);
					echo "<!--" . $sql->getSQL() . "-->";
					echo "<!--" . $searchtagid . "-->";
					$result = $sql->execute();
					while ($row = $result->fetchArray()) {
						array_push($andarray, $row[0]);
					}
					
					//print_r($andarray);

					if(!empty($andarray_final)){
						$andarray_final = array_intersect($andarray_final, $andarray);
					}
					else{
						$andarray_final = $andarray;
					}
					$andarray = [];

					//$andarray_final = array_intersect($andarray_final, $andarray);
				}
			}		
			//echo "<!--" . count($andarray_final) . "-->";					
		}	

		//print_r($orarray_final);

		if(!empty($orarray_final)){
			if(!empty($andarray_final)){
				$andarray_final = array_intersect($andarray_final, $orarray_final);
			}
			$andarray_final = $orarray_final;
		}

		rsort($andarray_final); //reverse sort ID descending
		$files = $andarray_final;

		//echo "<!--" . ($itemcount * ($page - 1)) . "-->";
		for($i = ($itemcount * ($page - 1)); $i <= (($page * $itemcount) - 1) and $i <= count($files) - 1; $i++){
			
			//echo "<!--" . $files[$i] . "-->";
			$sql = $db->prepare("select name, overall_rating, video, sound from files where id = :id");
			$sql->bindValue(':id', $files[$i], SQLITE3_INTEGER);
			$result = $sql->execute();
			while ($row = $result->fetchArray()) {
				array_push($file_page_data, $row);
				//echo "<!--" . print_r($row) . "-->";
			}
		}
		
		$_SESSION["filtered_ids"] = $files;
		$filtered = true;
	}
}

$rownum = count($files);

$r = rand(0, $rownum);

for($i = ($itemcount * ($page - 1)); $i <= (($page * $itemcount) - 1) and $i <= count($files) - 1; $i++){
			
	//echo "<!--" . $files[$i] . "-->";
	$sql = $db->prepare("select count(*) from media_tags where media_id = :id");
	$sql->bindValue(':id', $files[$i], SQLITE3_INTEGER);
	$result = $sql->execute();
	while ($row = $result->fetchArray()) {
		array_push($tag_counts, $row);
		
	}
}

$PageTitle = "Podobo - Posts [" . countFormat($rownum) . "]";
$list_view = true;
	
function customPageHeader(){?>
	<script>		
		function ListView() {
			var elements = document.getElementsByClassName("posts");
			for (i = 0; i < elements.length; i++) {
				elements[i].style.width = "100%";
				elements[i].style.height = "151px";
				elements[i].style.margin = "20px 0px 14px 0px";
				elements[i].classList.add("w3-center");
			}

			var nametext = document.getElementsByClassName("nametext");
			while(nametext.length){
				nametext[0].className = "nametext-list";
			}

			var vidandsoundmarker = document.getElementsByClassName(" vidandsoundmarker");
			while( vidandsoundmarker.length){
				vidandsoundmarker[0].className = "vidandsoundmarker-list";
			}

			var vidmarker = document.getElementsByClassName(" vidmarker");
			while( vidmarker.length){
				vidmarker[0].className = "vidmarker-list";
			}

			var nowrap = document.getElementsByClassName("nowrap");
			while(nowrap.length) {
				nowrap[0].classList.remove("nowrap");
			}

			var postid = document.getElementsByClassName("postid");
			while(postid.length) {
				postid[0].className = "postid-list";
			}
		}

		$(document).ready(function()
		{				
			var HeaderButton = document.getElementById("posts");
			HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
		});
	</script>
	<style type="text/css">
		input[type=text] {
			width: 200px;
		}
	</style>
<?php }

include_once('header.php');
?>
					
			<main class="row">
			<div class="w3-theme main-left">
                        <div>
			<h4 class="w3-bar-item"><b>Tags</b></h4>
			
			<?php
			$sql2 = "select tag_name, category, tag_count from tags order by tag_count desc limit 40";
			$result2 = $db->query($sql2);
			while ($row = $result2->fetchArray()) {
				array_push($tags, $row);
			}
			
			$db = null;
			echo "<form class='tagadd' action='Posts.php' method='GET'>";
				echo "<input type='text' id='tag-input' oninput='TagSuggestions(this.value)' name='search' value='" . htmlspecialchars($search, ENT_QUOTES) . "'  data-multiple/>";
				echo "<input type='submit' hidden />";
			echo"</form>";
			
			echo "<hr />";
			echo "</div>";
			echo "<div>";
                        
			echo "<ul class='search-tag-list'>";
			foreach($tags as $tag)
			{
				echo "<li>";
				echo "<a href ='Posts.php?search=!" . $tag[0] = str_replace(" ", "_", $tag[0]) ."&page=" . $page . "'>! </a><a style='color:" . $TagColors[$tag[1]] . "' href ='Posts.php?search=" . $tag[0] = str_replace(" ", "_", $tag[0]) ."&page=" . $page . "'>" . $tag[0] . " <div class='tag-count'>" . countFormat($tag[2])  . "</div></a>";
				echo "</li>";
			}
			echo "</ul>";
			echo "</div>";
			echo "</div>";
			
			echo "<div class='col-10'><div class='posts-wrapper'>";			
			
			$maxpage = ceil($rownum/($columncount*$rowcount));
			$pagestart = ($page - 1) * ($columncount * $rowcount);
			
			//for($i = $pagestart; $i<$pagestart+($rowcount*$columncount) and $i<$rownum; $i++)
			for($i = 0; $i<count($file_page_data); $i++)
			{
				//print_r($file_page_data);
				// if($i % $columncount == 0){
				// 	echo "<div class='row-posts'>";
				// }
				//echo "<!--" . $tag_counts[$i][0] .  "-->";
				if($tag_counts[$i][0] > 0) {$thumbclass = "class='thumbs tagged'";} else  {$thumbclass = "class='thumbs untagged'";} 
				echo "<article class='post-article'>";
				echo "<div class='post-preview'>";
				echo "<a href ='Post.php?id=" . $files[$pagestart + $i] . "'>";		
				
				echo "<img " . $thumbclass . " src ='" . $thumbs_source . pathinfo(rawurlencode($file_page_data[$i][0]), PATHINFO_FILENAME) . ".jpg" . "' onerror='this.src =\"" . $thumbs_source . "MissingThumb.jpg\"" . "' alt='N/A'/></a>"; //<a href='Post.php?id=" . $files[$i][0] . "'>" . $files[$i][0] . "</a>
				//echo "<div id='nametext' class='nametext'><p id='nowrap' class='nowrap'>" . $files[$i][1] . "</p></div>";
				if($file_page_data[$i][2] == 1 && $file_page_data[$i][3] == 1){
					echo "<div class='vidmarker'><i class='fas fa-volume-off fa-2x'></i></div>"; //<p>\u{25B6}\u{1F56A}</p>
				}
				else if($file_page_data[$i][2] == 1 && $file_page_data[$i][3] == 0){
					echo "<div class='vidmarker'><i class='fas fa-play fa-2x'></i></div>"; //\u{25B6}
				}
				else if($file_page_data[$i][2] == 0 && $file_page_data[$i][3] == 1){
					echo "<p class='vidmarker'>\u{1F56A}</p>";
				}
				echo "</div>";
				echo "</article>";

				// if($i % $columncount == ($columncount - 1) || $i == count($files) - 1){
				// 	echo "</div>";
				// }
			}
			
			echo "</div>";
			
			echo "<div class='container_footer'>";
			
			
			$pagelimit = 5;
			if($page <= $pagelimit) { $start_page = 1; } else { $start_page = $page - ($maxpage < $pagelimit ? $maxpage + 1 : $pagelimit);}
			if($page > $maxpage - $pagelimit) { $end_page = $maxpage; } else { $end_page = $page + ($maxpage < $pagelimit ? $maxpage : $pagelimit); }
			
			if($page != 1) 
			{
				echo "<a href='Posts.php?page=1&search=" . $search . "'><i class='fas fa-angles-left fa-2x'></i></a>";
				echo "<a href='Posts.php?page=".($page - 1)."&search=" . $search . "'><i class='fas fa-angle-left fa-2x'></i></a>";	
			}
			
			for($k = $start_page; $k <= $end_page; $k++)
			{
				if($k == $page){
					echo "<a class='current-page' href='Posts.php?page=".$k."&search=" . $search . "'>";
					echo $k;
					echo "</a>";
				}
				else{
					echo "<a class='other-page' href='Posts.php?page=".$k."&search=" . $search . "'>";
					echo $k;
					echo "</a>";
				}
				
			}
			
			if($page != $maxpage) 
			{
				echo "<a href='Posts.php?page=".($page + 1)."&search=" . $search . "'><i class='fas fa-angle-right fa-2x'></i></a>";
				echo "<a href='Posts.php?page=".$maxpage."&search=" . $search . "'><i class='fas fa-angles-right fa-2x'></i></a>";
			}
			
			echo "</div>";
			echo "</div>";
			echo "</main>";

			function countFormat($num) {

				if($num>1000) {
			  
					  $x = round($num);
					  $x_number_format = number_format($x);
					  $x_array = explode(',', $x_number_format);
					  $x_parts = array('k', 'm', 'b', 't');
					  $x_count_parts = count($x_array) - 1;
					  $x_display = $x;
					  $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
					  $x_display .= $x_parts[$x_count_parts - 1];
			  
					  return $x_display;
			  
				}
			  
				return $num;
			  }
			
		?>
		<script type="text/javascript">
			var awesomplete;
			var input;
			$(document).ready(function()
			{
				input = document.getElementById("tag-input");
				awesomplete = new Awesomplete(input, { sort: false, tabSelect: true , filter: function(text, input) {
						//console.log(input.match(/[^( |\|\||\~|\!)]*$/)[0]);
						return Awesomplete.FILTER_CONTAINS(text.value, input.match(/[^( |\|\||\~|\!)]*$/)[0]);
					},

					//Bug Here^? Filters out certain expected values

					item: function(text, input) {
						//console.log(input.match(/[^( |\|\||\~|\!)]*$/)[0]);
						return Awesomplete.ITEM(text, input.match(/[^( |\|\||\~|\!)]*$/)[0]);
					},

					replace: function(text) {
						var before = this.input.value.match(/^.+( |\|\||\!|\~)\s*|^(\~|\!)|/)[0];  //matches everything before and including the last space, ~, !, or ||
						//console.log(before);
						this.input.value = before + text.value;
				}  } );
			});			
			
			function TagSuggestions(data)
			{
				input.value = input.value.replace(/ $/g, "_");
				data = data.replace(/ $/g, "_");				
				
				input.value = input.value.replace(/__/g, " ");
				data = data.replace(/__/g, " ");
				
				//data = data.replace(/^.+ \s*|/g, "");
				data = data.replace(/^.+( |\|\|)\s*|/g, "");
				data = data.replace(/^(\!|\~)/g, "");
				
				//console.log(data);
				
				$.ajax({
					url: 'Tags/TagSuggestionsAjax.php?txt=' + data,
					type: 'get',
					dataType: 'JSON',
					success: function(response){	
						//console.log(response);					
						awesomplete.list = response;
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						console.log(xhr.responseText);
					}
				});
			}
		</script>
	</body>
</html>
