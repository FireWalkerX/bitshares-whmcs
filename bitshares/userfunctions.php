<?php
$path = getcwd();
chdir(ROOT.'..');
require 'dbconnect.php';
require 'includes/functions.php';
require 'includes/gatewayfunctions.php';
require 'includes/invoicefunctions.php';
chdir($path);
require 'config.php';

function isOrderCompleteUser($memo, $order_id)
{

	$result    = mysql_query("SELECT tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.id=$order_id and tblinvoices.status='Paid'");
	$data      = mysql_fetch_assoc($result);
	
	if($data)
	{
		$total = $data['total'];
		$asset = btsCurrencyToAsset($data['code']);
		$hash =  btsCreateEHASH(accountName,$order_id, $total, $asset, hashSalt);
		$memoSanity = btsCreateMemo($hash);			
		if($memoSanity === $memo)
		{	
			return TRUE;
		}	
	}
	return FALSE;	
}
function doesOrderExistUser($memo, $order_id)
{

	$result    = mysql_query("SELECT tblinvoices.total, tblinvoices.status, tblcurrencies.code, tblinvoices.date FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.id=$order_id and tblinvoices.status='Unpaid'");
	$data      = mysql_fetch_assoc($result);
	if($data)
	{
	
		$total = $data['total'];
		$asset = btsCurrencyToAsset($data['code']);
		
		$hash =  btsCreateEHASH(accountName,$order_id, $total, $asset, hashSalt);
		$memoSanity = btsCreateMemo($hash);		
		if($memoSanity === $memo)
		{	
			$order = array();
			$order['order_id'] = $order_id;
			$order['total'] = $total;
			$order['asset'] = $asset;
			$order['memo'] = $memo;	
      $order['date_added'] = $data['date'];	
			
			return $order;
		}
	}
	return FALSE;
}
function getOpenOrdersUser()
{
	$openOrderList = array();
	$result    = mysql_query("SELECT tblinvoices.id, tblinvoices.total, tblinvoices.status, tblcurrencies.code, tblinvoices.date FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.status='Unpaid'");
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$newOrder = array();
		$newOrder['total'] = $row['total'];
		$newOrder['currency_code'] = $row['code'];
		$newOrder['order_id'] = $row['id'];
		$newOrder['date_added'] = $row['date'];
		array_push($openOrderList,$newOrder);
	}
  return $openOrderList;
}
function completeOrderUser($order)
{
	
	$ret = array();
    $gatewaymodule = "bitshares";
	$GATEWAY       = getGatewayVariables($gatewaymodule);	
  
	# Checks invoice ID is a valid invoice number or ends processing
	$invoiceid = checkCbInvoiceID($order['order_id'], $GATEWAY["name"]);

	$transid = $order['trx_id'];
	checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

	# Successful
	$fee = 0;
	$amount = $order['amount']; // left blank, this will auto-fill as the full balance
	addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice	  
	$ret['url'] = baseURL.'viewinvoice.php?id='.$order['order_id'];				
	logTransaction($GATEWAY["name"], $order, $order['status']);
	return $ret;
}
function cancelOrderUser($order)
{
	
	$response = array();
	$res = mysql_query("UPDATE tblinvoices, tblclients, tblcurrencies SET tblinvoices.status='Cancelled' WHERE tblinvoices.userid = tblclients.id AND tblclients.currency = tblcurrencies.id and tblinvoices.id='".$order['order_id']."'");
	if(!$res)
	{
		$response['error'] = 'Could not cancel this order!';
		return $response;
	}
	$response['url'] = baseURL.'viewinvoice.php?id='.$order['order_id'];
	return $response;
}
function cronJobUser()
{
	return 'Success!';
}
function createOrderUser()
{

	$amount    = $_REQUEST['amount'];
	$asset = btsCurrencyToAsset($_REQUEST['code']);
	$order_id = $_REQUEST['invoiceId'];
	
	$hash =  btsCreateEHASH(accountName,$order_id, $amount, $asset, hashSalt);
	$memo = btsCreateMemo($hash);
	$ret = array(
		'accountName'     => accountName,
		'order_id'     => $order_id,
		'memo'     => $memo
	);
	return $ret;	
}

?>