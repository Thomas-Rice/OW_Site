<?php

//Connect to server and return the connection
function createConnection(){
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "OWStats";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	} 

	return $conn;

}

//Create a table to use on the webpage
function createTable($conn) {
	$result = mysqli_query($conn, "SELECT * FROM stats ORDER BY `Game` DESC");
	echo '<div id="cellTest">';
    echo '<table id="data-table" 
    summary="stats over time of your character" >'; // start a table tag in the HTML

    //create heading for table
    echo "	<tr>
   			<th>Game</th>
			<th>W/L</th>
			<th>Diff</th>
			<th>SR</th>
			<th>Win Streak</th>
			<th>Loss Streak</th>
			<th>Map</th>
			<th>Character Played</th>
			<th>Time</th>
			</tr>";


	//populate with date from DB
	while($row = mysqli_fetch_array($result)){   //Creates a loop to loop through results

		//Set the colour of the cell depending on win or loss
		if ($row['Score'] == 'L'){
			$ScoreBGColour = '#FF0000';
		}
		elseif ($row['Score'] == 'W') {
			$ScoreBGColour = '#5DE027';
		}
		elseif ($row['Score'] == 'D') {
			$ScoreBGColour = '#FF9700';
		}
		else{
			$ScoreBGColour = '';
		}


		//Increment the hex colour depending on win/loss streak
		$inc = $row['Win_Streak'] * 20;
		//Set the colour of the cell depending on win or loss
		if ($row['Loss_Streak'] > 0){
			$StreakBGcolour = '#FF00'.(string)$inc;
		}
		elseif ($row['Win_Streak'] > 0) {
			$StreakBGcolour = '#5DE0'.(string)$inc;
		}
		else{
			$StreakBGcolour = '#FF97'.(string)$inc;
		}



		//Print the table to the page.
		echo "<tr>
				<td>" .$row['Game'] ."</td>
				<td bgcolor= $StreakBGcolour>" . $row['Score'] . "</td>
				<td>" . $row['SR_Change'] . "</td>
				<td>" .$row['Rank'] . "</td>
				<td bgcolor= $StreakBGcolour>" . $row['Win_Streak'] . "</td>
				<td bgcolor= $StreakBGcolour>" .$row['Loss_Streak'] . "</td>
				<td>" . $row['Map'] . "</td>
				<td>" . $row['Character_Played'] . "</td>
				<td>" . $row['Time'] . "</td></tr>";  //$row['index'] the index here is a field name
		}
	echo '</div></table>'; //Close the table in HTML
	
}


function calculateGameStats($conn){
	$previousRating = getLastRowNumber($conn);
	$Rating = $_POST["Rating"];
	$Score = '';

	//Calculate Win oR loss.
	$winOrLoss = $Rating - $previousRating;
	if($winOrLoss > 0){
		$Score = 'W';
	}
	elseif ($winOrLoss === 0) {
		$Score = 'D';
	}
	else{
		$Score = 'L';
	}
	// echo 'Score is ... ' . $Score;

	//Calculate SR Change.
	$winOrLoss = $Rating - $previousRating;
	// echo 'Rank is ... ' . $winOrLoss;

	//Calculate if we are on a win or loss streak.
	$Win = getwinLossStreak($conn);
	if ($Score === 'W'){
		//take the previous winstreak value and increment 
		$winStreak = $Win[0] +1;
		//if we win reset the loss
		$lossStreak = 0;
	} 
	elseif ($Score === 'L') {
		//take the previous lossstreak value and increment 
		$lossStreak = $Win[1] +1;
		//if we lose reset the win
		$winStreak = 0;
	}
	else{
		//If it's a draw then reset both
		$winStreak = 0;
		$lossStreak = 0;
	}

	return array('Score' => $Score, 'Diff' => $winOrLoss, 'winStreak' => $winStreak, 'lossStreak' => $lossStreak );

}


function getwinLossStreak($conn){
	$winStreakNumber = mysqli_query($conn, "SELECT Win_Streak FROM stats ORDER by Game DESC LIMIT 1");
	$winStreak = mysqli_fetch_row($winStreakNumber);
	// echo 'Win'.$winStreak[0];	

	$lossStreakNumber = mysqli_query($conn, "SELECT Loss_Streak FROM stats ORDER by Game DESC LIMIT 1");
	$lossStreak = mysqli_fetch_row($lossStreakNumber);
	// echo 'Loss'.$lossStreak[0];	

	return array($winStreak[0], $lossStreak[0]);
}

function getLastRowNumber($conn){
	$resultRow = mysqli_query($conn, "SELECT Rank FROM stats ORDER by Game DESC LIMIT 1");
	$rows = mysqli_fetch_row($resultRow);
	return $rows[0];	
}


function submitButtonPressed($conn, $Score, $Diff, $winStreak, $lossStreak) {
	$Character = $_POST["Character"];
	$Map = $_POST["Map"];
	$Rating = $_POST["Rating"];

	$sql = "INSERT INTO stats (Score, SR_Change, Character_Played, Map, Rank, Win_Streak, Loss_Streak)
			 VALUES ('$Score', '$Diff', '$Character', '$Map', '$Rating', '$winStreak', '$lossStreak')";

	if ($conn->query($sql) === TRUE) {
	    $SuccessMessage = "Submission Successfully Created!"; 
	    echo "<script type='text/javascript'>alert('$SuccessMessage');</script>"; //Create an alert.
	    createTable($conn);
		} 
	else {
	    echo "Error: " . $sql . "<br>" . $conn->error;
		}

	}

function closeConn($conn){
	$conn->close();
}



//==============================================================================================================================================================================================================================================================================

// Setup ======================================================
    // Create two arrays to store the data in.

$errors         = array();      // array to hold validation errors
$data           = array();      // array to pass back data


// validate the variables ======================================================
    // if any of these variables don't exist, add an error to our $errors array

    if (empty($_POST['Rating']))
        $errors['Rating'] = 'Rating is required.';

    if (empty($_POST['Character']))
        $errors['Character'] = 'Character is required.';

    if (empty($_POST['Map']))
        $errors['Map'] = 'Map is required.';


// return a response ===========================================================

    // if there are any errors in our errors array, return a success boolean of false
    if ( ! empty($errors)) {

        // if there are items in our errors array, return those errors
        $data['success'] = false;
        $data['errors']  = $errors;

            foreach ($data['errors'] as $error => $value) { //Alert all errors it they occur
    	echo "<script type='text/javascript'>
    			alert('$value');
    			</script>"; //Create an alert.
   		 }

          } else {

        
		// if the form is submitted correctly then run the below functions
		$conn = createConnection();
		$calculatedStats = calculateGameStats($conn);
		getwinLossStreak($conn);
		submitButtonPressed($conn, $calculatedStats['Score'], $calculatedStats['Diff'], $calculatedStats['winStreak'], $calculatedStats['lossStreak']);
		
		closeConn($conn);

        // show a message of success and provide a true success variable
        $data['success'] = true;
        $data['message'] = 'Success!';
    }






?>


