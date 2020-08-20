<?php

require_once "pdo.php";
require_once "util.php";
session_start();

if(isset($_POST["cancel"]))
{
	header("Location: index.php");
	return;
}

$alt = 'XyZzy12*_';

// Check to see if we have some POST data, if we do store it in SESSION
if(isset($_POST["email"]) && isset($_POST["pass"]))
{
	$_SESSION["email"] = $_POST["email"];
	$_SESSION["pass"] = $_POST["pass"];

	header("Location: login.php");
	return;
}

// Check to see if we have some new data in SESSION, if we do process it
if(isset($_SESSION["email"]) && isset($_SESSION["pass"]))
{
	$email = $_SESSION["email"];
	$password = $_SESSION["pass"];
	unset($_SESSION["email"]);
	unset($_SESSION["pass"]);

	$check = hash("md5", $alt.$password);

	$sql = "SELECT user_id, name FROM users WHERE email = :em AND password = :pw";
	$stmt = $pdo -> prepare($sql);
	$stmt -> execute(array(":em" => $email, ":pw" => $check));
	$row = $stmt -> fetch(PDO::FETCH_ASSOC);

	if($row === false)
	{
		$_SESSION["error"] = "Incorrect password";
		header("Location: login.php");
		return;
	}
	else
	{
		$_SESSION["user_id"] = $row["user_id"];
		$_SESSION["name"] = $row["name"];
		header("Location: index.php");
		return;
	}
}

?>

<!DOCTYPE html>

<html lang = "en">

	<head>
		<meta charset = "utf-8">
		<title>Jared Best | Login Page</title>
		<?php require_once "head.php" ?>
	</head>

	<body>
		<div class = "container">
			<h1>Please Log In</h1>

			<?php flashmessages(); ?>

			<form method = "post">
				<label for = "email">Email</label>
				<input type="text" name="email" id="email"><br>
				<label for = "id_1723">Password</label>
				<input type="text" name="pass" id="id_1723"><br>
				<input type="submit" value="Log In" onclick="return doValidate();">
				<input type="submit" name="cancel" value="Cancel">
			</form>

			<script type="text/javascript">
				function doValidate()
				{
					console.log("Validating...");
					try{
						addr = document.getElementById("email").value;
						pw = document.getElementById("id_1723").value;
						console.log("Validating addr = " + addr + " pw = " + pw);
						if(addr == null || addr == "" || pw == null || pw == "")
						{
							alert("Both fields must be filled out");
							return false;
						}
						if(addr.indexOf("@") == -1)
						{
							alert("Invalid email address");
							return false;
						}
						return true;
					}
					catch(e)
					{
						return false;
					}

					return false;
				}
			</script>
		</div>
	</body>

</html>