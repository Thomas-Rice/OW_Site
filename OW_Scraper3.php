<?php

// Function for making a GET request using cURL
function curlGet($url) {
	 $ch = curl_init(); // Initialising cURL session
	 // Setting cURL options
	 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Returning transfer as a string
	 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); // Follow location
	 curl_setopt($ch, CURLOPT_URL, $url); // Setting URL
	 $results = curl_exec($ch); // Executing cURL session the results
	 return $results; // Return the results
}

// Function to return XPath object
function returnXPathObject($item) {
	 $xmlPageDom = new DomDocument(); // Instantiating a new DomDocument object
	 @$xmlPageDom->loadHTML($item); // Loading the HTML from  downloaded page
	 $xmlPageXPath = new DOMXPath($xmlPageDom); // Instantiating new XPath DOM object
	 return $xmlPageXPath; // Returning XPath object
}

// Function to convert the battle tag to the correct format for searching the stats
function choosePlayerName($playerName){
	$editedName = str_replace("#", "-", $playerName);
	$getPlayerPage = curlGet('https://playoverwatch.com/en-us/career/pc/eu/'.$editedName); // Calling function curlGet and storing returned results in $packtPage variable
	return $getPlayerPage;
}


// Function to return the names of characters from a dropdown list.
function getCharList($pageToParse){
	$characterArray = array();
	$played = $pageToParse->query('//select[@data-group-id="stats"]'); // Querying for the dropdown list of cahr names
	$test = $played->item(0)->getElementsByTagName("option"); // Each char name is stored in the option section

	foreach ($test as $key) {
		if($key->nodeValue ){
			// echo ($key->nodeValue),PHP_EOL; // get each character name.
			array_push($characterArray, $key->nodeValue); 
		}
	}
	return $characterArray;
}


// Function to return the player name from the Title
function getTitle($pageToParse){
	$title = $pageToParse->query('//h1'); // Querying for <h1> (title of book)
	// If title exists
	if ($title->length > 0) {
		echo 'Player Name is '.$title->item(0)->nodeValue,PHP_EOL;
	}
	else{
		echo 'No Player Name Found',PHP_EOL;
	}
}

// Function to return the games won
function getGamesWon($pageToParse){
	$played = $pageToParse->query('//div[@class="column xs-12 md-6 xl-4"]'); // Querying for <h1> (title of book)
	$rows = $played->item(6)->getElementsByTagName("tr");
	
	foreach($rows as $row){
		if (strpos($row->nodeValue, 'Games Won') !== false){
			$gamesWon = str_replace("Games Won", "", $row->nodeValue);
			echo $gamesWon, PHP_EOL;
		}
	}
}





// Function to return all of the game data into an array where the key is the name of the data and the value is the value.
function getAllGameData($pageToParse){
	$statArray = array();
	$gameDataArray = Array();
	$statBlock = $pageToParse->query("//*[contains(@class, 'row gutter-18@md spacer-12 spacer-18@md js-stats toggle-display')]");
	$lol = $statBlock->item(0)->getElementsByTagName("td");
	$characterList = getCharList($pageToParse);
	$characterIterator = 0;


	foreach ($characterList as $character) {
		foreach ($statBlock as $block) {
			$invert = false;
			$keyName = '';

			// The below is done as the format of the data is like - most kills\n 20.  So a new line for the stat name then the value. This means that on the for loop we 
			// need to store the key name first , then associate a value to it. Then reset for the next set of values.   
			foreach ($block->getElementsByTagName("td") as $var){ //for each stat in each containing box
										if (empty($characterList[$characterIterator])){
					break;}
				if ($invert === false){ // if we have no variable for the key
					$keyName = $var->nodeValue; //set the key to the variable name
					// echo $var->nodeValue,PHP_EOL;
					$invert = !$invert; // Invert the false to true in order to assign the key and value to the array.
				}
				elseif($characterList[$characterIterator] != Null){
					$newName = $characterList[$characterIterator].' - '.$keyName; // Create a new string that has the character attached to the stat - this will be changed later
					$statArray[$newName] = $var->nodeValue; // assign the key and value
					$invert = !$invert; // invert the true to false to reset
					$keyName = ''; // Clear the variable
				}					
				$gameDataArray[$characterList[$characterIterator]] = $statArray;

			}
			$characterIterator += 1;
			$statArray = array();	
		}
	}

		// foreach ($gameDataArray as $charlol => $value){ // Print out the values of the array
		// 	echo "Key: $charlol; Value: $value\n";	
		// 	foreach ($value as $key => $values) {
		// 		echo "Char: $charlol; Key: $key; Value: $values\n";
		// 	}
		// }
	return $gameDataArray;

}

