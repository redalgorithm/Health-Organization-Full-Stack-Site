<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
</head>

<body>
	<?php
	if($_SESSION['Email'])
	{
		// Connect to database
		require_once 'ncrypd/info.php';
		$db_server = mysql_connect($db_hostname, $db_username, $db_password);
		if(!$db_server) die("Connection failed: ").mysql_error();

		// Select Schema
		mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

		// get sessionid
		$ActType = strtoupper($_SESSION['ActType']);
		$Sessionid = mysql_query("SELECT ".$_SESSION['ActType']."ID FROM ".$ActType." WHERE Email = '".$_SESSION['Email']."'", $db_server) or die("Couldn't save the information").mysql_error();
		$Sessionid = mysql_result($Sessionid,0);

		// FOR PATIENT ACCOUNTS
		if($_SESSION['ActType'] == 'Patient')
		{
			// Check if post data properly set
			if(isset($_POST['SelectDate']) && isset($_SESSION['SelectDoc']))
			{
				// Segregate selected date into date and time
				list($Adate, $Atime) = split(" ", $_POST['SelectDate']);	//no need for mysql_real_escape_string

				// Find duplicate providerID's (at most one appointment per provider)
				$Excessive = mysql_query("SELECT Indx FROM APPOINTMENT WHERE ProviderID = '".$_SESSION['SelectDoc']."' AND PatientID = '".$Sessionid."'",$db_server) or die("Couldn't save the information").mysql_error();
				$Excessive = mysql_result($Excessive,0);
				
				// Proceed to make appointment
				if(!$Excessive)
				{
					// Check for existing appointments on that date (User may have forgotten)
					$Conflict = mysql_query("SELECT Indx FROM APPOINTMENT WHERE PatientID = '".$Sessionid."' AND ApptDate = '".$Adate."' AND ApptTime = '".$Atime."'",$db_server) or die("Couldn't save the information").mysql_error();
					$Conflict = mysql_result($Conflict,0);

					if(!$Conflict)
					{
						mysql_query("INSERT INTO APPOINTMENT (`ProviderID`, `PatientID`, `ApptDate`, `ApptTime`, `Department`, `Room`) VALUES ('".$_SESSION['SelectDoc']."', '".$Sessionid."', '".$Adate."', '".$Atime."', '".rand(1,3)."', '".rand(100,370)."')", $db_server) or die("Couldn't insert the data").mysql_error();
						echo "<h2>Appointment was made successfully!</h2><br>Would you like to see your <a href='schedule.php' style='text-decoration:none;'>schedule</a>?";
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
				// Goteem
				else
				{
					echo "<h3>Our providers are very busy, you may only make one appointment for each.</h3>";
					echo "<a href='make.php' style='text-decoration:none'>Back</a>";
				}

			}
			// A selection was not made (hopefully it's not an error)
			else
			{
				echo "<script>alert('Please make a selection')</script>";
				echo "<script>location.href='make.php'</script>";
			}
		}
		// FOR PROVIDER ACCOUNTS
		else if($_SESSION['ActType'] == 'Provider')
		{
			// Check if post data properly set
			if(isset($_POST['SelectDate']) && isset($_SESSION['SelectPat']))
			{
				// Segregate selected date into date and time
				list($Adate, $Atime) = split(" ", $_POST['SelectDate']);	//no need for mysql_real_escape_string

				// Find duplicate providerID's (at most one appointment per provider)
				$Excessive = mysql_query("SELECT Indx FROM APPOINTMENT WHERE ProviderID = '".$Sessionid."' AND PatientID = '".$_SESSION['SelectPat']."'",$db_server) or die("Couldn't save the information").mysql_error();
				$Excessive = mysql_result($Excessive,0);

				// Proceed to make appointment
				if(!$Excessive)
				{
					// Check for existing appointments on that date (User may have forgotten)
					$Conflict = mysql_query("SELECT Indx FROM APPOINTMENT WHERE PatientID = '".$_SESSION['SelectPat']."' AND ApptDate = '".$Adate."' AND ApptTime = '".$Atime."'",$db_server) or die("Couldn't save the information").mysql_error();
					$Conflict = mysql_result($Conflict,0);

					if(!$Conflict)
					{
						mysql_query("INSERT INTO APPOINTMENT (`ProviderID`, `PatientID`, `ApptDate`, `ApptTime`, `Department`, `Room`) VALUES ('".$Sessionid."', '".$_SESSION['SelectPat']."', '".$Adate."', '".$Atime."', '".rand(1,3)."', '".rand(100,370)."')", $db_server) or die("Couldn't insert the data").mysql_error();
						echo "<h2>Appointment was made successfully!</h2><br>Would you like to see your <a href='schedule.php' style='text-decoration:none;'>schedule</a>?";
					}
					else
					{
						$Who = mysql_query("SELECT b.ProviderLast FROM APPOINTMENT a JOIN PROVIDER b ON a.ProviderID = b.ProviderID WHERE Indx = '".$Conflict."'",$db_server) or die("Couldn't save the information").mysql_error();
						$Who = mysql_result($Who,0);
						$_SESSION['Index'] = $Conflict;// store the index-to-update for reschedule.php
						echo "<h3>Your patient is already scheduled for this time with Dr. ".$Who.".</h3><br>";
						echo "Would they like to <a href='reschedule.php' style='text-decoration:none'>reschedule</a>?";
					}
				}
				// Goteem
				else
				{
					echo "<h3>You are already booked with this patient.</h3>";
					echo "<a href='make.php' style='text-decoration:none'>Back</a>";
				}

			}
			// A selection was not made (hopefully it's not an error)
			else
			{
				echo "<script>alert('Please make a selection')</script>";
				echo "<script>location.href='make.php'</script>";
			}
		}
	}
	else
	{
		echo "<script>location.href='login.php'</script>";
	}
	?>
</body>
</html>
