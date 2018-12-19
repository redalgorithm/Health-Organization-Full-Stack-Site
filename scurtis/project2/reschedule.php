<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Reschedule Appointment</title>
<style>
button
{
	padding: 20px;
}
</style>
</head>
<!---->
<body>
	<?php
	// User signed in
	if($_SESSION['Email'])
	{
		// Connect to DB
		require_once 'ncrypd/info.php';
		$db_server = mysql_connect($db_hostname, $db_username, $db_password);

		// Select Schema
		mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

		// Check and delete expired appointments
		$Expired = mysql_query("SELECT Indx FROM APPOINTMENT WHERE ApptDate <= '".date('Y-m-d')."' AND ApptTime <= '".date('H:i:s')."'", $db_server) or die("Couldn't save the information").mysql_error();
		$Expired = mysql_result($Expired,0);
		if($Expired)
		{
			mysql_query("DELETE FROM APPOINTMENT WHERE Indx = '".$Expired."'", $db_server) or die("Couldn't delete expired record").mysql_error();
		}

		if(!isset($_SESSION['ActType']))
		{
			$Sessionid = mysql_query("SELECT PatientID FROM PATIENT WHERE Email = '".$_SESSION['Email']."'",$db_server) or die("Couldn't save the information").mysql_error();
			if(!mysql_result($Sessionid,0))
			{
				$_SESSION['ActType'] = 'Provider';
			}
			else
			{
				$_SESSION['ActType'] = 'Patient';
			}
		}
		// Get Session ID
                $ActType = strtoupper($_SESSION['ActType']);
                $Sessionid = mysql_query("SELECT ".$_SESSION['ActType']."ID FROM ".$ActType." WHERE Email = '".$_SESSION['Email']."'", $db_server) or die("Couldn't save the information").mysql_error();
                $Sessionid = mysql_result($Sessionid,0);


		// Check and delete expired (returns expiration 10-30 min after actual expiration time)
//		$Expired = mysql_query("SELECT Indx FROM APPOINTMENT WHERE ApptDate <= '".date('Y-m-d')."' AND ApptTime <= '".date('H:i:s')."'", $db_server) or die("Couldn't save the information").mysql_error();
//		$Expired = mysql_result($Expired,0);
//		if($Expired)
//		{
//			mysql_query("DELETE FROM APPOINTMENT WHERE Indx = '".$Expired."'", $db_server) or die("Couldn't delete expired record").mysql_error();
//		}

		// Check validity of click
		if((isset($_POST['Reschedule']) && isset($_POST['ninja'])) || isset($_SESSION['Index']))
		{
			if(isset($_POST['Reschedule']))
			{
				// Segregate hidden input value into date, time, and index (APPOINTMENT)
				list($Date, $Time, $Index) = split(" ", $_POST['ninja']);
				$UpdateNDX = $Index;			// for DX, Developer Experience™
			}
			else
			{
				$UpdateNDX = $_SESSION['Index'];	// for DX, Developer Experience™
				$dt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE Indx = '".$UpdateNDX."'",$db_server) or die("Couldn't save the information").mysql_error();
				$dt = mysql_fetch_array($dt);
				$Date = $dt['ApptDate'];
				$Time = $dt['ApptTime'];
			}
			
			$_SESSION['UpdateNDX'] = $UpdateNDX;		// for update.php
			// Welcome message
			$Date = new DateTime($Date);
			$Time = new DateTime($Time);

			if($_SESSION['ActType']=='Patient')
			{
				echo "<h2>Reschedule my ".$Date->format('l')." appointment.</h2>";
			}
			else
			{
				$Who = mysql_query("SELECT b.PatientFirst FROM APPOINTMENT a JOIN PATIENT b ON a.PatientID = b.PatientID WHERE Indx = '".$UpdateNDX."'",$db_server) or die("Couldn't save the information").mysql_error();
				$Pid = mysql_query("SELECT PatientID FROM APPOINTMENT WHERE Indx = '".$UpdateNDX."'",$db_server) or die("Couldn't save the information").mysql_error();
				$Who = mysql_result($Who,0);
				$Pid = mysql_result($Pid,0);
				echo "<h2>Reschedule ".$Time->format('h:i A')." appointment</h2>";
				echo "<b style='font-size:21px'>with ".$Who."</b>";
				echo "<p style='font-size:15px'>MRN: ".$Pid."</p><br>";
			}

			// PATIENT ACCOUNTS (RESCHEDULE)
			if($_SESSION['ActType']=='Patient')
			{
				// Gather Provider info
				$Who = mysql_query("SELECT b.ProviderLast FROM APPOINTMENT a JOIN PROVIDER b ON a.ProviderID = b.ProviderID WHERE Indx = '".$UpdateNDX."'",$db_server) or die("Couldn't save the information").mysql_error();
				//$Who = mysql_result($Who,0);
				$Pid = mysql_query("SELECT ProviderID FROM APPOINTMENT WHERE Indx = '".$UpdateNDX."'",$db_server) or die("Couldn't save the information").mysql_error();
				$Who = mysql_result($Who,0);
				$Pid = mysql_result($Pid,0);
			
				// Provider info (for reference)
				echo "Health Care Provider: Dr. ".$Who."<br><br>";
				
				// Gather busy schedule (provider's)
				$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Pid."' GROUP BY ApptTime",$db_server) or die("Couldn't save the information").mysql_error();
				
				// Begin at first available time from current (1 hour headroom)
				$curr = date('Y-m-d');
				$curr = (string)$curr;
				$currt = date('H');
				$currt = (string)$currt;
				$currt++;
				$objt = "$curr $currt:00:00";
				$begint = new DateTime($objt);

				// Provider has no appointments (free -- this should never execute)
				if(!mysql_result($Appt,0))
				{
					echo "<script>alert('A problem occurred: You were not found in the schedule')</script>";
					echo "<script>location.href='schedule.php'</script>";
					//echo "<script>alert('A problem occurred: You were not found in the schedule')</script>";
				}
				// Provider has appointments (busy -- more likely to execute)
				else
				{
					// necessary redundancy for now
					$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Pid."' GROUP BY ApptTime",$db_server) or die("Couldn't save the information").mysql_error();

					// Store unavailable times in more liquidable format
					$j=0;
					$BusyDate = array();
					$BusyTime = array();
					while($row = mysql_fetch_array($Appt))
					{
						$BusyDate[$j] = $row['ApptDate'];
						$BusyTime[$j] = $row['ApptTime'];
						$j++;
					}

					// For the post variable (until a better way is discovered)
					echo "<form action='update.php' name='update' method='post'>";

					// Add available dates (display 10 nearest current)
					$k=0;
					while($k<10)
					{
						// Consider open/close policy (Primary Concern, logically)
						while(($begint->format('H:i:s') < '08:00:00') || ($begint->format('H:i:s') > '20:00:00'))
						{
							$begint->add(new DateInterval(PT60M));
						}

						// Check if date is available out of j unavailable
						$i=0;
						while($i < $j)
						{
							if($begint->format('Y-m-d H:i:s') == "$BusyDate[$i] $BusyTime[$i]")
							{
								//echo "BUSY!<br>";
								$begint->add(new DateInterval(PT60M));	// continue to next date w/o printing
								$i--;					// step back to ensure full check of next date
							}
							$i++;
						}
					
						//echo "Available";
						
						echo "<button onclick='document.getElementById('update').submit()' name='thisone' value='".$begint->format('Y-m-d H:i:s')."'>".$begint->format('l j \of F Y')." ".$begint->format('h:i A')."</button><br>";
						$begint->add(new DateInterval(PT60M));

						$k++;
					}
					echo "</form>";
				}
			}
			// PROVIDER ACCOUNTS (RESCHEDULE)
			else
			{

				// Gather busy schedule (provider's)
				$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Sessionid."' GROUP BY ApptTime",$db_server) or die("Couldn't save the information").mysql_error();

				// Begin at first available time from current (1 hour headroom)
				$curr = date('Y-m-d');
				$curr = (string)$curr;
				$currt = date('H');
				$currt = (string)$currt;
				$currt++;
				$objt = "$curr $currt:00:00";
				$begint = new DateTime($objt);

				// Provider has no appointments (free -- this should never execute)
				if(!mysql_result($Appt,0))
				{
					echo "<script>alert('A problem occurred: ".$Who." was not found in the schedule')</script>";
					echo "<script>location.href='schedule.php'</script>";
					//echo "<script>alert('A problem occurred: You were not found in the schedule')</script>";
				}
				// Provider has appointments (busy -- more likely to execute)
				else
				{
					// necessary redundancy for now
					$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Sessionid."' GROUP BY ApptTime",$db_server) or die("Couldn't save the information").mysql_error();

					// Store unavailable times in more liquidable format
					$j=0;
					$BusyDate = array();
					$BusyTime = array();
					while($row = mysql_fetch_array($Appt))
					{
						$BusyDate[$j] = $row['ApptDate'];
						$BusyTime[$j] = $row['ApptTime'];
						$j++;
					}

					// For the post variable (until a better way is discovered)
					echo "<form action='update.php' name='update' method='post'>";

					// Add available dates (display 10 nearest current)
					$k=0;
					while($k<10)
					{
						// Consider open/close policy (Primary Concern, logically)
						while(($begint->format('H:i:s') < '08:00:00') || ($begint->format('H:i:s') > '20:00:00'))
						{
							$begint->add(new DateInterval(PT60M));
						}

						// Check if date is available out of j unavailable
						$i=0;
						while($i < $j)
						{
							if($begint->format('Y-m-d H:i:s') == "$BusyDate[$i] $BusyTime[$i]")
							{
								//echo "BUSY!<br>";
								$begint->add(new DateInterval(PT60M));// continue to next date w/o printing
								$i--;// step back to ensure full check of next date
							}
							$i++;
						}

						//echo "Available";

						echo "<button onclick='document.getElementById('update').submit()' name='thisone' value='".$begint->format('Y-m-d H:i:s')."'>".$begint->format('l j \of F Y')." ".$begint->format('h:i A')."</button><br>";
						$begint->add(new DateInterval(PT60M));

						$k++;
					}
					echo "</form>";
				}
			}
		}
		// Big obvi on error
		else
		{
			echo "<h1>Something is obviously wrong</h1>I just don't know what.";
		}
	}
	// User not signed in
	else
	{
		echo "<script>location.href='login.php'</script>";
	}
	?>
</body>
</html>
