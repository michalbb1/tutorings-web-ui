<?php

include_once "database/database.php";
include_once "database/currency.php";
include_once "database/student.php";
include_once "database/tutoring.php";

include_once "string_utility.php";

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