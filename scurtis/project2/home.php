<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home Page</title>
</head>

<body>
<?php
if($_SESSION['Email'])
{
	echo "<h1>Welcome " .$_SESSION['Email']. "</h1>";
	echo '<br><a href="make.php"><input type="button" value="Make Appointment" name="Appointment" title="Make an Appointment"></a>';
	echo '<a href="schedule.php"><input type="button" value="View Schedule" name="Schedule" title="View your Current Schedule"></a>';
//	echo '<a href="updatereschedule..."><input type="button" value="Reschedule" name="Reschedule" title="Reschedule an Appointment"></a>';
	echo '<a href="logout.php"><input type="button" value="Logout" name="logout"></a>';
}
else
{
	echo "<script>location.href='login.php'</script>";
}
?>
</body>
</html>
