<?php

function priceToString($amount, $currencyName = "EUR")
{
	$wholePart = floor($amount / 100);
	$centsPart = $amount % 100;
	$result = $centsPart == 0 ? sprintf("%d", $wholePart) : sprintf("%d.%02d", $wholePart, $centsPart);
	if(strlen($currencyName) > 0) {
		$result .= " $currencyName";
	}

	return $result;
}

function minutesToString($minutes)
{
    if($minutes <= 0) {
        return "&lt;no data&gt;";
    }
    
	$fullHours = floor($minutes / 60);
	$remainingMinutes = $minutes % 60;
	$result = "";
	if($fullHours > 0)
	{
		$result .= "$fullHours hour";
		if($fullHours > 1) {
			$result .= "s";
		}
	}

	if($remainingMinutes > 0)
	{
		if(strlen($result) > 0) {
			$result .= " ";
		}

		$result .= "$remainingMinutes minute";
		if($remainingMinutes > 1) {
			$result .= "s";
		}
	}

	return $result;
}

?>