// this will take the stats of a character after it has all been calculated via anoter function.
function getCurrentCharacterStats($characterToStat, $keysToParse){
	$updatedKey = $characterToStat.' - '.$keysToParse;
	$updatedKey2 = $characterToStat.' - '.$keysToParse.date("h:i:sa");
	$statFile = file_get_contents('finalStatFile.json');
	$DecodedStatFile = json_decode($statFile, true);

	$characterStatArray = $DecodedStatFile[$characterToStat];

	// echo $characterToStat.' '.$keysToParse,' is ',$characterStatArray[$updatedKey].PHP_EOL;

	$statArray = array($updatedKey2 => $characterStatArray[$updatedKey]);

	// echo $statArray[$updatedKey2];


}

// Get stats over time 
function getStatsOverTime($characterToStat, $keysToParse){
	// $statOverTime = array();
	// $dates = array();
	$arrayToReturn = array();
	$dateAndTime = array();

	foreach ($keysToParse as $key) {
		// Get all the files stored in the folder as they contain the stat history over time
		foreach (glob("*20{16,17}.json", GLOB_BRACE) as $filename) { //For each file containing stat history - formatted with date.json
			$updatedKey = $characterToStat.' - '.$key; // Change the string to be correct to the key of the array
			
			// String operations to get the date of the file
			// $splitString = explode("-", $filename); 
			$minusTheJson = explode('.' , $filename);
			$dateOfFile = $minusTheJson[0];

			// Open the file
			$statFile = file_get_contents($filename);
			$DecodedStatFile = json_decode($statFile, true);
			// get the date from the key in the array
			$characterStatArray = $DecodedStatFile[$characterToStat];
			// echo $characterToStat.' '.$key,' is ',$characterStatArray[$updatedKey],' for date ', $filename.PHP_EOL;

			// array_push($statOverTime, $characterStatArray[$updatedKey] ); // add the data to a new array
			// array_push($dates, $dateOfFile); // add the date of the file to a new array

			$dateAndTime[$dateOfFile] = $characterStatArray[$updatedKey]; //Store the data of date as the key and 
			}

		$arrayToReturn[$key] = $dateAndTime;

		}
		// foreach ($dates as $value) {
		// 	echo $value,PHP_EOL;
		// }

		return $arrayToReturn;
}

function ripStatsFromFile(){
	// $blankArray = array();
	$dateAndStat = array();
	$test = array();
	$files = glob('*.{json}', GLOB_BRACE);
	foreach($files as $file) {
		$data = file_get_contents ($file);
		$json = json_decode($data, TRUE);
		$cahar = $json['Ana'];
		// echo $cahar['Ana - Scoped Accuracy'],PHP_EOL;
		// array_push($blankArray, $cahar['Ana - Scoped Accuracy']);


		$minusTheJson = explode('.' , $file);
		$dateOfFile = $minusTheJson[0];
		$dateAndStat[$dateOfFile] = $cahar['Ana - Scoped Accuracy'];
		array_push($test, array($dateOfFile, $cahar['Ana - Scoped Accuracy']));	
	}
	// print_r($dateAndStat);
	$test = array(array(1,20),array(2,58),array(3,100));
	return $test;
}


$battleTag = choosePlayerName('Tikkle#2648');
$packtPageXpath = returnXPathObject($battleTag); // Instantiating new XPath DOM object
$gameDataArray2 = getAllGameData($packtPageXpath);


// Set Date and Time and write out that as the filename
date_default_timezone_set("Europe/London");
$dateAndTime = date("h:i:sa").'-'.date("d:m:Y");
$myfile = fopen("$dateAndTime.json", "w");
fwrite($myfile, json_encode($gameDataArray2, JSON_PRETTY_PRINT));
fclose($myfile);

