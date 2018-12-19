<?php
session_start();
?>

<!DOCTYPE html>	<!--make.php-->
<html>
<head>
<title>Make an Appointment</title>
</head>

<body>
<?php
// User logged in
if($_SESSION['Email'])	//***is still valid '== UserID'
{
	// Connect to DB
	require_once 'ncrypd/info.php';
	$db_server = mysql_connect($db_hostname, $db_username, $db_password);
	if(!$db_server) die("Connection failed: ").mysql_error();

	// Select Schema
	mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

	// Page Title
	echo "<h1>Make An Appointment</h1>";

	// Get Session ID and Account Type
	$Sessionid = mysql_query("SELECT PatientID FROM PATIENT WHERE Email = '".$_SESSION['Email']."'", $db_server) or die("Couldn't save the information").mysql_error();
	$isPatient = True;
	$_SESSION['ActType'] = 'Patient';
	if(!mysql_result($Sessionid,0))
	{
		$Sessionid = mysql_query("SELECT ProviderID FROM PROVIDER WHERE Email = '".$_SESSION['Email']."'", $db_server) or die("Couldn't save the information").mysql_error();
		$isPatient = False;
		$_SESSION['ActType'] = 'Provider';

	}

	// PATIENT ACCOUNTS (MAKE APPOINTMENT)
	if($isPatient)
	{
		// Selection header
		echo "<form name='form1' id='form1' method='post' action='cal.php' target='iframe1'>My Doctor<select name='SelectDoc' onchange='this.form.submit()'>";

		// Query all names of provider table for display
		$names = mysql_query("SELECT ProviderFirst, ProviderLast, ProviderID FROM PROVIDER", $db_server) or die("Couldn't save the information").mysql_error();
		echo "<option value='na' name='na'>--select--</option>";

		// Fetch each Provider as an option
		while($row = mysql_fetch_array($names))
		{
			echo "<option value='".$row['ProviderID']."' name='".$row['ProviderID']."'>".$row['ProviderFirst']." ".$row['ProviderLast']."</option>";
		}

		echo "</select></form><br><br>";
	}
	// PROVIDER ACCOUNTS (MAKE APPOINTMENT)
	else
	{
		// Selection header
		echo "<form name='form1' id='form1' method='post' action='cal.php' target='iframe1'>My Patient<select name='SelectPat' onchange='this.form.submit()'>";

		// Query all names of patient table for display
		$names = mysql_query("SELECT PatientFirst, PatientLast, PatientID FROM PATIENT", $db_server) or die("Couldn't save the information").mysql_error();
		echo "<option value='na' name='na'>--select--</option>";

		// Fetch each Patient as an option
		while($row = mysql_fetch_array($names))
		{
			echo "<option value='".$row['PatientID']."' name='".$row['PatientID']."'>".$row['PatientFirst']." ".$row['PatientLast']."</option>";
		}

		echo "</select></form><br><br>";
	}

	// Iframe for framed page display (stay on page effect)
	echo "<iframe name='iframe1' height = '500px' width = '1000px'></iframe>";
}
// Not signed in
else
{
	// redirect to login
	echo "<script>location.href='login.php'</script>";
}
?>

</body>
</html>
