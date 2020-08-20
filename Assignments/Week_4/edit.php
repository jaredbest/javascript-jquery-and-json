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

if(isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["email"]) && isset($_POST["headline"]) 
	&& isset($_POST["summary"]) && isset($_POST["profile_id"]))
{
	$_SESSION["first_name"] = $_POST["first_name"];
	$_SESSION["last_name"] = $_POST["last_name"];
	$_SESSION["email"] = $_POST["email"];
	$_SESSION["headline"] = $_POST["headline"];
	$_SESSION["summary"] = $_POST["summary"];

	initSession("year", "desc");
	initSession("edu_year", "edu_school");

	header("Location: edit.php?profile_id=" . $_POST["profile_id"]);
	return;
}

if(isset($_SESSION["first_name"]) && isset($_SESSION["last_name"]) && isset($_SESSION["email"]) && isset($_SESSION["headline"]) 
        && isset($_SESSION["summary"]))
{
 	$firstName = $_SESSION["first_name"];
    $lastName = $_SESSION["last_name"];
    $email = $_SESSION["email"];
    $headline = $_SESSION["headline"];
    $summary = $_SESSION["summary"];
    unset($_SESSION["first_name"]);
    unset($_SESSION["last_name"]);
    unset($_SESSION["email"]);
    unset($_SESSION["headline"]);
    unset($_SESSION["summary"]);
	$profileId = $_GET["profile_id"];

	if(validateProfile($firstName, $lastName, $email, $headline, $summary) === true 
		&&  validateFields("edu_year", "edu_school", "Education") === true && validateFields("year", "desc", "Position") === true)
	{
		$sql = "UPDATE profile SET first_name = :fn, last_name = :ln, email = :em, headline = :he, summary = :sum WHERE profile_id = :profile_id";
		$stmt = $pdo -> prepare($sql);
		$stmt -> execute(array(":fn" => $firstName, ":ln" => $lastName, ":em" => $email, ":he" => $headline, ":sum" => $summary, ":profile_id" => $profileId));
		
		// Clear out the old position
		$sql = "DELETE FROM position WHERE profile_id = :pid";
		$stmt = $pdo -> prepare($sql);
		$stmt -> execute(array(":pid" => $profileId));

		// Insert the position entries
		insertPosition($pdo, $profileId);
		
		// Clear out the old education
		$sql = "DELETE FROM education WHERE profile_id = :pid";
		$stmt = $pdo -> prepare($sql);
		$stmt -> execute(array("pid" => $profileId));

		// Insert the education entries
		insertEducation($pdo, $profileId);

		$_SESSION["success"] = "Profile updated";

		header("Location: index.php");
		return;
	}
	
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

$sql = "SELECT first_name, last_name, email, headline, summary FROM profile WHERE profile_id = " . $_GET["profile_id"];
$stmt = $pdo -> query($sql);

if($stmt -> rowCount() == 0)
{
	$_SESSION["error"] = "Could not load profile";
	header("Location: index.php");
	return;
}


$row = $stmt -> fetch(PDO::FETCH_ASSOC);

$fn = htmlentities($row["first_name"]);
$ln = htmlentities($row["last_name"]);
$em = htmlentities($row["email"]);
$he = htmlentities($row["headline"]);
$sum = htmlentities($row["summary"]);

// Load up the position rows
$positions = loadEduOrPos($pdo, $_GET["profile_id"], "position");
// Load up the education rows
$educations = loadEduOrPos($pdo, $_GET["profile_id"], "education");

?>

<!DOCTYPE html>

<html lang = "en">

	<head>
		<meta charset = "utf-8">
		<title>Jared Best | Edit Page</title>
		<?php require_once "head.php" ?>
	</head>

	<body>
		<div class = "container">
			<h1>Editing Profile for <?php echo(htmlentities($_SESSION["name"])); ?></h1>
			<?php flashmessages(); ?>
			<form method="post">
				<p>
					First Name :
					<input type="text" name="first_name" size = "60" value="<?php echo($fn) ?>">
				</p>
				<p>
					Last Name :
					<input type="text" name="last_name" size = "60" value="<?php echo($ln) ?>">
				</p>
				<p>
					Email :
					<input type="text" name="email" size = "30" value="<?php echo($em) ?>">
				</p>
				<p>
					Headline :
					<input type="text" name="headline" size = "80" value="<?php echo($he) ?>">
				</p>
				<p>
					Summary :<br>
					<textarea name="summary" rows = "8" cols = "80"><?php echo($sum) ?></textarea>
				</p>
				<p>
					Education:
					<input type = "submit" id = "addEdu" value="+">
					<div id="edu_fields">
						<?php
							if(count($educations) > 0)
							{
								foreach ($educations as $row) {
									$rank = $row["rank"];
									$year = htmlentities($row["year"]);
									$institution_id = $row["institution_id"];

									$sql = "SELECT name FROM institution WHERE institution_id = $institution_id";
									$stmt = $pdo -> query($sql);
									$rowInst = $stmt -> fetch(PDO::FETCH_ASSOC);
									$schoolName = htmlentities($rowInst["name"]);

									echo('<div id="edu' . $rank . '">' . "\n");
									echo('<p> Year : <input type = "text" name="edu_year' . $rank . '" value = "' . $year . '">' . "\n");
									echo('<input type = "button" value = "-" onclick="$(\'#edu' . $rank . '\').remove(); eduCount--;
										  return false;"></p>' . "\n");
									echo('<p>School: <input type = "text" size="80" name="edu_school' . $rank . '" class="school" 
										value ="' . $schoolName . '"' . "></p></div>\n");
								}
							}
						?>
					</div>
				</p>
				<p>
					Position:
					<input type = "submit" id = "addPos" value="+">
					<div id="position_fields">
						<?php
							if(count($positions) > 0)
							{
								foreach ($positions as $row) {
									$rank = $row["rank"];
									$year = htmlentities($row["year"]);
									$descr = htmlentities($row["description"]);
									echo('<div id="position' . $rank . '">' . "\n");
									echo('<p> Year : <input type = "text" name="year' . $rank . '" value = "' . $year . '">' . "\n");
									echo('<input type = "button" value = "-" onclick="$(\'#position' . $rank . '\').remove();
										  return false;"></p>' . "\n");
									echo('<textarea name="desc' . $rank . '" rows="8" cols="80">');
									echo($descr);
									echo("</textarea></div>\n"); 
								}
							}
						?>
					</div>
				</p>
				<input type="hidden" name="profile_id" value="<?php echo($_GET['profile_id']) ?>">
				<input type="submit" value = "Save">
				<input type="submit" name="cancel" value = "Cancel">
			</form>
			
			<script type="text/javascript">

				countPos = <?php echo(count($positions)) ?>;
				countEdu = <?php echo(count($educations)) ?>;

				$(document).ready(
					function()
					{
						window.console && console.log("Document ready called");
						
						$("#addPos").click(
							function(event)
							{
								event.preventDefault();
								if(countPos >= 9)
								{
									alert("Maximum of nine position entries exceeded");
									return;
								}

								countPos++;
								window.console && console.log("Adding position" + countPos);

								$("#position_fields").append(
									'<div id="position' + countPos + '">  \
										<p>  \
										    Year :  \
										    <input type = "text" name="year' + countPos + '" value = "" /> \
										    <input type="button" value="-"  \
										        onclick="$(\'#position' + countPos + '\').remove(); return false;">  \
										</p>  \
										<textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>  \
									</div>'
								);
								
							}
						);

						$("#addEdu").click(
							function(event)
							{
								event.preventDefault();
								if(countEdu >= 9)
								{
									alert("Maximum of nine educastion entries exceeded");
									return;
								}

								countEdu++;
								window.console && console.log("Adding education" + countEdu);

								$("#edu_fields").append(
									'<div id="edu' + countEdu + '">  \
										<p>  \
										    Year :  \
										    <input type = "text" name="edu_year' + countEdu + '" value = "" /> \
										    <input type="button" value="-"  \
										        onclick="$(\'#edu' + countEdu + '\').remove(); return false;">  \
										</p>  \
										<p>   \
											School :    \
											<input type = "text" size="80" name="edu_school' + countEdu + '" class="school" value="">   \
										</p>   \
									</div>'
								);
								
								$(".school").autocomplete(
									{source: "school.php"}
								);
							}
						);
						$(".school").autocomplete(
							{source: "school.php"}
						);
					}
				);
			</script>
			
		</div>
	</body>

</html>