<?php

include "deployment.php";

function isDatabaseConnectionOpen()
{
	return isset($GLOBALS["dbConnection"]);
}

function openDatabaseConnection()
{
	if(!isDatabaseConnectionOpen())
	{
		try
		{
			$dbConnection = new PDO("mysql:host=".DATABASE_SERVER.";dbname=".DATABASE_DEFAULT_DB, DATABASE_USERNAME, DATABASE_PASSWORD);
			$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$GLOBALS["dbConnection"] = $dbConnection;
		}
		catch(PDOException $e)
		{
			echo "<h2>Connection to database failed :(</h2>";
			die();
		}
	}
}

function getDbConnectionPDO() {
    return $GLOBALS["dbConnection"];
}

function closeDatabaseConnection() {
	$GLOBALS["dbConnection"] = null;
}

?>