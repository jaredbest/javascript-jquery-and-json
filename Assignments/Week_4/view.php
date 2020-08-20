<?php

require_once "pdo.php";
require_once "util.php";

session_start();

$firstName = "";
$lastName = "";
$email = "";
$headline = "";
$summary = "";

if(!isset($_GET["profile_id"]))
{
	$_SESSION["error"] = "Missing profile_id";
	header("Location: index.php");
	return;
}
else
{
	if($_GET["profile_id"] == "")
	{
		$_SESSION["error"] = "Could not load profile";
		header("Location: index.php");
		return;
	}

	$sql = "SELECT first_name, last_name, email, headline, summary FROM profile WHERE profile_id = " . $_GET["profile_id"];
	$stmt = $pdo -> query($sql);

	if($stmt -> rowCount() == 0)
	{
		$_SESSION["error"] = "Could not load profile";
		header("Location: index.php");
		return;
	}
	else
	{
		$row = $stmt -> fetch(PDO::FETCH_ASSOC);
		$firstName = htmlentities($row["first_name"]);
		$lastName = htmlentities($row["last_name"]);
		$email = htmlentities($row["email"]);
		$headline = htmlentities($row["headline"]);
		$summary = htmlentities($row["summary"]);
	}
}

// Load up the position rows
$positions = loadEduOrPos($pdo, $_GET["profile_id"], "position");
// Load up the education rows
$educations = loadEduOrPos($pdo, $_GET["profile_id"], "education");

?>

<!DOCTYPE html>

<html lang = "en">

	<head>
		<meta charset = "utf-8">
		<title>Jared Best | View Page</title>
		<?php require_once "head.php" ?>
	</head>

	<body>
		<div class = "container">
			<h1>Profile information</h1>
			<p>
				First Name : <?php echo($firstName . "\n"); ?>
			</p>
			<p>
				Last Name : <?php echo($lastName . "\n"); ?>
			</p>
			<p>
				Email : <?php echo($email . "\n"); ?>
			</p>
			<p>
				Headline : <?php echo("<br>" . $headline . "\n"); ?>
			</p>
			<p>
				Summary : <?php echo("<br>" . $summary . "\n"); ?>
			</p>
			<p>
				<?php
					if(count($educations) > 0)
					{
						echo"<p> Education </p><ul>\n";

						foreach ($educations as $row) 
						{
							$year = htmlentities($row["year"]);
							$institution_id = $row["institution_id"];

							$sql = "SELECT name FROM institution WHERE institution_id = $institution_id";
							$stmt = $pdo -> query($sql);
							$rowInst = $stmt -> fetch(PDO::FETCH_ASSOC);
							$schoolName = htmlentities($rowInst["name"]);
							echo ("<li>" . $year . " : " . $schoolName . "</li>\n");
						}

						echo "</ul>\n";
					}
				?>
			</p>
			<p>
				<?php
					if(count($positions) > 0)
					{
						echo"<p> Position </p><ul>\n";

						foreach ($positions as $row) 
						{
							$year = htmlentities($row["year"]);
							$descr = htmlentities($row["description"]);
							echo ("<li>" . $year . " : " . $descr . "</li>\n");
						}

						echo "</ul>\n";
					}
				?>
			</p>
			<a href="index.php">Done</a>
		</div>
	</body>

</html>








