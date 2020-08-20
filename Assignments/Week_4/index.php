<?php

require_once "pdo.php";
require_once "util.php";
session_start();

?>

<!DOCTYPE html>

<html lang = "en">

	<head>
		<meta charset = "utf-8">
		<title>Jared Best | Resume Registry</title>
		<?php require_once "head.php" ?>
	</head>

	<body>
		<div class = "container">
			<h1>Jared Best | Resume Registry</h1>
			<?php
				flashmessages();
				if(isset($_SESSION["name"]) && isset($_SESSION["user_id"]))
					echo '<a href = "logout.php">Logout</a>' . "\n";
				else
					echo '<a href = "login.php">Please log in</a>' . "\n";
			?>
		
			<table border = "1">
				<?php
					$sql = "SELECT profile_id, first_name, last_name, email, headline, summary FROM profile";
					$stmt = $pdo -> query($sql);

					if($stmt -> rowCount() > 0)
					{
						echo "<tr><th>Name</th><th>Headline</th>";
						if(isset($_SESSION["name"]) && isset($_SESSION["user_id"]))
							echo"<th>Action</th>";
						echo "</tr>\n";
						while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
						{
							$firstname = htmlentities($row["first_name"]);
							$lastname = htmlentities($row["last_name"]);
							$headline = htmlentities($row["headline"]);
							$profileID = $row["profile_id"];

							echo '<tr><td><a href="view.php?profile_id=' . $profileID . '">' . $firstname . " " . $lastname . "</a></td>";
							echo "<td>" . $headline . "</td>";
							if(isset($_SESSION["name"]) && isset($_SESSION["user_id"]))
								echo '<td><a href="edit.php?profile_id=' . $profileID . '">Edit</a> <a href="delete.php?profile_id=' . $profileID . '">Delete</a></td>';
							echo "</tr>\n";
						}
					}
				?>
			</table>
			<?php
				if(isset($_SESSION["name"]) && isset($_SESSION["user_id"]))
					echo '<a href = "add.php">Add New Entry</a>' . "\n";
			?>
		</div>
	</body>

</html>