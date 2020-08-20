<?php

require_once "pdo.php";
require_once "util.php";

session_start();

if(!isset($_SESSION["name"]))
	die("ACCESS DENIED");

if(isset($_POST["cancel"]))
{
	header("Location: index.php");
	return;
}

if(isset($_POST["profile_id"])  && isset($_POST["delete"]))
{
	
	$_SESSION["delete"] = $_POST["delete"];
	$_SESSION["profile_id"] = $_POST["profile_id"];
	header("Location: delete.php?profile_id=" . $_POST["profile_id"]);
	return;
}

if(isset($_SESSION["delete"]) && isset($_SESSION["profile_id"]))
{

	$profileID = $_SESSION["profile_id"];
	unset($_SESSION["delete"]);
	unset($_SESSION["profile_id"]);


	$sql = "DELETE FROM profile WHERE profile_id = :profID";
	$stmt = $pdo -> prepare($sql);
	$stmt -> execute(array(":profID" => $profileID));
	$_SESSION["success"] = "Profile deleted";
	header("Location: index.php");
	return;
}

if(!isset($_GET["profile_id"]))
{
	$_SESSION["error"] = "Missing profile_id";
	header("Location: index.php");
	return;
}

if($_GET["profile_id"] == "")
{
	$_SESSION["error"] = "Could not load profile";
	header("Location: index.php");
	return;
}

$sql = "SELECT first_name, last_name FROM profile WHERE profile_id = " . $_GET["profile_id"];
$stmt = $pdo -> query($sql);

if($stmt -> rowCount() == 0)
{
	$_SESSION["error"] = "Could not load profile";
	header("Location: index.php");
	return;
}

$row = $stmt -> fetch(PDO::FETCH_ASSOC);

$fn = $row["first_name"];
$ln = $row["last_name"];

?>

<!DOCTYPE html>

<html lang = "en">

	<head>
		<meta charset = "utf-8">
		<title>Jared Best | Delete Page</title>
		<?php require_once "head.php" ?>
	</head>

	<body>
		<div class = "container">
			<h1>Deleting Profile</h1>
			<form method="post">
				<p>
					First Name : <?php echo($fn) ?>
				</p>
				<p>
					Last Name : <?php echo($ln) ?>
				</p>
				<input type="hidden" name="profile_id" value="<?php echo($_GET['profile_id']) ?>">
				<input type="submit" name="delete" value = "Delete">
				<input type="submit" name="cancel" value = "Cancel">
			</form>
		</div>
	</body>

</html>