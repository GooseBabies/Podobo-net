<?php
	session_start();

	$db = new SQLite3("D:\\Piaz\\Database\\nevada.db");
	$db->busyTimeout(100);
	$files = [];

	try{
		$files = [];
	if(isset($_SESSION["filtered_ids"]) && count($_SESSION["filtered_ids"]) > 0){
		$files = $_SESSION["filtered_ids"];
		$idcount = count($files)-1;
		$filtered = true;
	}
	else{
		if(isset($_SESSION["all_ids"])){
			$files = $_SESSION["all_ids"];				
			$filtered = false;
		}
		else{		
			$result = $db->query("SELECT ID FROM files order by id desc");				
			while ($row = $result->fetchArray()) {
				array_push($files, $row[0]);
			}			
			$_SESSION["all_ids"] = $files;
			$filtered = false;
		}
		$_SESSION["search"] = "";
		$idcount = count($files)-1;
	}
	
		$r = rand(0, $idcount);
		
		$postp = $db->query("select media_id, booru_tag, namespace, booru_source from booru_proc where processed = 0 order by booru_source asc limit 1")->fetchArray() ?? '';	
		$overallcount = $db->query("select count(*) from booru_proc where processed = 0")->fetchArray()[0] ?? '';

		if($postp == ""){
			$postp = array(0, "0", "0", "0");
		}

		//echo "<!--" . print_r($postp) . "-->";

		// $sql = $db->prepare("select id from files where hash = :hash");
		// $sql->bindValue(":hash", $postp[0], SQLITE3_TEXT);
		// $id = $sql->execute()->fetchArray()[0] ?? '';

		$booru_tag = $postp[1];
		$namespace = $postp[2];
		$booru_source = $postp[3];
		$id = $postp[0];

		$sql = $db->prepare("select count(*) from booru_proc where booru_tag = :bt and booru_source = :bs and namespace = :namespace");
		$sql->bindValue(":bt", $booru_tag, SQLITE3_TEXT);
		$sql->bindValue(":bs", $booru_source, SQLITE3_INTEGER);
		$sql->bindValue(":namespace", $namespace, SQLITE3_TEXT);
		$tagcount = $sql->execute()->fetchArray()[0] ?? '';
		
		$sql = $db->prepare("select * from tags where tag_name = :tag COLLATE NOCASE");
		$sql->bindValue(":tag", str_replace("_", " ", $booru_tag), SQLITE3_TEXT);
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
	
		$PageTitle = "Podobo - " . $booru_tag;
	
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
		echo "<p>" . $overallcount . " Tags Left - " . GetBooruSource($booru_source) . "</p>";
		if($booru_source == 6){
			if(is_null($namespace)){
				echo "<p>Namespace: None (" . $tagcount . ")</p>";
			}
			else if($namespace == ""){
				echo "<p>Namespace: General (" . $tagcount . ")</p>";
			}
			else{
				echo "<p>Namespace: " . htmlspecialchars($namespace) . " (" . $tagcount . ")</p>";
			}
		}
		else{
			echo "<p>Namespace: None (" . $tagcount . ")</p>";
		}
			//echo "<form autocomplete='off'>\r\n";
				echo '<input type="text" name="query" value="' . $booru_tag . '" onclick="copyTag()" />';
				//echo "<input type='submit' hidden />\r\n";
			//echo "</form>\r\n";
			
			echo "<div class='w3-center'>";
				echo "<a href='https://rule34.xxx/index.php?page=post&s=list&tags=" . rawurlencode($booru_tag) . "' target='_blank'>" . htmlspecialchars($booru_tag) . " (rule34.xxx)</a>";
			echo "</div>";

			echo "<div class='w3-center'>";
				echo "<p> - </p>";
				echo "<a href='https://e621.net/posts?tags=" . rawurlencode($booru_tag) . "' target='_blank'>" . htmlspecialchars($booru_tag) . " (e621)</a>";
			echo "</div>";

			echo "<div class='w3-center'>";
				echo "<p> - </p>";
				echo "<a href='https://danbooru.donmai.us/posts?tags=" . rawurlencode($booru_tag) . "' target='_blank'>" . htmlspecialchars($booru_tag) . " (Danbooru)</a>";
			echo "</div>";

			echo "<div class='w3-center'>";
				echo "<p> - </p>";
				echo "<a href='HydrusImages.php?namespace=" . rawurlencode($namespace) . "&tag=" . rawurlencode($booru_tag) . "' target='_blank'>" . htmlspecialchars($booru_tag) . " (Hydrus)</a>";
			echo "</div>";

			echo "<div class='w3-center'>";
				echo "<p> - </p>";
				echo "<a href='Post.php?id=" . $id . "' target='_blank'>[" . $id . "]</a>";
			echo "</div>";
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
					echo "<option value='3'>Rating</option>";
					echo "<option value='4'>Artist</option>";
					echo "<option value='5'>Studio</option>";
					echo "<option value='6'>Sex</option>";
					echo "<option value='7'>Afilliation</option>";
					echo "<option value='8'>Race</option>";
					echo "<option value='9'>Body</option>";
					echo "<option value='10'>Clothing</option>";
					echo "<option value='11'>Action</option>";
					echo "<option value='12'>Setting</option>";
					//echo "<option value='13'>Action</option>";
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
			echo "<div><input class='w3-center' id='siblingcapitalizebutton' type='button' value='Capitalize' onclick='CapitalizeSiblings()'/><input class='w3-center' id='newlinebutton' type='button' Value='New Line' onclick='AddNewLine()' /></div>";
		echo "</div>";
		
		echo "<hr />";
		
		echo "<div class='w3-center'>";
			echo "<input class='w3-center' id='submitbutton' type='button' value='Submit' onclick='SubmitTags()'/>\r\n";
			echo "<input class='w3-center' type='button' value='Clear' onclick='ClearTags()'/>";
			echo "<input class='w3-center' id='ignorebutton' type='button' value='Ignore' onclick='IgnoreTags()'/>";		
		echo "</div>";
		
		echo "<div id='response' class='w3-center'>";
		
		echo "</div>";
	}catch(exception $e){

	}
	
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
			case 6:
				return "(Hydrus PTR)";
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
		var bt = <?php echo json_encode($booru_tag, JSON_UNESCAPED_UNICODE); ?>;
		var bs = <?php echo $booru_source; ?>;
		var namespace = <?php if(is_null($namespace)) { echo json_encode(""); } else { echo json_encode($namespace, JSON_UNESCAPED_UNICODE); };  ?>;
		var submit;
		var ignore;
		var resdiv;
		var siblingcount = 0;
		var parent0_exists = false;
		var parent1_exists = false;
		var parent2_exists = false;
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
			ignore = document.getElementById("ignorebutton");
			
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

		function copyTag(){
			navigator.clipboard.writeText(bt);
		}

		function CapitalizeSiblings(){
			sibling.value = sibling.value.split('_').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('_');
			sibling.value = sibling.value.split('(').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('(');
			sibling.value = sibling.value.split('-').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('-');
			sibling.value = sibling.value.split('[').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('[');
			sibling.value = sibling.value.split('.').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('.');
			//sibling.value = sibling.value.split('\'').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('\'');
			sibling.value = sibling.value.split('\n').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('\n');

			//removed capitalize after ' due too 's

			input.value = input.value.split('_').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('_');
			input.value = input.value.split('(').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('(');
			input.value = input.value.split('-').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('-');
			input.value = input.value.split('[').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('[');
			input.value = input.value.split('.').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('.');
			//input.value = input.value.split('\'').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('\'');
			input.value = input.value.split('\n').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join('\n');
		}

		function AddNewLine(){
			sibling.value = sibling.value + '\n';
			sibling.focus();
		}
		
		function GetParent(data)
		{
			$.ajax({
				url: 'Tags/ParentAjax.php?txt=' + encodeURIComponent(data),
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					if(response.length > 0)
					{							
						for (let i = 0; i < 3 && i < response.length; i++)
						{							
							eval("parent" + i + ".value = response[" + i + "];");
							eval("parent" + i + "_exists = true;");		
							eval("parent" + i + ".disabled = true;");				
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
				url: 'Tags/AliasAjax.php?txt=' + encodeURIComponent(data),
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
						siblingcount = response.length;
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
				url: 'Tags/CheckSiblingsAjax.php?siblings=' + sibling.value.split('\n').slice(siblingcount).join('+'),
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
				var parents = "&parents="
				if(parent0.value != "" && !parent0_exists){
					parents += encodeURIComponent(parent0.value);
				}

				if(parent1.value != "" && !parent1_exists){
					parents += "+" + encodeURIComponent(parent1.value);
				}

				if(parent2.value != "" && !parent2_exists){
					parents += "+" + encodeURIComponent(parent2.value);
				}
			}
			else
			{
				var parents = "";
			}
			
			if(!providedsiblings)
			{
				if(sibling.value != "")
				{
					var sibs = sibling.value.split('\n').slice(siblingcount);
					for (let i = 0; i < sibs.length; i++) {
						sibs[i] = encodeURIComponent(sibs[i]);
					}
					var siblings = "&siblings=" + sibs.join('+');
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
			
			$.ajax({
				url: 'Tags/BooruAddTagAjax.php?tag=' + encodeURIComponent(tag) + parents + siblings + '&bt=' + encodeURIComponent(bt) + '&bs=' + bs + '&cat=' + cat + '&namespace=' + namespace,
				type: 'get',
				dataType: 'JSON',
				success: function(response){
					
					if(response.length > 0)
					{	
						resdiv.innerHTML += "<hr />";
						if(response.includes("Error")){
							resdiv.innerHTML += "<div><p style='color:red;'>" + response[response.length - 1] + "</p></div>";
						}else{
							resdiv.innerHTML += "<div><a href='Tags/Tag.php?tagid=" + response[response.length - 1] + "' target='_blank'>Tag Page</a><p>-</p></div>";	
						}
						for (let i = 0; i < response.length - 1; i++)
						{
							resdiv.innerHTML += "<div><a href='Post.php?id=" + response[i][0] + "' target='_blank'>" + response[i][0] + "</a></div>";																
						}					
					}
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					//console.log(url);
					console.log(xhr.responseText);
					console.log(thrownError);
					resdiv.innerHTML += "<div><p style='color:red;'>Ajax Error</p></div>";
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
					resdiv.innerHTML += "<hr />";
					resdiv.innerHTML += "<div><p>Tag Processing Ignored</p></div>";
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					console.log(xhr.responseText);
					resdiv.innerHTML += "<div><p style='color:red;'>Error</p></div>";
				}
			});
			ignore.value = "Ignored!";
		}

		function ClearTags()
		{
			input.value = "";
			parent0.value = "";
			parent0.disabled = false;
			parent0_exists = false;
			parent1.value = "";
			parent1.disabled = false;
			parent1_exists = false;
			parent2.value = "";
			parent2.disabled = false;
			parent2_exists = false;
			sibling.value = "";
			category.value = 0;
			siblungcount = 0;
		}
		
	</script>
</html>