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



/**
 * @return array
 */
function bitshares_config()
{

    $configarray = array(
        "FriendlyName" => array(
            "Type" => "System",
            "Value"=>"Bitshares"
        ),
		"accountName" => array (
				"FriendlyName" => "BTS Account Name",
				"Type" => "text", # Text Box
				"Size" => "25", # Defines the Field Width
				"Description" => "ie: bobsmith",
				"Default" => "",
			),        
		"rpcUser" => array (
				"FriendlyName" => "RPC Username",
				"Type" => "text", # Text Box
				"Size" => "25", # Defines the Field Width
				"Description" => "Set this in your Bitshares config (--rpcuser command line)",
				"Default" => "",
			), 
		"rpcPass" => array (
				"FriendlyName" => "RPC Password",
				"Type" => "password", # PW Box
				"Size" => "25", # Defines the Field Width
				"Description" => "Set this in your Bitshares config (--rpcpassword command line)",
				"Default" => "",
			), 
		"rpcPort" => array (
				"FriendlyName" => "HTTP RPC Port",
				"Type" => "text", # Text Box
				"Size" => "8", # Defines the Field Width
				"Description" => "Set this in your Bitshares config (--httpport command line)",
				"Default" => "",
			), 			
		"demoMode" => array (
			"FriendlyName" => "Enable Demo Mode",
			"Type" => "yesno", # Yes/No Checkbox
			"Description" => "Demo mode allows you to pay for items in any asset ie: 100 BTS for items sold in $100 USD/EUR/GBP etc, do not use in real sites. Enable to demo/test plugin functionality",
		),                     
		"cronURL" => array (
				"FriendlyName" => "Cron Job's URL",
				"Type" => "text", # Text Box
				"Size" => "100", # Defines the Field Width
				"Description" => "Set a cron job to call this URL, to update order status",
				"Default" => "",
			) 
    );

    return $configarray;
}

/**
 * @param array $params
 *
 * @return string
 */
function bitshares_link($params)
{
    # Invoice Variables
    $invoiceid = $params['invoiceid'];

    # Client Variables
   
	$amount = $params['amount']; # Format: ##.##
    $currency = $params['currency']; # Currency Code

    # System Variables

    $systemurl = $params['systemurl'];

    $post = array(
        'invoiceId'     => $invoiceid,
        'systemURL'     => $systemurl,
        'currency'     => $currency,
        'amount'     => $amount
        
    );

    $form = '<form action="'.$systemurl.'/modules/gateways/bitshares/createinvoice.php" method="POST">';

    foreach ($post as $key => $value) {
        $form.= '<input type="hidden" name="'.$key.'" value = "'.$value.'" />';
    }

    $form.='<input type="submit" value="'.$params['langpaynow'].'" />';
    $form.='</form>';

    return $form;
}
