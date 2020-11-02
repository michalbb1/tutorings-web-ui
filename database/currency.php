<?php

include_once "database.php";

final class Currency
{
	public function __construct($ID, $name)
    {
		$this->ID = $ID;
		$this->name = $name;
	}
	
	public function getId()
	{
		return $this->ID;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	private $ID;
	private $name;
}

function getCurrencyById($idCurrency)
{
	if(!isset($GLOBALS["currencyRepository"])) {
		$GLOBALS["currencyRepository"] = array();
	}

	if(array_key_exists($idCurrency, $GLOBALS["currencyRepository"])) {
		return $currenciesRepository[$idCurrency];
	}
	
	$stmt = getDbConnectionPDO()->prepare("SELECT * FROM currencies WHERE idCurrency=:idCurrency");
	$stmt->bindValue(":idCurrency", $idCurrency);
    $stmt->execute();
	if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$currency = new Currency($row["idCurrency"], $row["name"]);
		$GLOBALS["currencyRepository"][$currency->getId()] = $currency;
		return $currency;
	}
	
	return null;
}

?>