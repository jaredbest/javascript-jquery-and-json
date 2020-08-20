<?php

//util.php
function flashMessages()
{
    if(isset($_SESSION["success"]))
    {
    	echo('<p style="color:green;">' . $_SESSION["success"] . "</p>\n");
    	unset($_SESSION["success"]);
    }
    if(isset($_SESSION["error"]))
    {
    	echo('<p style="color:red;">' . $_SESSION["error"] . "</p>\n");
    	unset($_SESSION["error"]);
    }
}


function validateProfile($firstName, $lastName, $email, $headline, $summary)
{
    if(strlen($firstName) < 1 || strlen($lastName) < 1 || strlen($email) < 1 || strlen($headline) < 1 || strlen($summary) < 1)
    {
        $_SESSION["error"] = "All field are required";
        return false;
    }
    if(strpos($email, "@") === false)
    {
        $_SESSION["error"] = "Email address must contain @";
        return false;
    }

    return true;
}

//*
function validatePos()
{
    for($i = 1; $i <= 9; $i++)
    {
        $year = "year" . $i;
        $desc = "desc" . $i;

        if(isset($_SESSION[$year]) && isset($_SESSION[$desc]))
        {
            $yearVal = $_SESSION[$year];
            $descVal = $_SESSION[$desc];

            if(strlen($yearVal) == 0 || strlen($descVal) == 0)
            {
                $_SESSION["error"] = "All field are required";
                return false;
            }

            if(!is_numeric($yearVal))
            {
                $_SESSION["error"] = "Position year must be numeric";
                return false;
            }

        }
    }

    return true;
}
//*/

function validateFields($yearField, $otherField, $posOrEdu)
{

    for($i = 1; $i <= 9; $i++)
    {
        $year = $yearField . $i;
        $other = $otherField . $i;


        if(isset($_SESSION[$year]) && isset($_SESSION[$other]))
        {
            $yearVal = $_SESSION[$year];
            $otherVal = $_SESSION[$other];

            if(strlen($yearVal) == 0 || strlen($otherVal) == 0)
            {
                $_SESSION["error"] = "All field are required";
                return false;
            }

            if(!is_numeric($yearVal))
            {
                $_SESSION["error"] = $posOrEdu . " year must be numeric";
                return false;
            }

        }
    }

    return true;
}

function loadEduOrPos($pdo, $profileID, $eduOrPos)
{
    $sql = "SELECT * FROM " . $eduOrPos . " WHERE profile_id = :prof ORDER BY rank";
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute(array(":prof" => $profileID));
    $positions = array();

    while($row = $stmt -> fetch(PDO::FETCH_ASSOC))
        $positions[] = $row;

    return $positions;
}

function initSession($yearName, $otherName)
{

    for($i = 1; $i <=9; $i++)
    {
        $year = $yearName . $i;
        $other = $otherName . $i;

        if(isset($_POST[$year]) && isset($_POST[$other]))
        {
            $_SESSION[$year] = $_POST[$year];
            $_SESSION[$other] = $_POST[$other];  
        }
    }
}

function insertPosition($pdo, $profileID)
{
    $rank = 1;

    for($i = 1; $i <= 9; $i++)
    {
        $year = "year" . $i;
        $desc = "desc" . $i;

        if(isset($_SESSION[$year]) && isset($_SESSION[$desc]))
        {
            $yearVal = $_SESSION[$year];
            $descVal = $_SESSION[$desc];
            unset($_SESSION[$year]);
            unset($_SESSION[$desc]);

            $sql = "INSERT INTO position (profile_id, rank, year, description) VALUES (:pid, :rank, :year, :descr)";
            $stmt = $pdo -> prepare($sql);
            $stmt -> execute(array(":pid" => $profileID, ":rank" => $rank, ":year" => $yearVal, ":descr" => $descVal));
        }
        $rank++;
    }
}

function insertEducation($pdo, $profileID)
{
    $rank = 1;
    
    for($i = 1; $i <= 9; $i++)
    {
        $year = "edu_year" . $i;
        $school = "edu_school" . $i;

        if(isset($_SESSION[$year]) && isset($_SESSION[$school]))
        {
            $yearVal = $_SESSION[$year];
            $schoolVal = $_SESSION[$school];
            unset($_SESSION[$year]);
            unset($_SESSION[$school]);

            // Try to insert new school in case it is not already in the database
            
            $institutionID = -1;

            try
            {
                $sql = "INSERT INTO institution(name) VALUES (:schoolName)";
                $stmt = $pdo -> prepare($sql);
                $stmt -> execute(array(":schoolName" => $schoolVal));
                $institutionID = $pdo -> lastInsertId();
            }
            catch(Exception $e)
            {
                $sql = "SELECT institution_id FROM institution WHERE name = :schoolName";
                $stmt = $pdo -> prepare($sql);
                $stmt -> execute(array(":schoolName" => $schoolVal));
                $row = $stmt -> fetch(PDO::FETCH_ASSOC);
                $institutionID = $row["institution_id"];
            }

            $sql = "INSERT INTO education (profile_id, institution_id, rank, year) VALUES (:pid, :iid, :rank, :year)";
            $stmt = $pdo -> prepare($sql);
            $stmt -> execute(array(":pid" => $profileID, ":iid" => $institutionID, ":rank" => $rank, ":year" => $yearVal));

        }
        $rank++;
    }
}

?>