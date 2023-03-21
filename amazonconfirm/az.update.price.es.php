<?php

/*	
* -----------------------------------------------------------------
*	File			az.update.price.es.php
*	Version			1.0
*	Update Date		2014/08/10
*	Created By		Somsak Ri (risomsak@gmail.com)
* -----------------------------------------------------------------
*/
require dirname(__FILE__)."/function.php";


$connector = new amazonconnector;

$account = 4;

$currency = 'EUR';

$fileDes  = dirname(__FILE__) . "/temp/es.dat";

$content = file_get_contents($fileDes);

$inv_array = json_decode($content);

print_r($inv_array);

$inventoryArray = array_chunk($inv_array, 1000);

for ( $i = 0; $i < count($inventoryArray); $i++ ){
	$xml = $connector->makeAzXMLFeed($inventoryArray[$i], $account, $currency);
	echo $xml;
	$result = $connector->submitPriceFeed($account, $xml);
    echo $result;
}