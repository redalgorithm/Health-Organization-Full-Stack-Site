<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Sign In</title>
<style type="text/css">
table{

	margin-top: 150px;
	background-image: url("images/marble.png");
	background-position: center;
	background-size: 100%;
	color: white;
	border-radius: 25px;
}

td{
	border:0px;
	padding: 10px;
}
th{
	background-color: rgba(0%, 0%, 0%, .3);
	background-size: 100%;
	background-position: center;
	border-radius: 25px 25px 0px 0px;
}
</style>
</head>

<body style="background-image: url('images/giphy.gif'); background-position: center; background-repeat: no-repeat; background-size: 100%;">

<?php
if($_SESSION['Email'])	// if already logged in or successful redirect
{
	echo "<script>location.href='welcome.php'</script>";
}
?>

	<form action="welcome.php" method="post">
		<table align="center">
			<tr>
				<th colspan="3"><h2 align="center">Sign In</h2></th>
			</tr>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="Email" placeholder="enter email"></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type="password" name="Pass" placeholder="enter password"></td>
			</tr>
			<tr>
				<td align="right" colspan="2"><input type="submit" name="login" value="Login"></td>
			</tr>
	</form>
</body>
</html>
