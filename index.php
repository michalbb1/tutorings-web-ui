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
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<?
echo "<title>".$student->getName()." - Tutorings Report</title>";
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<h1><?php echo $student->getName(); ?> - Tutorings Report</h1>
<h3><a data-toggle="collapse" href="#summary">Summary:</a></h3>
<div id="summary" class="panel-collapse collapse in">
<div class="infoBlock">
<?
$isDefaultCurrency = $student->getIdDefaultCurrency() == 1;
$defaultCurrency = getCurrencyById($student->getIdDefaultCurrency());
$currencyName = $defaultCurrency->getName();
$unpaidTutorings = getAllUnpaidTutorings($student->getId());
echo "<b>Number of sessions: </b>".count($unpaidTutorings)."<br />";
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

echo "<b>Total time spent: </b>".minutesToString($totalMinutes)."<br />";
echo "<b>Price per hour (averaged): </b>$pricePerHourAveragedFormatted<br />";
echo "<b>Total amount: </b>".priceToString($totalPrice, $currencyName).$totalPriceSuffix;
echo "</div></div>";
?>

<h3><a data-toggle="collapse" href="#paymentDetailsSepa">Payment details (SEPA bank transfer):</a></h3>
<?
$allClasses = "panel-collapse collapse";
if(count($unpaidTutorings) > 0) {
	$allClasses .= " in";
}
echo "<div id=\"paymentDetailsSepa\" class=\"$allClasses\">";
?>
	<div class="infoBlock">
		<b>IBAN number: </b>SK24 8360 5207 0042 0351 4042<br />
		<b>BIC (SWIFT) code: </b>BREXSKBX<br />
		<b>Beneficiary's name: </b>Michal Bubnár<br />
		<?
			echo "<b>Total amount: </b>".priceToString($totalPrice, $currencyName).$totalPriceSuffix."<br />";
		?>
		<h4><a data-toggle="collapse" href="#additionalBankData">Additional bank information (if required):</a></h4>
		<div id="additionalBankData" class="panel-collapse collapse">
			<b>Name of bank: </b>mBank S.A., pobočka zahraničnej banky<br />
			<b>Bank address: </b>Pribinova 10, 811 09 Bratislava<br />
		</div>
	</div>
</div>

<h3><a data-toggle="collapse" href="#paymentDetailsPaypal">Payment details (PayPal):</a></h3>
<div id="paymentDetailsPaypal" class="panel-collapse collapse">
	<div class="infoBlock">
		<b>E-mail: </b>michalbb1@gmail.com<br />
		<?
			echo "<b>Total amount: </b>".priceToString($totalPrice, $currencyName).$totalPriceSuffix."<br />";
		?>
	</div>
</div>

<?
echo "<h3><a data-toggle=\"collapse\" href=\"#unpaidTutorings\">Unpaid tutorings:</a></h3>";
echo "<div class=\"infoBlock\">";
echo "<div id=\"unpaidTutorings\" class=\"panel-collapse collapse in\">";
if(count($unpaidTutorings) == 0)
{
	echo "All tutorings have been paid for!";
}
else
{
	
	echo "	<div class=\"divTable\">";
	echo "		<div class=\"divTableRow\">";
	echo "			<div class=\"divTableHeadCell\">Date And Time</div>";
	echo "			<div class=\"divTableHeadCell\">Duration</div>";
	echo "			<div class=\"divTableHeadCell\">Price</div>";
	echo "			<div class=\"divTableHeadCell\">Topic</div>";
	echo "		</div>";

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
echo "</div>";
echo "</div>";
?>

</body>
</html>

<?
closeDatabaseConnection();
?>