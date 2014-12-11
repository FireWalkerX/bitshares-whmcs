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

include '../../../dbconnect.php';
include '../../../includes/functions.php';
include '../../../includes/gatewayfunctions.php';
include '../../../includes/invoicefunctions.php';
require 'bts_lib.php';

$gatewaymodule = "bitshares";
$GATEWAY = getGatewayVariables($gatewaymodule);

// get invoice
$invoiceId = (int) $_POST['invoiceId'];
$price     = $currency = false;
$result    = mysql_query("SELECT tblinvoices.total, tblinvoices.status, tblcurrencies.code FROM tblinvoices, tblclients, tblcurrencies where tblinvoices.userid = tblclients.id and tblclients.currency = tblcurrencies.id and tblinvoices.id=$invoiceId");
$data      = mysql_fetch_assoc($result);

if (!$data) {
    error_log('no invoice found for invoice id'.$invoiceId);
    die("Invalid invoice");
}
$amount    = $_POST['amount'];
$price    = $data['total'];
$currency = $_POST['currency'];
$status   = $data['status'];

if ($data['code'] != $currency) {
    error_log("Currency doesn't match invoice order.  Symbol: ".$currency);
    die('bad invoice currency');
}
if ($status != 'Unpaid') {
    error_log("Invoice status must be Unpaid.  Status: ".$status);
    die('bad invoice status');
}

$response = btsCreateInvoice($GATEWAY['accountName'], $invoiceId, $amount, $price, $currency);
if(array_key_exists('error', $response))
{
    error_log($response['error']);
    die("bitshares invoice error");
}
else {
    header("Location: ".$response['url']);
}

