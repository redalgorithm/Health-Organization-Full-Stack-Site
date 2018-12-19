<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head></head>

<body>
	<?php
	// User Logged In
	if($_SESSION['Email'])
	{
		// Connect to database
		require_once 'ncrypd/info.php';
		$db_server = mysql_connect($db_hostname, $db_username, $db_password);
		if(!$db_server) die("Connection failed: ").mysql_error();

		// Select Schema
		mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

		// Get Session ID
                $ActType = strtoupper($_SESSION['ActType']);
                $Sessionid = mysql_query("SELECT ".$_SESSION['ActType']."ID FROM ".$ActType." WHERE Email = '".$_SESSION['Email']."'", $db_server) or die("Couldn't save the information").mysql_error();
                $Sessionid = mysql_result($Sessionid,0);


		// Check and delete expired (returns expiration 10-30 min after actual expiration time)
		$Expired = mysql_query("SELECT Indx FROM APPOINTMENT WHERE ApptDate <= '".date('Y-m-d')."' AND ApptTime <= '".date('H:i:s')."'", $db_server) or die("Couldn't save the information").mysql_error();
		$Expired = mysql_result($Expired,0);
		if($Expired)
		{
			mysql_query("DELETE FROM APPOINTMENT WHERE Indx = '".$Expired."'", $db_server) or die("Couldn't delete expired record").mysql_error();
		}

		// Determine account type and assign proper selection
		if($_SESSION['ActType']=='Patient')
		{
			$Select = mysql_real_escape_string($_POST['SelectDoc']);
		}else
		{
			$Select = mysql_real_escape_string($_POST['SelectPat']);
		}

		// Check if selection correctly posted (from make.php)
		if(isset($Select))
		{
			// display header if selection was made
			if($Select!='na')
			{
				echo "<h3>Availabilities:</h3><br><form name='Make' action='process.php' method='post' target='_top'>";
				$_SESSION['SelectDoc'] = $_POST['SelectDoc']; 	// will need this for later
				$_SESSION['SelectPat'] = $_POST['SelectPat']; 	// will need this for later
			}

			// FOR PATIENT ACCOUNTS
			if($_SESSION['ActType']=='Patient')
			{
				// Selected provider by ID (lookup busy schedule)
				$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Select."' GROUP BY ApptTime", $db_server) or die("Couldn't save the information").mysql_error();

				// Begin at first available time from current (give 1 hour headroom)
				$curr = date('Y-m-d');
				$curr = (string)$curr;
				$currt = date('H');
				$currt = (string)$currt;
				$currt++;
				$objt = "$curr $currt:00:00";
				$begint = new DateTime($objt);

				// Provider has no appointments (free!)
				if(!mysql_result($Appt,0) && $Select != 'na')
				{
					// display 10 results at a time (*update this to results by page button)
					$i = 0;
					while($i < 10)
					{
						// consider open/close policy
						while(($begint->format('H:i:s') < '08:00:00') || ($begint->format('H:i:s') > '20:00:00'))
						{
							$begint->add(new DateInterval(PT60M));
						}

						// list available times
						echo "<input type='radio' name='SelectDate' value='".$begint->format('Y-m-d H:i:s')."'>".$begint->format('l j \of F Y')." ".$begint->format('h:i A')."<br>";
						$begint->add(new DateInterval(PT60M));
						$i++;
					}
				}
				// Provider has appointments (busy)
				else
				{
					// for valid selections only (otherwise leave frame empty for --select-- placeholder)
					if($Select!='na')
					{
						// query data must have gotten lost somehow (returns error if not repeated)
						$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Select."' GROUP BY ApptTime", $db_server) or die("Couldn't save the information").mysql_error();

						// reset begint to closest date/time to next open/close
						$begint = new DateTime($objt);

						// store unavailable times in more liquidable variables
						$j = 0;
						$BusyDate = array();
						$BusyTime = array();
						while($row = mysql_fetch_array($Appt))
						{
							$BusyDate[$j] = $row['ApptDate'];
							$BusyTime[$j] = $row['ApptTime'];
							$j++;
						}

						// Add available dates (not including policy)
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
									$begint->add(new DateInterval(PT60M)); //continue to next date w/o printing
									$i--;				       // step back to ensure full check of next date
								}
								$i++;
							}

							// echo "Available";
							echo "<input type='radio' name='SelectDate' value='".$begint->format('Y-m-d H:i:s')."'>".$begint->format('l j \of F Y')." ".$begint->format('h:i A')."<br>";
							$begint->add(new DateInterval(PT60M));

							$k++;
						}
			    		}
			    	}
			}
			// FOR PROVIDER ACCOUNTS
			else if($_SESSION['ActType']=='Provider')
			{
				// Selected patient by ID (lookup provider's busy schedule)
				$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Sessionid."' GROUP BY ApptTime", $db_server) or die("Couldn't save the information").mysql_error();

				// Begin at first available time from current (give 1 hour headroom)
				$curr = date('Y-m-d');
				$curr = (string)$curr;
				$currt = date('H');
				$currt = (string)$currt;
				$currt++;
				$objt = "$curr $currt:00:00";
				$begint = new DateTime($objt);

				// Provider has no appointments (free!)
				if(!mysql_result($Appt,0) && $Select != 'na')
				{
					// display 10 results at a time (*update this to results by page button)
					$i = 0;
					while($i < 10)
					{
						// consider open/close policy
						while(($begint->format('H:i:s') < '08:00:00') || ($begint->format('H:i:s') > '20:00:00'))
						{
							$begint->add(new DateInterval(PT60M));
						}

						// list available times
						echo "<input type='radio' name='SelectDate' value='".$begint->format('Y-m-d H:i:s')."'>".$begint->format('l j \of F Y')." ".$begint->format('h:i A')."<br>";
						$begint->add(new DateInterval(PT60M));
						$i++;
					}
				}
				// Provider has appointments (busy)
				else
				{
					// for valid selections only (otherwise leave frame empty for --select-- placeholder)
					if($Select!='na')
					{
						// repeat query in case data lost
						$Appt = mysql_query("SELECT ApptDate, ApptTime FROM APPOINTMENT WHERE ProviderID = '".$Sessionid."' GROUP BY ApptTime", $db_server) or die("Couldn't save the information").mysql_error();

				       		// reset begint to closest date/time to next open/close
                                        	$begint = new DateTime($objt);

						// store unavailable times in more liquidable variables
                                        	$j = 0;
						$BusyDate = array();
						$BusyTime = array();
                                        	while($row = mysql_fetch_array($Appt))
                                        	{
                                        	        $BusyDate[$j] = $row['ApptDate'];
							$BusyTime[$j] = $row['ApptTime'];
							$j++;
						}

						// Add available dates
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
									//echo "BUSY!";
									$begint->add(new DateInterval(PT60M));	// continue to next date w/o printing
									$i--;					// step back to ensure full check of next date
								}
								$i++;
							}

							//echo "Available";
							echo "<input type='radio' name='SelectDate' value='".$begint->format('Y-m-d H:i:s')."'>".$begint->format('l j \of F Y')." ".$begint->format('h:i A')."<br>";
							$begint->add(new DateInterval(PT60M));

							$k++;
						}
					}
				}
			}
			if($Select != 'na')
			{
				// SUBMIT button
				echo "<br><input type='submit' name='Create' value='Make Appointment'></form>";
			}
		}
		else
		{
			echo "<h1>It's Not You, It's Us (me)</h1>";
			echo "I'll get to it eventually";
		}
	}
	else
	{
		echo "<script>location.href='login.php'</script>";
	}
	?>
</body>
</html>
