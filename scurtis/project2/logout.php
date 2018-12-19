<?php
session_start();
?>

<!DOCTYPE html>
<html>

<body>
<?php
if(isset($_SESSION['Email']))
{
	session_destroy();
	echo '<script>location.href="login.php"</script>';
}
else
{
	echo '<script>location.href="login.php"</script>';
}
?>
</body>
</html>
