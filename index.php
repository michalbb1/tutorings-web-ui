<?php

include "database.php";
include "string_utility.php";

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

openDatabaseConnection();
$student = getStudent($_GET["personalGuid"]);
if(!$student)
{
	closeDatabaseConnection();
	echo "<h1>Unknown student!</h1>";
	die();
}

?>

<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<?
echo "<title>".$student->getName()." - Tutorings Report</title>";
?>
<style>

.introCaption
{
  width: 100%;
  text-align: center;
  font-size: 175%;
  background-color: #F0F0F0;
  font-family: 'Helvetica', 'Arial';
  font-weight: bold;
  margin-bottom: 20px;
  padding: 5px;
}

.separatorCaption
{
  width: 100%;
  text-align: left;
  font-size: 110%;
  background-color: #F0F0F0;
  font-family: 'Helvetica', 'Arial';
  font-weight: italic;
  margin-top: 10px;
  margin-bottom: 10px;
  padding: 5px;
}

.caption, .data
{
  font-size: 125%;
  font-family: 'Helvetica', 'Arial';
}

.caption
{
  font-weight: bold;
  float: left;
  padding-right: 10px;
}

.data
{
  clear: right;
  padding-bottom: 5px;
}

.divTable{
	margin-top: 5px;
	display: table;
	width: 80%;
	font-size: 120%;
	font-family: 'Arial';
}

.divTableRow {
	display: table-row;
}

.divTableHeadCell
{
	background-color: #79BCFF;
	font-weight: bold;
	border: 1px solid #999999;
	padding: 3px 10px;
	display: table-cell;
}

.divTableCellEven {
	border: 1px solid #999999;
	display: table-cell;
	padding: 3px 10px;
	border: 1px solid #C0C0C0;
}

.divTableCellOdd {
	background-color: #F0F0F0;
	border: 1px solid #C0C0C0;
	display: table-cell;
	padding: 3px 10px;
	border: 1px solid #C0C0C0;
}

.divTableBody {
	display: table-row-group;
}

</style>
</head>
<body>

<div class="introCaption"><?php echo $student->getName(); ?> - Tutorings Report</div>

<?
$isDefaultCurrency = $student->getIdDefaultCurrency() == 1;
$defaultCurrency = getCurrencyById($student->getIdDefaultCurrency());
$currencyName = $defaultCurrency->getName();
$unpaidTutorings = getAllUnpaidTutorings($student->getId());
echo "<div class=\"caption\">Number Of Sessions: </div><div class=\"data\">".count($unpaidTutorings)."</div>";
$totalMinutes = 0;
$totalPrice = 0;
$totalPricePrimaryCurrency = 0;
foreach($unpaidTutorings as $tutoring)
{
	$totalPrice += $tutoring->getPrice();
	$totalPricePrimaryCurrency += $tutoring->getPrimaryCurrencyPrice();
	$totalMinutes += $tutoring->getTotalMinutes();
}

$averagePrice = $totalMinutes == 0 ? 0 : 60*($totalPrice / $totalMinutes);
$pricePerHourAveragedFormatted = priceToString($averagePrice, $currencyName);

foreach($unpaidTutorings as $tutoring)
{
	if($tutoring->getAlreadyPaidAmount() == 0) {
		continue;
	}

	if($isDefaultCurrency) {
		$totalPrice -= $tutoring->getAlreadyPaidAmount();
	}
	else
	{
		$approximateRate = $tutoring->getAlreadyPaidAmount() / $tutoring->getPrimaryCurrencyPrice();
		$totalPrice -= $approximateRate * $tutoring->getPrice();
	}
	
	$totalPricePrimaryCurrency -= $tutoring->getAlreadyPaidAmount();
}

$totalPriceSuffix = $defaultCurrency->getId() != 1 ? " (" . priceToString($totalPricePrimaryCurrency) . ")" : "";

