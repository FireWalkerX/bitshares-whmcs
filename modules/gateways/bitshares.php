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
        'code'     => $currency,
        'amount'     => $amount
    );

    $form = '<form action="'.$systemurl.'/modules/gateways/bitshares/redirect2bitshares.php" method="POST">';

    foreach ($post as $key => $value) {
        $form.= '<input type="hidden" name="'.$key.'" value = "'.$value.'" />';
    }

    $form.='<input type="submit" value="'.$params['langpaynow'].'" />';
    $form.='</form>';

    return $form;
}
