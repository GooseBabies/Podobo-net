<?php
	session_start();
	//$TagOrder=array(14,4,0,1,2,8,6,7,9,10,12,13,11,16,5,15);
	//$TagCategoryTitle=array("General", "IP/Series", "Individual", "Artist", "Studio/Network", "Sex", "Afilliation/Group", "Race/Species/Ethnicity", "Body Part", "Clothing/Accessory", "Position", "Setting", "Action", "Meta", "Title", "Release Date");

	$db = new SQLite3("C:\\Users\\Chris\\AppData\\Roaming\\Paiz\\Database\\nevada.db");	

	$files = [];
	if(isset($_SESSION["filtered_data"]) && count($_SESSION["filtered_data"]) > 0){
		$files = $_SESSION["filtered_data"];
		$idcount = count($files)-1;
		$filtered = true;
		//echo "<!--true-->";
	}
	else{
		if(isset($_SESSION["image_data"])){
			$files = $_SESSION["image_data"];				
			$filtered = false;
			//echo "<!--false-->";
		}
		else{		
			$result = $db->query("SELECT ID, name, overall_rating, video, sound, tag_list FROM files order by id desc");				
			while ($row = $result->fetchArray()) {
				array_push($files, $row);
			}
			$_SESSION["image_data"] = $files;
			$filtered = false;
			//echo "<!--false";
			//echo count($_SESSION["image_data"]) . "-->";
		}
		$idcount = count($files)-1;
	}

	$r = rand(0, $idcount);
	
	$postp = $db->query("select * from booru_proc where processed = 0 order by random() limit 1")->fetchArray() ?? '';	
	$postpcount = $db->query("select distinct count(*) from booru_proc where processed = 0")->fetchArray()[0] ?? '';
	
	$sql = $db->prepare("select * from tags where tag_name = :tag COLLATE NOCASE");
	$sql->bindValue(":tag", str_replace("_", " ", $postp[2]), SQLITE3_TEXT);
	$tag_item = $sql->execute()->fetchArray() ?? '';
	if($tag_item != ''){
		$tag = str_replace(" ", "_", $tag_item[1]);
	}
	else{
		$tag = "";
	}
	

	//if tag is a alias get preferred
	if($tag != ""){
		$sql = $db->prepare("select preferred from siblings where alias = :alias");
		$sql->bindValue(":alias", $tag_item[0], SQLITE3_INTEGER);
		$preferred = $sql->execute()->fetchArray()[0] ?? -1;
		if($preferred > 0)
		{		
			$sql = $db->prepare("select tag_name from tags where tagid= :preferred ");
			$sql->bindValue(":preferred", $preferred, SQLITE3_INTEGER);
			$tag = $sql->execute()->fetchArray()[0] ?? '';
			$tag = str_replace(" ", "_", $tag);
		}
	}

	$PageTitle = "Podobo - " . $postp[2];

	function customPageHeader(){?>
		<script>
			$(document).ready(function()
			{				
				var HeaderButton = document.getElementById("tools");
				HeaderButton.className = "w3-bar-item w3-button w3-theme-l1";
			});
		</script>
		<style type="text/css">
		#siblings-indicator {
            color: gray;
			margin-left: 5px;
			margin-right: 5px;
		}
		</style>
	<?php }

	include_once('header.php');

	$db = null;
			echo "<!-- Tag: " . $tag . " -->";
			
			echo "<hr />";
			
			echo "<div class='w3-center'>";
			echo "<p>" . $postpcount . " Tags Left</p>";
				//echo "<form autocomplete='off'>\r\n";
					echo '<input type="text" name="query" value="' . $postp[2] . '" />';
					//echo "<input type='submit' hidden />\r\n";
				//echo "</form>\r\n";
				
				echo "<div class='w3-center'>";
					echo '<a href="' . GetBooruLink($postp[3]) . $postp[2] . '" target="_blank">' . $postp[2] . ' ' . GetBooruSource($postp[3]) . '</a>';
				echo "</div>";
				if($postp[3] != 0)
				{
					echo "<div class='w3-center'>";
						echo "<p> - </p>";
						echo "<a href='" . "https://danbooru.donmai.us/posts?tags=" . $postp[2] . "' target='_blank'>" . $postp[2] . " (Danbooru)</a>";
					echo "</div>";
				}
			echo "</div>";
			
			echo "<hr />";
			
			//podobo tag
			echo "<div>";
				echo "<div class='w3-center'>";
					echo "<label>Tag:</label>";
				echo "</div>";
				echo "<div class='w3-center'>";
					echo "<input type='text' id='tag-input' oninput='TagSuggestions(this.value, 0)' />";		
					//echo "<input type='submit' hidden />";
				echo "</div>";
				echo "<div class='w3-center'>";
					echo "<select name='categories' id='category'>";
						echo "<option value='0'>General</option>";
						echo "<option value='1'>IP</option>";
						echo "<option value='2'>Individual</option>";
						echo "<option value='4'>Artist</option>";
						echo "<option value='5'>Studio</option>";
						echo "<option value='6'>Sex</option>";
						echo "<option value='7'>Afilliation</option>";
						echo "<option value='8'>Race</option>";
						echo "<option value='9'>Body Part</option>";
						echo "<option value='10'>Clothing</option>";
						echo "<option value='11'>Position</option>";
						echo "<option value='12'>Setting</option>";
						echo "<option value='13'>Action</option>";
						echo "<option value='14'>Meta</option>";
						echo "<option value='15'>Title</option>";
						echo "<option value='16'>Date</option>";
					echo "</select>";
				echo "</div>";
			echo "</div>";
			
			echo "<hr />";
			
			//parent tag
			echo "<div id='parentdiv' class='w3-center'>\r\n";
				echo "<div>";
					echo "<label>Parent:</label>";
				echo "</div>";
				echo "<div>";
					echo "<input type='text' id='parent0' oninput='TagSuggestions(this.value, 1)' />";
				echo "</div>";
				echo "<div>";
					echo "<label>Parent:</label>";
				echo "</div>";
				echo "<div>";
					echo "<input type='text' id='parent1' oninput='TagSuggestions(this.value, 2)' />";
				echo "</div>";
				echo "<div>";
					echo "<label>Parent:</label>";
				echo "</div>";
				echo "<div>";
					echo "<input type='text' id='parent2' oninput='TagSuggestions(this.value, 3)' />";
				echo "</div>";
			echo "</div>\r\n";
			
			echo "<hr />";
			
			//sibling tag
			echo "<div id='siblingdiv' class='w3-center'>";
				echo "<div>";
					echo "<i id='siblings-indicator' class='fa-solid fa-circle'></i>";
					echo "<label>Alias Siblings:</label>";					
				echo "</div>";
				echo "<div>";
					echo "<textarea id='sibling' rows='6' cols='40' oninput='AddedSiblings()'></textarea>";					
				echo "</div>";
				//echo "<div></div>";
			echo "</div>";
			
			echo "<hr />";
			
			echo "<div class='w3-center'>";
				echo "<input class='w3-center' id='submitbutton' type='button' value='Submit' onclick='SubmitTags()'/>\r\n";
				echo "<input class='w3-center' type='button' value='Ignore' onclick='IgnoreTags()'/>";		
			echo "</div>";
			
			echo "<div id='response' class='w3-center'>";
			
			echo "</div>";
			
			function GetBooruLink($booru_source)
			{
				switch($booru_source)
				{
					case 0:
						return "https://danbooru.donmai.us/posts?tags=";
						break;
					case 1:
						return "https://e621.net/posts?tags=";
						break;
					case 2:
						return "https://rule34.xxx/index.php?page=post&s=list&tags=";
						break;
					case 3:
						return "https://gelbooru.com/index.php?page=post&s=list&tags=";
						break;
					case 4:
						return "https://realbooru.com/index.php?page=post&s=list&tags=";
						break;
					case 5:
						return "https://chan.sankakucomplex.com/wiki/show?title=";
						break;
					default:
						return "https://www.google.com/search?q=";
						break;
				}
			}
			
			function GetBooruSource($booru_source)
			{
				switch($booru_source)
				{
					case 0:
						return "(Danbooru)";
						break;
					case 1:
						return "(e621)";
						break;
					case 2:
						return "(rule34.xxx)";
						break;
					case 3:
						return "(gelbooru)";
						break;
					case 4:
						return "(realbooru)";
						break;
					case 5:
						return "(Sankaku Complex)";
						break;
					default:
						return "(google)";
						break;
				}
			}
			
		?>
	</body>
	<script type="text/javascript">
		var awesomplete;
		var awesomplete2;
		var awesomplete3;
		var awesomplete4;
		var input;
		var parent0;
		var parent1;
		var parent2;
		var sibling;
		var category;
		var cat;
		var awesompletearray = ["awesomplete", "awesomplete2", "awesomplete3", "awesomplete4"];
		var providedparents = false;
		var providedsiblings = false;
		var bt = <?php echo json_encode($postp[2], JSON_UNESCAPED_UNICODE); ?>;
		var bs = <?php echo $postp[3]; ?>;
		var submit;
		var resdiv;
		$(document).ready(function()
		{
			input = document.getElementById("tag-input");
			input.value = <?php echo json_encode($tag, JSON_UNESCAPED_UNICODE); ?>;
			<?php 
				if ($tag != ""){
					echo "GetParent(input.value);";
					echo "GetSibling(input.value);";
					echo "GetCategory(input.value);";
				}
			?>
			awesomplete = new Awesomplete(input, { sort: false } );
			input.focus();
			
			parent0 = document.getElementById("parent0");
			parent0.value = "";
			awesomplete2 = new Awesomplete(parent0, { sort: false } );
			
			parent1 = document.getElementById("parent1");
			parent1.value = "";
			awesomplete3 = new Awesomplete(parent1, { sort: false } );
			
			parent2 = document.getElementById("parent2");
			parent2.value = "";
			awesomplete4 = new Awesomplete(parent2, { sort: false } );
			
			sibling = document.getElementById("sibling");
			sibling.value = "";
			
			category = document.getElementById("category");
			category.value = 0;
			submit = document.getElementById("submitbutton");
			
			resdiv = document.getElementById("response");
			sib_ind = document.getElementById("siblings-indicator");
		
			input.addEventListener("awesomplete-select", (event) => {
				GetParent(event.text.value);
				GetSibling(event.text.value);
				GetCategory(event.text.value);
			});	
		});
		
		function TagSuggestions(data, id)
		{
			switch(id)
			{
				case 0:
					input.value = input.value.replace(/ $/g, "_");					
					break;
				case 1:
					parent0.value = parent0.value.replace(/ $/g, "_");
					break;
				case 2:
					parent1.value = parent1.value.replace(/ $/g, "_");
					break;
				case 3:
					parent2.value = parent2.value.replace(/ $/g, "_");
					break;
			}
			data = data.replace(/ $/g, "_");
			
			$.ajax({
				url: 'Tags/TagSuggestionsAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){					
					if(id==0)
					{
						awesomplete.list = response;
						//console.log(response);
					}
					else
					{
						providedparents = false;
						eval(awesompletearray[id] + '.list = response;');
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
		function GetParent(data)
		{
			$.ajax({
				url: 'Tags/ParentAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{							
						for (let i = 0; i < 3 && i < response.length; i++)
						{							
							eval("parent" + i + ".value = response[" + i + "];");							
						}
						providedparents = true;						
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
		function GetSibling(data)
		{
			$.ajax({
				url: 'Tags/AliasAjax.php?txt=' + data,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{	
						//console.log(response);
						for (let i = 0; i < response.length; i++)
						{
							if (i == response.length - 1)
							{
								sibling.value += response[i];
							}
							else
							{
								sibling.value += response[i] + "\n";
							}
										
						}
						providedsiblings = true;						
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
		
		function GetCategory(data)
		{
			$.ajax({
				url: 'Tags/CategoryAjax.php?txt=' + encodeURIComponent(data),
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					category.value = response;
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
		function AddedSiblings()
		{
			providedsiblings = false;

			sibling.value = sibling.value.replace(/ $/g, "_");

			$.ajax({
				url: 'Tags/CheckSiblingsAjax.php?siblings=' + sibling.value.split('\n').join('+'),
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					//console.log(response);
					if(response == 1){
						sib_ind.style.color = "yellow";
					}
					else{
						sib_ind.style.color = "gray";
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
				}
			});
		}
		
		function SubmitTags()
		{
			submit.value = "Submitted!"
			var tag = input.value;
			if(!providedparents)
			{
				var parents = "&parents=" + (parent0.value == "" ? "" : parent0.value) + (parent1.value == "" ? "" : "+" + parent1.value) + (parent2.value == "" ? "" : "+" + parent2.value);
			}
			else
			{
				var parents = "";
			}
			
			if(!providedsiblings)
			{
				if(sibling.value != "")
				{
					var siblings = "&siblings=" + sibling.value.split('\n').join('+');
				}
				else
				{
					var siblings = "";
				}
			}
			else
			{
				var siblings = "";
			}
			cat = category.value;
			//console.log(cat);
			
			$.ajax({
				url: 'Tags/BooruAddTagAjax.php?tag=' + encodeURIComponent(tag) + parents + siblings + '&bt=' + encodeURIComponent(bt) + '&bs=' + bs + '&cat=' + cat,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{	
						resdiv.innerHTML += "<hr />";
						for (let i = 0; i < response.length; i++)
						{
							if(i==0)
							{
								resdiv.innerHTML += "<div><a href='Tags/Tag.php?tagid=" + response[i] + "' target='_blank'>Tag Page</a><p>-</p></div>";	
							}
							else{
								resdiv.innerHTML += "<div><a href='Post.php?id=" + response[i] + "' target='_blank'>" + response[i] + "</a></div>";	
							}
																
						}					
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});
		}
		
		function IgnoreTags()
		{
			$.ajax({
				url: 'Tags/IgnoreBooruAjax.php?bt=' + encodeURIComponent(bt) + '&bs=' + bs,
				type: 'get',
				//dataType: 'JSON',
				success: function(response){
					//just a small updated post
					console.log(response);
					resdiv.innerHTML += "<hr />";
					resdiv.innerHTML += "<div><p>Tag Processing Ignored</p></div>";
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});
		}
		
	</script>
</html>