<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
</head>

<body>

	<?php	//include for Provider accounts as well 16
		if($_SESSION['Email'])	// user logged in
		{
			// Connect to database
			require_once 'ncrypd/info.php';
			$db_server = mysql_connect($db_hostname, $db_username, $db_password);
			if(!$db_server) die("Connection failed: ").mysql_error();

			// Select Schema
			mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

			// Get Session ID (an update will come with a global variable for Session ID)
			$ActType = strtoupper($_SESSION['ActType']);
			$Sessionid = mysql_query("SELECT ".$_SESSION['ActType']."ID FROM ".$ActType." WHERE Email = '".$_SESSION['Email']."'", $db_server) or die("Couldn't save the information").mysql_error();
			$Sessionid = mysql_result($Sessionid,0);

			if(isset($_SESSION['UpdateNDX']) && isset($_POST['thisone']))
			{

				list($Adate,$Atime) = split(" ", $_POST['thisone']);

				// Check for existing appointments on that date (User may have forgotten)
				$Conflict = mysql_query("SELECT Indx FROM APPOINTMENT WHERE PatientID = '".$Sessionid."' AND ApptDate = '".$Adate."' AND ApptTime = '".$Atime."'",$db_server) or die("Couldn't save the information").mysql_error();
				$Conflict = mysql_result($Conflict,0);

				if(!$Conflict)
				{
					mysql_query("UPDATE APPOINTMENT SET ApptDate = '".$Adate."', ApptTime = '".$Atime."' WHERE Indx = '".$_SESSION['UpdateNDX']."'",$db_server) or die("Unable to reschedule appointment").mysql_error();
					echo "<h3>SUCCESS</h3>";
					echo "Your appointment was rescheduled to ".$Adate." at ".$Atime.".<br>";
					// I'm going to make a navigation menu before asking user what they want
				}
				else
				{
					$Who = mysql_query("SELECT b.ProviderLast FROM APPOINTMENT a JOIN PROVIDER b ON a.ProviderID = b.ProviderID WHERE Indx = '".$Conflict."'",$db_server) or die("Couldn't save the information").mysql_error();
					$Who = mysql_result($Who,0);
					$_SESSION['Index'] = $Conflict;		// store the index-to-update for reschedule.php
					echo "<h3>You are already scheduled for this time with Dr. ".$Who.".</h3><br>";
					echo "Would you like to <a href='reschedule.php' style='text-decoration:none'>reschedule</a>?";
				}

			}
			else
			{
				echo "<h3>What did you do now?</h3>";
				echo "Just kidding, it was probably me.<br><br>";	// *insert Homer Simpson

				// echo "In the meantime, ... something entertaining while AI fixes everything #goals";
			}
		}
		else
		{
			echo "<script>location.href='login.php'</script>";
		}
	?>
</body>
</html>