echo "<div class=\"caption\">Total Time Spent: </div><div class=\"data\">".minutesToString($totalMinutes)."</div>";
echo "<div class=\"caption\">Price Per Hour (Averaged): </div><div class=\"data\">$pricePerHourAveragedFormatted</div>";
?>

<div class="separatorCaption">Payment details:</div>
<div class="caption">IBAN Number: </div><div class="data">SK24 8360 5207 0042 0351 4042</div>
<div class="caption">BIC (SWIFT) Code: </div><div class="data">BREXSKBX</div>
<div class="caption">Beneficiary's Name: </div><div class="data">Michal Bubnár</div>
<?
echo "<div class=\"caption\">Total Amount: </div><div class=\"data\">" . priceToString($totalPrice, $currencyName) . $totalPriceSuffix . "</div>";
?>
<div class="separatorCaption">Additional bank information (if required):</div>
<div class="caption">Name of bank: </div><div class="data">mBank S.A., pobočka zahraničnej banky</div>
<div class="caption">Address: </div><div class="data">Pribinova 10, 811 09 Bratislava</div>
<?
if(count($unpaidTutorings) == 0)
{
	echo "<div class=\"separatorCaption\">No unpaid tutorings found!</div>";
}
else
{
	echo "<div class=\"separatorCaption\">Following table shows data in detail:</div>";
	echo "<div class=\"divTable\">";
	echo "	<div class=\"divTableRow\">";
	echo "		<div class=\"divTableHeadCell\">Date And Time</div>";
	echo "		<div class=\"divTableHeadCell\">Duration</div>";
	echo "		<div class=\"divTableHeadCell\">Price</div>";
	echo "		<div class=\"divTableHeadCell\">Topic</div>";
	echo "	</div>";

	$even = true;
	foreach($unpaidTutorings as $tutoring)
	{
		$className = $even ? "divTableCellEven" : "divTableCellOdd";
		echo "<div class=\"divTableRow\">";
		echo "	<div class=\"$className\">".$tutoring->getStartDateTime()."</div>";
		echo "	<div class=\"$className\">".sprintf("%dh %02dm", floor($tutoring->getTotalMinutes() / 60), $tutoring->getTotalMinutes() % 60)."</div>";
		echo "	<div class=\"$className\">";
		$isPartiallyPaid = $tutoring->getAlreadyPaidAmount() > 0 && $tutoring->getPrimaryCurrencyPrice() != $tutoring->getAlreadyPaidAmount();
		if($isPartiallyPaid)
		{
			$primaryCurrencyPartiallyPaid = priceToString($tutoring->getAlreadyPaidAmount(), "") . " / " . priceToString($tutoring->getPrimaryCurrencyPrice());
			if($isDefaultCurrency) {
				echo $primaryCurrencyPartiallyPaid;
			}
			else
			{
				$approximateRate = $tutoring->getAlreadyPaidAmount() / $tutoring->getPrimaryCurrencyPrice();
				$approximatePaidInDefaultCurrency = floor($approximateRate * $tutoring->getPrice());
				echo priceToString($approximatePaidInDefaultCurrency, "") . " / " . priceToString($tutoring->getPrice(), $currencyName);
				echo " (" . $primaryCurrencyPartiallyPaid . ")";
			}

			echo " - partially paid";
		}
		else
		{
			$primaryCurrencyPrice = priceToString($tutoring->getPrimaryCurrencyPrice());
			if($isDefaultCurrency) {
				echo $primaryCurrencyPrice;
			}
			else {
				echo priceToString($tutoring->getPrice(), $currencyName) . " (" . $primaryCurrencyPrice . ")";
			}
		}
		echo "</div>";
		echo "	<div class=\"$className\">".$tutoring->getDescription()."</div>";
		echo "</div>";
		$even = !$even;
	}
	echo "</div>";
}
?>

</body>
</html>

<?
closeDatabaseConnection();
?>