<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2011-2014 bitshares
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

# Required File Includes
include '../../../dbconnect.php';
include '../../../includes/functions.php';
include '../../../includes/gatewayfunctions.php';
include '../../../includes/invoicefunctions.php';

require_once '../bitshares/bts_lib.php';

$gatewaymodule = "bitshares";
$GATEWAY       = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"]) {
    logTransaction($GATEWAY["name"], $_POST, 'Not activated');
    error_log('bitshares module not activated');
    die("bitshares module not activated");
}
$result    = mysql_query("SELECT tblinvoices.id, tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.status='Unpaid'");
$openOrderList = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$newOrder = array();
	$newOrder['total'] = $row['total'];
	$newOrder['currency_code'] = $row['code'];
	$newOrder['order_id'] = $row['id'];
	$newOrder['date_added'] = 0;
	array_push($openOrderList,$newOrder);
}
$demo = FALSE;
if($GATEWAY['demoMode'] == "1" || $GATEWAY['demoMode'] == 1 || $GATEWAY['demoMode'] == TRUE || $GATEWAY['demoMode'] == "true")
{
	$demo = TRUE;
}
$response   = btsVerifyOpenOrders($openOrderList, $GATEWAY['accountName'], $GATEWAY['rpcUser'], $GATEWAY['rpcPass'], $GATEWAY['rpcPort'], $demo);
if(array_key_exists('error', $response))
{
	logTransaction('CrobJob error: ' .$response['error']);
	die($response);
}
foreach ($response as $responseOrder) {
	// update the order based on response status (processing for partial funds and complete for full funds)	
	switch($responseOrder['status'])
	{
		case 'complete':
			$order_id = $responseOrder['order_id'];
			# Checks invoice ID is a valid invoice number or ends processing
			$invoiceid = checkCbInvoiceID($order_id, $GATEWAY["name"]);

			$transid = $responseOrder['trxId'];
			checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

			# Successful
			$fee = 0;
			$amount = ''; // left blank, this will auto-fill as the full balance
			addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice
			logTransaction($GATEWAY["name"], $responseOrder, "Successful");
			break;		
		case 'overpayment':		
			$order_id = $responseOrder['order_id'];
			# Checks invoice ID is a valid invoice number or ends processing
			$invoiceid = checkCbInvoiceID($order_id, $GATEWAY["name"]);

			$transid = $responseOrder['trxId'];
			checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

			$fee = 0;
			$amount = $responseOrder['amountReceived'];
			addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice
			logTransaction($GATEWAY["name"], $responseOrder, "Over-Payment");
			break;
		case 'processing':
			$order_id = $responseOrder['order_id'];
			# Checks invoice ID is a valid invoice number or ends processing
			$invoiceid = checkCbInvoiceID($order_id, $GATEWAY["name"]);

			$transid = $responseOrder['trxId'];
			checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

			$fee = 0;
			$amount = $responseOrder['amountReceived'];
			addInvoicePayment($invoiceid, $transid, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice
			logTransaction($GATEWAY["name"], $responseOrder, "Partial Payment");
			break;		
	}
	 
}
