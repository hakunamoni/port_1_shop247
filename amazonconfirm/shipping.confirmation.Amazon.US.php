<?php

error_reporting(E_ALL);

//--------  include part   ------------------------------//

require dirname(__FILE__)."/../function.php";
require dirname(__FILE__)."/../library/NetSuiteService.php";

echo "--------------------------------------------------------------------------<br>";
echo "Start Shipping Confirmation Script.<br>";
echo "--------------------------------------------------------------------------<br>";

//--------------------------------------------------------------------
//--------------------------------------------------------------------

// Define Email Address For Send Alert Message

//--------------------------------------------------------------------
//--------------------------------------------------------------------

$emailAddress = 'hakunamoni@gmail.com';

//--------------------------------------------------------------------
//--------------------------------------------------------------------

$service	= new NetSuiteService();
$service->setSearchPreferences(false, 200);

$connector	= new amazonconnector;

$searchFulfilledOrders = new TransactionSearch();

$type = array();
$type[] = 'salesOrder';

$orderType = new SearchEnumMultiSelectField();
$orderType->searchValue = $type;
$orderType->operator = "anyOf";

$status = array();
//$status[] = 'salesOrderPendingBilling';
//$status[] = 'salesOrderPendingFulfillment';

$status[] = 'salesOrderBilled';
$status[] = 'salesOrderPendingBilling';
$status[] = 'salesOrderPendingBillingPartiallyFulfilled';

$orderStatus = new SearchEnumMultiSelectField();
$orderStatus->searchValue = $status;
$orderStatus->operator = "anyOf";

$date_ago_twohours = date('Y-m-d\TH:i:s', time()-3600*30*24);
echo $date_ago_twohours;
                                        
$orderStatus = new SearchEnumMultiSelectField();
$orderStatus->searchValue = $status;
$orderStatus->operator = "anyOf";

$channel = new SearchMultiSelectField();
$channel->operator = "anyOf";
$channelRec = new RecordRef();
$channelRec->internalId = '13';
$channel->searchValue = array($channelRec);


$searchFulfilledOrders->basic->status = $orderStatus;
$searchFulfilledOrders->basic->type = $orderType;
$searchFulfilledOrders->basic->class = $channel;
$searchFulfilledOrders->basic->lastModifiedDate->operator = 'after';
$searchFulfilledOrders->basic->lastModifiedDate->searchValue = $date_ago_twohours; 

    
$requestSearchFulfilledOrder = new SearchRequest();
$requestSearchFulfilledOrder->searchRecord = $searchFulfilledOrders;

$responseSearchFulfilledOrder = $service->search($requestSearchFulfilledOrder);

$shippedArray = array();

$i = 0;
$searchid = $responseSearchFulfilledOrder->searchResult->searchId;
foreach ( $responseSearchFulfilledOrder->searchResult->recordList->record as $record ){
	$shippedArray[$i]['amazon_order_id'] = $record->otherRefNum;
	if ( $record->actualShipDate == '' || $record->actualShipDate == null){
		if ( $record->shipDate == '' || $record->shipDate == null ){
			$shippedArray[$i]['shipping_date'] = $today . 'T00:00:00.000-08:00';
		}else{
			$shippedArray[$i]['shipping_date']	 = $record->shipDate;
		}
	}else{
		$shippedArray[$i]['shipping_date']	 = $record->actualShipDate;
	}
	$linkTrackingNumber = explode(" ", $record->linkedTrackingNumbers);
	$shippedArray[$i]['tracking_number'] = $linkTrackingNumber[0];
	$i++;
}
$j = 2;
while( count($responseSearchFulfilledOrder->searchResult->recordList->record) == 200 ){
	
    
    $requestSearchMoreFulfilledOrder = new SearchMoreWithIdRequest();
    $requestSearchMoreFulfilledOrder->pageIndex = $j;
    $requestSearchMoreFulfilledOrder->searchId = $searchid;
	$responseSearchFulfilledOrder = $service->searchMoreWithId($requestSearchMoreFulfilledOrder);
	foreach ( $responseSearchFulfilledOrder->searchResult->recordList->record as $record ){
		$shippedArray[$i]['amazon_order_id'] = $record->otherRefNum;
		if ( $record->actualShipDate == '' || $record->actualShipDate == null){
			if ( $record->shipDate == '' || $record->shipDate == null ){
				$shippedArray[$i]['shipping_date'] = $today . 'T00:00:00.000-08:00';
			}else{
				$shippedArray[$i]['shipping_date']	 = $record->shipDate;
			}
		}else{
			$shippedArray[$i]['shipping_date']	 = $record->actualShipDate;
		}
		$linkTrackingNumber = explode(" ", $record->linkedTrackingNumbers);
		$shippedArray[$i]['tracking_number'] = $linkTrackingNumber[0];
		$i++;
	}
	$j++;
}

print_r($shippedArray);

exit;

if ( count($shippedArray) > 0 ){

	$filename = dirname(__FILE__)."/../temp.txt";

	$connector->save_array_to_file($filename, $shippedArray);

	$url = 'http://golfoutletsusa.net/importOrder/library/amazon_usa/MarketplaceWebService/pro/submitConfirmFeed.php?index=6';

	$submit_feed = get_web_page($url,$curl_data);

	echo $submit_feed;

}else{
	echo "No data to update now";
}
function get_web_page( $url )
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch) ;
    $header  = curl_getinfo($ch);
    curl_close($ch); 
    return $content;
}

echo "--------------------------------------------------------------------------<br>";
echo "End Shipping Confirmation Script.<br>";
echo "--------------------------------------------------------------------------<br>";

?>