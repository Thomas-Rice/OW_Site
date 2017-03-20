





<html>

<head> 
	<meta charset="utf-8">
	<title>OW Character Stats</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css"> <!-- load bootstrap via CDN -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>

	<style>
/*		div.background {
		background-color: #333;
		border: 2px solid black;
		opacity: 0.6;
		}*/

		body {	
			margin: 0 15%;
			font-family: Futura, Helvetica, Roboto, Arial, sans-serif;
			background-color: #ececec;}

		h1 {			
			text-align: center;
			vertical-align: middle;
			text-transform: uppercase;

			border: 3px solid #ff9c00;
			background-color: #ff9c00;
			border-color: #FFFFFF;
			color: #333;
			font-family: Futura, Helvetica, Roboto, Arial, sans-serif;
		}

		h2 { 

			text-align: center;
			border: 3px solid;
		    color: #ff9c00;
    		border-color: #000000;
    		background-color: #FFFFFF;
    		font-family: Futura, Helvetica, Roboto, Arial, sans-serif;

			}		
		table {
			margin: 10px;
			overflow-x:auto;
		}


		table, th, td, tr {
			vertical-align: center;
    		border: 1px solid black;
    		color: #333;
    		font-family: Futura, Helvetica, Roboto, Arial, sans-serif;
    		padding: 10px;
    		border-spacing: 0px;
			}

		tr:nth-child(even) {
		    background-color: #dddddd;
			}

		tr:hover {background-color: #f5f5f5}

		/* Create Scrolling table */
		tbody, thead tr { display: block; }

		tbody {
		    height: 500px;
		    overflow-y: auto;
		}
		

	</style>


 </head>


		<div id="titleBar"></div>
		<h1>OW Stat Traker</h1>
		<br>
		<section id = "UserInputBar" align="center">
			<form id="stat_submission_form" action=" " method="post">
				<div class="input">
  				<input type="text" name="Character" placeholder="Character">
			  	<input type="text" name="Rating" placeholder="2100">

			  		<input list="Map" name="Map" placeholder="Hanamura">
					  <datalist id="Map">
					    <option value="Hanamura">
					    <option value="Temple of Anubis">
					    <option value="Dorado">
					    <option value="Route 66">
					    <option value="Watchpoint: Gibraltar">
					    <option value="Hollywood">
					    <option value="King's Row">
					    <option value="Numbani">
					    <option value="Eichenwalde">
					    <option value="Ilios">
					    <option value="Lijiang Tower">
					    <option value="Nepal">
					    <option value="Oasis">
					  </datalist>
					<input type="submit" name="SubmitButton" >
			</div>
			</form>
			
		</section>

<div id = "TableTitleBar">
	<h2> Stats </h2>
</div>
<?php  include 'dataGatheringFunctions.php';  ?>


<script> 

	$( "th" ).resizable();
</script> 

<script> 


$(document).ready(function(){
    $("#TableTitleBar").click(function(){
        $("#cellTest").slideToggle("fast");
    });
});
</script>



<html>












