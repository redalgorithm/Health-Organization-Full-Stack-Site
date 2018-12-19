<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Welcome</title>
</head>

<body>
<?php
if($_SESSION['Email'])	// If Current User or Successful Login, redirect
{
	// redirect to welcome page
	echo "<script>location.href='home.php'</script>";
}
else
{	// Connect to DB
	require_once 'ncrypd/info.php';
        $db_server = mysql_connect($db_hostname, $db_username, $db_password);

        if(!$db_server) die("Connection failed: ").mysql_error();

        echo "Connection Successful";

        // Select Schema
        mysql_select_db($db_database) or die("Unable to select database: ").mysql_error();

	// Check User Credentials
	if(isset($_POST['Email']) && isset($_POST['Pass']))
	{
		$Email = mysql_real_escape_string($_POST['Email']);
		$Pass = mysql_real_escape_string($_POST['Pass']);

		$sqlid = mysql_query("SELECT PatientID FROM PATIENT WHERE Email = '".$Email."' AND Pass = '".$Pass."'", $db_server) or die("Couldn't save the information").mysql_error();

		if(!mysql_result($sqlid,0))
		{
			$sqlid = mysql_query("SELECT ProviderID FROM PROVIDER WHERE Email = '".$Email."' AND Pass = '".$Pass."'",$db_server) or die("Couldn't save the information").mysql_error();

			if(!mysql_result($sqlid,0))
			{
				session_destroy(); // hehehee >:) you won't glitch now
				echo "<script>alert('username or password incorrect!')</script>";
                		echo "<script>location.href='login.php'</script>";
			}
		}
			$_SESSION['Email'] = $Email;
                        echo "<script>location.href='welcome.php'</script>";
	}
	else
	{
		echo "Did not login properly";
	}
	mysql_close($db_server);
}
?>

</body>
</html>
