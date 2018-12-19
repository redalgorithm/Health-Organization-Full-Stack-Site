<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>View Schedule</title>
<style>
table
{
	border-collapse: collapse;
	width: 100%;
}
th, td
{
	padding: 8px;
	text-align: left;
	border-bottom: 1px solid #ddd;
}
</style>
</head>

<body>
<?php
	// Check if logged in, otherwise redirect to login
	if($_SESSION['Email'])
	{
		// Connect to DB
		require_once 'ncrypd/info.php';
		$db_server = mysql_connect($db_hostname, $db_username, $db_password);
		if(!$db_server) die("Connection failed: ").mysql_error();

		// Select Schema
		mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

		// Page Title and Today's Data
		echo "<h1>Schedule</h1><br>";
		echo "<h4>Today is " .date("l"). ", " .date("F d, Y"). "</h4>";

		// Queries (multiple results are possible since
		// client can make several appointments as long as providers are unique)
		$proid = mysql_query("SELECT ProviderID FROM APPOINTMENT WHERE ProviderID = (SELECT ProviderID FROM PROVIDER WHERE Email = '".$_SESSION['Email']."')");
		$patid = mysql_query("SELECT PatientID FROM APPOINTMENT WHERE PatientID = (SELECT PatientID FROM PATIENT WHERE Email = '".$_SESSION['Email']."')");
		$timeexpired = mysql_query("SELECT Indx FROM APPOINTMENT WHERE ApptDate <= '".date('Y-m-d')."' AND ApptTime <= '".date('H:i:s')."'", $db_server) or die("Couldn't save the information").mysql_error();
		if(mysql_result($timeexpired,0))
		{
			mysql_query("DELETE FROM APPOINTMENT WHERE Indx = '".mysql_result($timeexpired,0)."'", $db_server) or die("Couldn't delete expired record").mysql_error();
		}
		// Number of appointments (if provider account match)
		$count = mysql_num_rows($proid);
		$isPatient=False;  //Current Account is Provider?

		// Either wrong account or no appointments were made as provider...
		if(!mysql_result($proid,0) || $count==0)
		{
			// Number of appointments (if patient account match)
			$count = mysql_num_rows($patid);
			$isPatient=True;  // Current Account is Patient?

			// Nothing returned? No appointments exist and account type still undefined (include AcctType in update)
			if(!mysql_result($patid,0) || $count==0)
			{
				die("You are not scheduled for any appointments at this time.").mysql_error();
			}
		}

		// Account Type must be Patient or Provider to get to this point...
		$date1 = mysql_query("SELECT MIN(ApptDate) FROM APPOINTMENT WHERE ApptTime > '".date('H:i:s')."'", $db_server) or die("Couldn't save the information").mysql_error();
		$closestdate = mysql_query("SELECT DATE_FORMAT('".mysql_result($date1,0)."', '%M %D')", $db_server) or die("Couldn't save the information").mysql_error();

		// For Provider Accounts
		if(!$isPatient)
		{
				//$name = "SELECT PatientFirst FROM PATIENT WHERE PatientID = (SELECT PatientID FROM APPOINTMENT WHERE ProviderID = '".mysql_result($proid,0)."' AND ApptDate = '".mysql_result($closestdate,0)."')";
				//$NextPatient = mysql_query($name, $db_server) or die("Couldn't save the information").mysql_error();
			// Gather array of all existing appointment records for this user
			$proappts = "SELECT a.Indx, a.PatientID, a.ApptDate, a.ApptTime, a.Department, a.Room, b.PatientFirst, b.PatientLast FROM APPOINTMENT a JOIN PATIENT b ON a.PatientID = b.PatientID WHERE a.ProviderID = '".mysql_result($proid,0)."'";
			$Proschedule = mysql_query($proappts, $db_server) or die("Couldn't save the information").mysql_error();
			//$PatientInfo = mysql_query("SELECT * FROM PATIENT WHERE PatientID IN (SELECT DISTINCT PatientID FROM APPOINTMENT WHERE ProviderID = '".mysql_result($proid,0)."')", $db_server) or die("Couldn't save the information").mysql_error();
		}
		// For Patient Accounts
		else
		{
				//$name = "SELECT ProviderLast FROM PROVIDER WHERE ProviderID = (SELECT DISTINCT ProviderID FROM APPOINTMENT WHERE PatientID = '".mysql_result($patid,0)."' AND ApptDate = '".mysql_result($closestdate,0)."')";
				//$NextDoctor = mysql_query($name, $db_server) or die("Couldn't save the information").mysql_error();
			// Gather array of all existing appointment records for this user
			$patappts = "SELECT a.Indx, a.PatientID, a.ApptDate, a.ApptTime, a.Department, a.Room, b.ProviderID, b.ProviderFirst, b.ProviderLast FROM APPOINTMENT a JOIN PROVIDER b ON a.ProviderID = b.ProviderID WHERE a.PatientID  = '".mysql_result($patid,0)."'";
			$Patschedule = mysql_query($patappts, $db_server) or die("Couldn't save the information").mysql_error();
			//$ProviderInfo = mysql_query("SELECT * FROM PROVIDER WHERE ProviderID IN (SELECT DISTINCT ProviderID FROM APPOINTMENT WHERE PatientID = '".mysql_result($patid,0)."')", $db_server) or die("Couldn't save the information").mysql_error();
		}

			//$Proschedule = mysql_query($proappts, $db_server) or die("Couldn't save the information").mysql_error();
			//$Patschedule = mysql_query($patappts, $db_server) or die("Couldn't save the information").mysql_error();
			// Number of appointments user is scheduled for
			echo "You are currently scheduled for ".$count." appointment(s) from ".mysql_result($closestdate,0).".<br><br>";
			// Display Table of appointments

			echo "<form action='reschedule.php' method='post'>";
			echo "<table>";

			// For Provider Accounts
			if(!$isPatient)
			{
				$i = 1;
				echo "<tr><th></th><th>MRN</th><th>Patient</th><th>Date</th><th>Time</th><th>Building</th><th>Room</th><th></th></tr>";
				while($row = mysql_fetch_array($Proschedule))
                        	{
					$Indx = $row['Indx'];
					$ProviderID = $row['ProviderID']; 	// place near profile info for user reference
                        		$PatientID = $row['PatientID'];
                            		$PatientFirst = $row['PatientFirst'];	//should be in results
					$PatientLast = $row['PatientLast'];  	//should be in results
                                	$ApptDate = $row['ApptDate'];
                                	$ApptTime = $row['ApptTime'];
                                	$Department = $row['Department'];
                                	$Room = $row['Room'];

					$DTI = "$ApptDate $ApptTime $Indx";
					// Insert Rows
					echo "
						<tr>
						<input type='hidden' id='ninja' name='ninja' value='".$DTI."'>
						<td>$i</td>
						<td>$PatientID</td>
						<td>$PatientFirst $PatientLast</td>
						<td>$ApptDate</td>
						<td>$ApptTime</td>
						<td>$Department</td>
						<td>$Room</td>
						<td><button name='Cancel' onclick=' ".$_SESSION['Indx']=$Indx." 'title='Cancel appointment'><a href='cancel.php' style='text-decoration:none'>Cancel</a></button> <input type='submit' name='Reschedule' value='Reschedule' title='Reschedule appointment'></td>
						</tr>";
					$i++;
                         	}
				echo "</table>";
			}
			// For Patient Accounts
			else
			{
				$i = 1;
				echo "<tr><th></th><th>Date</th><th>Time</th><th>Building</th><th>Room</th><th>Health Care Provider</th><th></th></tr>";
				while($row = mysql_fetch_array($Patschedule))
                       		{
					$Indx = $row['Indx'];
					$PatientID = $row['PatientID'];			// near profile for reference
					$ApptDate = $row['ApptDate'];
                       	        	$ApptTime = $row['ApptTime'];
                                	$Department = $row['Department'];
                                	$Room = $row['Room'];
					$ProviderID = $row['ProviderID'];
					$ProviderFirst = $row['ProviderFirst'];
					$ProviderLast = $row['ProviderLast'];

					$DTI = "$ApptDate $ApptTime $Indx";
					echo "	<tr>
						<input type='hidden' id='ninja' name='ninja' value='".$DTI."'>
						<td>$i</td>
						<td>$ApptDate</td>
						<td>$ApptTime</td>
						<td>$Department</td>
						<td>$Room</td>
						<td>Dr. $ProviderFirst $ProviderLast</td>
						<td><button name='Cancel' onclick=' ".$_SESSION['Indx']=$Indx." 'title='Cancel appointment'><a href='cancel.php' style='text-decoration:none'>Cancel</a></button> <input type='submit' name='Reschedule' value='Reschedule' title='Reschedule appointment'></td>
						</tr>";
					$i++;
				}
				echo "</table>";
			}

		echo "</form>";
		mysql_close($db_server);
	}
	else
	{
		echo "<script>location.href='login.php'</script>";
	}
	

?>

</body>
</html>
