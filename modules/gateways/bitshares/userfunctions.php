<?php
require ROOT.'../../../dbconnect.php';
require ROOT.'../../../includes/functions.php';
require ROOT.'../../../includes/gatewayfunctions.php';
require ROOT.'../../../includes/invoicefunctions.php';

function getOpenOrdersHelper()
{
	$openOrderList = array();
	$result    = mysql_query("SELECT tblinvoices.id, tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.status='Unpaid'");
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$newOrder = array();
		$newOrder['total'] = $row['total'];
		$newOrder['currency_code'] = $row['code'];
		$newOrder['order_id'] = $row['id'];
		$newOrder['date_added'] = 0;
		array_push($openOrderList,$newOrder);
	}
  return $openOrderList;
}
function isOrderComplete($memo, $order_id)
{
	global $accountName;
	global $hashSalt;
	$result    = mysql_query("SELECT tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.id=$order_id and tblinvoices.status='Paid'");
	$data      = mysql_fetch_assoc($result);
	
	if($data)
	{
		$total = $data['total'];
		$asset = btsCurrencyToAsset($data['code']);
		$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
		$memoSanity = btsCreateMemo($hash);			
		if($memoSanity === $memo)
		{	
			return TRUE;
		}	
	}
	return FALSE;	
}
function doesOrderExist($memo, $order_id)
{
	global $accountName;
	global $hashSalt;
	$result    = mysql_query("SELECT tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.id=$order_id and tblinvoices.status='Unpaid'");
	$data      = mysql_fetch_assoc($result);
	if($data)
	{
	
		$total = $data['total'];
		$asset = btsCurrencyToAsset($data['code']);
		
		$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
		$memoSanity = btsCreateMemo($hash);		
		if($memoSanity === $memo)
		{	
			$order = array();
			$order['order_id'] = $order_id;
			$order['total'] = $total;
			$order['asset'] = $asset;
			$order['memo'] = $memo;	
			
			return $order;
		}
	}
	return FALSE;
}

function completeOrderUser($memo, $order_id)
{
	global $baseURL;
	global $accountName;
	global $rpcUser;
	global $rpcPass;
	global $rpcPort;
	global $demoMode;
	global $hashSalt;
	$gatewaymodule = "bitshares";
	$GATEWAY       = getGatewayVariables($gatewaymodule);	
	$orderArray = getOrder($memo, $order_id);
	if(count($orderArray) <= 0)
	{
	  $ret = array();
	  $ret['error'] = 'Could not find this order in the system, please review the Order ID and Memo';
	  return $ret;
	}

	if ($orderArray[0]['order_id'] !== $order_id) {
		$ret = array();
		$ret['error'] = 'Invalid Order ID';
		return $ret;
	}
	$demo = FALSE;
	if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
	{
		$demo = TRUE;
	}
	$response = btsVerifyOpenOrders($orderArray, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);

	if(array_key_exists('error', $response))
	{
	  $ret = array();
	  $ret['error'] = 'Could not verify order. Please try again';
	  return $ret;
	}
	$ret = array();	
	$ret['url'] = $baseURL;
	foreach ($response as $responseOrder) {
		switch($responseOrder['status'])
		{
			case 'complete':
			case 'overpayment':   
				# Checks invoice ID is a valid invoice number or ends processing
				$invoiceid = checkCbInvoiceID($responseOrder['order_id'], $GATEWAY["name"]);

				$transid = $responseOrder['trx_id'];
				checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

				# Successful
				$fee = 0;
				$amount = ''; // left blank, this will auto-fill as the full balance
				addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice	  
				$ret['url'] = 'viewinvoice.php?id='.$order_id;				
				logTransaction($GATEWAY["name"], $responseOrder, $responseOrder['status']);
				break;		
			default:
				break;	    
		}		 
	}
	
	return $ret;
}
function cancelOrderUser($memo, $order_id)
{
	global $baseURL;
	$response = array();
	$response['url'] = $baseURL;
	
	$orderArray = getOrder($memo, $order_id);
	
	if(count($orderArray) <= 0)
	{
	  return $response;
	}
	if ($orderArray[0]['order_id'] !== $order_id)
	{
	  return $response;
	}
	$res = mysql_query("UPDATE tblinvoices, tblclients, tblcurrencies SET tblinvoices.status='Cancelled' WHERE tblinvoices.userid = tblclients.id AND tblclients.currency = tblcurrencies.id and tblinvoices.id=$order_id");
	if(!$res)
	{
		return $response;
	}
	$response['url'] = $baseURL.'viewinvoice.php?id='.$order_id;
	return $response;
}
function cronJobUser()
{
	global $cronToken;
	global $baseURL;
	global $accountName;
	global $rpcUser;
	global $rpcPass;
	global $rpcPort;
	global $demoMode;
	global $hashSalt;
	$gatewaymodule = "bitshares";
	$GATEWAY       = getGatewayVariables($gatewaymodule);	
	$orderArray = getOpenOrdersHelper();
	if(count($orderArray) <= 0)
	{
	  $ret = array();
	  $ret['error'] = 'No open orders found!';
	  return $ret;
	}

	$demo = FALSE;
	if($demoMode === "1" || $demoMode === 1 || $demoMode === TRUE || $demoMode === "true")
	{
		$demo = TRUE;
	}
	$response = btsVerifyOpenOrders($orderArray, $accountName, $rpcUser, $rpcPass, $rpcPort, $hashSalt, $demo);

	if(array_key_exists('error', $response))
	{
	  $ret = array();
	  $ret['error'] = 'Could not verify order. Please try again';
	  return $ret;
	}	
	foreach ($response as $responseOrder) {
		switch($responseOrder['status'])
		{
			case 'complete':    	
			case 'overpayment':
				# Checks invoice ID is a valid invoice number or ends processing
				$invoiceid = checkCbInvoiceID($responseOrder['order_id'], $GATEWAY["name"]);

				$transid = $responseOrder['trx_id'];
				checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

				# Successful
				$fee = 0;
				$amount = ''; // left blank, this will auto-fill as the full balance
				addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice	 
				logTransaction($GATEWAY["name"], $responseOrder, $responseOrder['status']);
				break; 
			default:
				break;	    
		}	 
	}

	return $response;	
}
function createOrderUser()
{

	global $accountName;
	global $hashSalt;
	$amount    = $_REQUEST['amount'];
	$asset = btsCurrencyToAsset($_REQUEST['code']);
	$order_id = $_REQUEST['invoiceId'];
	
	$hash =  btsCreateEHASH($accountName,$order_id, $amount, $asset, $hashSalt);
	$memo = btsCreateMemo($hash);
	$ret = array(
		'accountName'     => $accountName,
		'order_id'     => $order_id,
		'memo'     => $memo
	);
	return $ret;	
}

?>