<?php

include_once "database.php";

final class Tutoring
{
    public function __construct($ID, $startDateTime, $idStudent, $idSubject, $description, $totalMinutes, $price, $idCurrency, $primaryCurrencyPrice, $hasAlreadyBeen, $alreadyPaidAmount)
    {
        $this->ID = $ID;
		$this->startDateTime = $startDateTime;
		$this->idStudent = $idStudent;
		$this->idSubject = $idSubject;
		$this->description = $description;
		$this->totalMinutes = $totalMinutes;
		$this->price = $price;
		$this->idCurrency = $idCurrency;
		$this->primaryCurrencyPrice = $primaryCurrencyPrice;
		$this->hasAlreadyBeen = $hasAlreadyBeen;
		$this->alreadyPaidAmount = $alreadyPaidAmount;
    }
	
	public function getStartDateTime()
	{
		return $this->startDateTime;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function getTotalMinutes()
	{
		return $this->totalMinutes;
	}
	
	public function getPrice()
	{
		return $this->price;
	}
	
	public function getPrimaryCurrencyPrice()
	{
		return $this->primaryCurrencyPrice;
	}
	
	public function getIdCurrency()
	{
		return $this->idCurrency;
	}
	
	public function getAlreadyPaidAmount()
	{
		return $this->alreadyPaidAmount;
	}

	private $ID;
	private $startDateTime;
	private $idStudent;
	private $idSubject;
	private $description;
    private $totalMinutes;
	private $price;
	private $idCurrency;
	private $primaryCurrencyPrice;
    private $hasAlreadyBeen;
    private $alreadyPaidAmount;
}

function getAllUnpaidTutorings($idStudent)
{
	$result = array();
	$stmt = getDbConnectionPDO()->prepare("SELECT * FROM tutorings WHERE idStudent=:idStudent AND alreadyBeen=1 AND primaryCurrencyPrice != alreadyPaid ORDER BY startDateTime DESC");
	$stmt->bindValue(":idStudent", $idStudent);
    $stmt->execute();
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		array_push($result, new Tutoring($row["idTutoring"], $row["startDateTime"], $row["idStudent"], $row["idSubject"], $row["description"], $row["minutes"], $row["price"], $row["idCurrency"], $row["primaryCurrencyPrice"], $row["hasAlreadyBeen"], $row["alreadyPaid"]));
	}
	
	return $result;
}

?>