<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
</head>
<!---->
<body>
	<?php
		if($_SESSION['Email'])
		{
			// Connect to DB
			require_once 'ncrypd/info.php';
			$db_server = mysql_connect($db_hostname, $db_username, $db_password);
			if(!$db_server) die("Connection failed: ").mysql_error();

			// Select Schema
			mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

			// need particular record clicked
			// gather post data and delete appointment
			if(isset($_SESSION['Indx'])) // Provider Account
			{
				list($id,$trash1,$trash2,$trash3) = split(" ", $_SESSION['Indx']); //as long as it works -_-
				mysql_query("DELETE FROM APPOINTMENT WHERE Indx = '".$id."'", $db_server) or die("Deletion failed").mysql_error();
				echo "<h3>Appointment cancelled successfully.</h3><br> Return to <a href='schedule.php' style='text-decoration:none;'>schedule</a>?";
			}
			else
			{
				echo "a problem occurred";
			}
			// patient can only have one appt per provider
			// provider can only have one appt per patient
		}
		else
		{
			echo "<script>location.href='login.php'</script>";
		}
	?>
</body>
</html>
