<!DOCTYPE html>

<html>
<body>
<?php	// connecting to database server
	require_once 'ncrypd/info.php';
	$db_server = mysql_connect($db_hostname, $db_username, $db_password);

	if(!db_server) die("Connection failed: " . mysql_error());
	
	echo "<h1>Congratulations! You are now a member of Kaesar Health</h1>";

	// selecting a schema
	mysql_select_db($db_database)
 	or die("Unable to select database: " . mysql_error());

	
	// $_POST check current status: works
	//echo 'Hello ' . htmlspecialchars($_POST['PatientFirst']) . '!';
	// isset check current status: works
	/*
	if (isset($_POST['Email']))
	{
		echo 'Your email is ' . htmlspecialchars($_POST['Email']);
	}
	*/
	
	if (isset($_POST['Email']) && isset($_POST['Pass']) &&
	    isset($_POST['PatientLast']) && isset($_POST['PatientFirst']) 
	    && isset($_POST['DoB']) && isset($_POST['Country']) && 
	    isset($_POST['State']) && isset($_POST['City']) && 
	    isset($_POST['Zip']))
	{
		$Email = get_post('Email');
		$Pass  = get_post('Pass');
		$PatientLast = get_post('PatientLast');
		$PatientFirst = get_post('PatientFirst');
		$DoB = get_post('DoB');
		$Country = get_post('Country');
		$State = get_post('State');
		$City = get_post('City');
		$Zip = get_post('Zip');
		$Address = get_post('Address');
		$Apt = get_post('Apt');

		$sql = "INSERT INTO PATIENT (`Email`, `Pass`, `PatientLast`,
			 `PatientFirst`,`Dob`,`Country`, `State`, `City`,
			 `Zip`, `Address`, `Apt`) VALUES ('$Email', '$Pass', 
			 '$PatientLast','$PatientFirst', '$DoB', 
			 '$Country', '$State','$City', '$Zip', 
			 '$Address', '$Apt')";

		if (!mysql_query($sql, $db_server)) echo "INSERT failed: $sql<br>" .
		mysql_error() . "<br><br>";
	}

	mysql_close($db_server);
	echo "<a href='login.php' style='text-decoration:none'>Login</a> to your account";
	function get_post($var)
	{
		return mysql_real_escape_string($_POST[$var]);
	} ?>
</body>
</html>