ripStatsFromFile();

// characterStats('Reaper', 'Souls Consumed')
// getStatsOverTime('Ana', 'Scoped Accuracy');

?>

















































<html>

<head> 
	<meta charset="utf-8">
	<title>OW Character Stats</title>
	<style>
	
		body {	
			margin: 0 15%;
			font-family: Futura,century gothic,arial,sans-serif;
			background-color: #ececec;}

		h1 {			
			text-align: center;
			vertical-align: middle;
			padding: 7px 45px 6px;
			text-transform: uppercase;

			border: 3px solid #ff9c00;
			background-color: #ff9c00;
			color: #333;
			line-height: 1;
			letter-spacing: .025em;
			font-family: Futura,century gothic,arial,sans-serif;
			font-weight: 700;
			font-size: 1.6rem;}

		h2 { 
			text-align: center;
			border: 3px solid;
		    color: #00a5e2;
    		border-color: #00a5e2;
    		background-color: transparent;

			}		
			#placeholder {
			    width: 450px;
			    height: 200px;
				}	
	</style>


 </head>


<body>


<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.symbol.min.js"></script>
<script type="text/javascript" src="http://raw.github.com/markrcote/flot-axislabels/master/jquery.flot.axislabels.js"></script>
<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.time.min.js"></script>
 		<script type="text/javascript">
		    
		    <?php $php_array = ripStatsFromFile(); ?> //Call the PHP function to get the formatted data
			var js_array = <?php echo json_encode($php_array );?>;
			alert(js_array);
			$(document).ready(function () {
			    $.plot($("#placeholder"), [js_array],{
			        xaxis: {
			            min: (new Date(2016, 11, 18)).getTime(),
			            max: (new Date(2017, 11, 15)).getTime(),
			            mode: "time",
			            tickSize: [1, "month"],
			            monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],

			        },
			        yaxis: {
			            axisLabel: 'Value',
			            axisLabelUseCanvas: true,
			            axisLabelFontSizePixels: 12,
			            axisLabelFontFamily: 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
			            axisLabelPadding: 5
			        },
			        series: {
			            lines: { show: true },
			            points: {
			                radius: 3,
			                show: true,
			                fill: true
			            },
			        },
			        grid: {
			            hoverable: true,
			            borderWidth: 1
			        },
			        legend: {
			            labelBoxBorderColor: "none",
			                position: "right"
			        }
			    });
						});
		</script>



	<section id="Favourites">
		<h1>Overwatch Stats!</h1>


<?php
foreach ($gameDataArray2 as $charlol => $value){ // Print out the values of the array
	// echo "Key: $charlol; Value: $value\n";	
	if (empty($charlol) == false){
	foreach ($value as $key => $values) { ?>
		<li> <?php echo "Char: $charlol; Key: $key; Value: $values\n"; ?> </li> 
	<?php }}
}
?>


</section>

         <div class="chart">
            <h3>Character Stats</h3>
            <table id="data-table" border="1" cellpadding="10" cellspacing="0"
            summary="stats over time of your character">
               <caption>Stats Over Time</caption>
               <tbody>
				<tr <th scope="row">Ana</th>
                <?php 
                	$testArray = array();
                	$statAndDate = getStatsOverTime('Ana', array('Scoped Accuracy', 'Damage Done - Average')); 
                	$increment = 0;
               		foreach ($statAndDate as $key) { 
               			$var = array_keys($statAndDate); // this is becuase I cannot work out how to increment the key othewise Doing $key => $name screws up the rest of the for loop. ?>


						<thead>
						<?php foreach($key as $stats => $value){ ?>
               				<th scope="col"> <?php echo $stats; ?> </th>
               			<?php } ?>
               			</thead>

	               			<?php foreach($key as $stats => $values){ ?>
								<td> <?php echo $var[$increment], '-' ,$values; ?> </td> 
							<?php }
								$testArray[$stats]=$values ;
								foreach ($testArray as $key => $value) { ?>
									<li> <?php echo $value,PHP_EOL; ?> </li> 
								 <?php } ?>
				</tr>
				 <?php $increment += 1; } ?>

               </tbody>
            </table>
         </div>
		<div id="placeholder"></div>



</body>
<html>














