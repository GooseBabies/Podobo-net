<?php
	date_default_timezone_set("America/New_York");
	$getdate = getdate();

	if(isset($_GET["m"])) { $month = $_GET["m"]; } else { $month = $getdate["mon"]; };
    if(isset($_GET["y"])) { $year = $_GET["y"]; } else { $year = $getdate["year"]; };
    
	$monthstart = getdate(mktime(0, null, null, $month, 1, $year))["weekday"];
	//echo "<!--" . print_r($getdate) . "-->";	
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
	    <title>291 Gillett</title>
	    <link rel="stylesheet" type="text/css" href="style/GillettStyle.css" />		
		<link rel="stylesheet" href="style/w3.css" />
		<link rel="icon" type="image/x-icon" href="">

	</head>
	<body>
	<div class="w3-bar w3-theme w3-left-align w3-medium container_header">
		<a id="cal" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey">Calendar</a>		
		<a id="add-task" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey">Add Task</a>
		<a id="grocery" class="w3-bar-item w3-button w3-hide-small w3-hover-blue-grey">Grocery List</a>

	</div>
	<main class="row">
        <div class="col-2 w3-theme main-left">
			<h4>Tasks</h4>
					
		</div>
		<div class="col-9">
			<Table class="cal-month">
				<tr>
					<th colspan="7"><?php echo DateTime::createFromFormat('!m', $month)->format('F') . " " . $year; ?></th>
				</tr>
				<tr>					
					<th>Monday</th>
					<th>Tuesday</th>
					<th>Wednesday</th>
					<th>Thursday</th>
					<th>Friday</th>
					<th>Saturday</th>
					<th>Sunday</th>
				</tr>

				<?php
				$start = 0;
				switch ($monthstart) {					
					case "Monday":
						$start = 0;
						break;
					case "Tuesday":
						$start = 1;
						break;
					case "Wednesday":
						$start = 2;
						break;
					case "Thursday":
						$start = 3;
						break;
					case "Friday":
						$start = 4;
						break;
					case "Satruday":
						$start = 5;
						break;	
					case "Sunday":
						$start = 6;
						break;								
				}

				$rowlim = ($start == 6 ? 6 : 5);

					for($i = 0; $i<$rowlim; $i++){
						for($j = 0; $j<7; $j++){
							if($j == 0){
								echo "<tr>";
							}

							$day = $i*7 + ($j + 1 - $start);

							if($day < 1 || $day > date('t')){
								echo "<td></td>";
							}
							else if($day == $getdate["mday"] && $getdate['mon'] == $month){
								echo "<td class='current-day'><p class='month-num'>" . $day . "</p>";
								if($day == 18){
									echo "<ul class='day-tasks'>";
									echo "<li>task 1</li>";
									echo "<li>task 2</li>";
									echo "<li>task 3</li>";
									echo "</ul>";
								}
								echo "</td>";
							}
							else{
								echo "<td><p class='month-num'>" . $day . "</p>";
								if($day == 18){
									echo "<ul class='day-tasks'>";
									echo "<li>task 1</li>";
									echo "<li>task 2</li>";
									echo "<li>task 3</li>";
									echo "</ul>";
								}
								echo "</td>";
							}
							
							if($j == 7){
								echo "</tr>";
							}
						}
					}

				?>
				<!-- <tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>01</td>
				</tr>
				<tr>
					<td>02</td>
					<td>03</td>
					<td>04</td>
					<td>05</td>
					<td>06</td>
					<td>07</td>
					<td>08</td>
				</tr>
				<tr>
					<td>09</td>
					<td>10</td>
					<td>11</td>
					<td>12</td>
					<td>13</td>
					<td>14</td>
					<td>15</td>
				</tr>
				<tr>
					<td>16</td>
					<td>17</td>
					<td>18</td>
					<td>19</td>
					<td>20</td>
					<td>21</td>
					<td>21</td>
				</tr>
				<tr>
					<td>22</td>
					<td>23</td>
					<td>24</td>
					<td>25</td>
					<td>26</td>
					<td>27</td>
					<td>28</td>
				</tr>
				<tr>
					<td>29</td>
					<td>30</td>
					<td>31</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr> -->
		</div>
	</main>
</body>
</html>
