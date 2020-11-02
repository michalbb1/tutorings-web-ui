<?php

include_once "database.php";

final class Student
{
    public function __construct($idStudent, $name, $isActive, $defaultPrice, $idDefaultCurrency, $personalGuid)
    {
        $this->idStudent = $idStudent;
		$this->name = $name;
		$this->isActive = $isActive;
		$this->defaultPrice = $defaultPrice;
		$this->idDefaultCurrency = $idDefaultCurrency;
		$this->personalGuid = $personalGuid;
    }
	
	public function getId()
	{
		return $this->idStudent;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getIdDefaultCurrency()
	{
		return $this->idDefaultCurrency;
	}
	
	public function getPersonalGuid()
	{
		return $this->personalGuid;
	}

	private $idStudent;
	private $name;
	private $isActive;
	private $defaultPrice;
	private $idDefaultCurrency;
	private $personalGuid;
}

function getStudent($personalGuid)
{
	$result = array();
	$stmt = getDbConnectionPDO()->prepare("SELECT * FROM students WHERE personalGuid=:personalGuid");
	$stmt->bindValue(":personalGuid", $personalGuid);
    $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if(!$row) {
		return false;
	}
	return new Student($row["idStudent"], $row["name"], $row["isActive"], $row["defaultPrice"], $row["idDefaultCurrency"], $row["personalGuid"]);
}

?>