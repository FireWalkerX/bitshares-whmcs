bitshares-whmcs
======================

# Installation

1. Copy these files into your WHMCS root directory
2. Copy Bitshares Checkout (https://github.com/sidhujag/bitsharescheckout) files into your WHMCS root directory, overwrite any existing files.

# Configuration

1. Fill out config.php with appropriate information and configure Bitshares Checkout
    - See the readme at https://github.com/sidhujag/bitsharescheckout
2. Check that you have set your Domain and WHMCS System URL under whmcs/admin > Setup > General Settings
3. In the whmcs administration under Payment Gateways, find the bitshares extension from the dropdown and click Activate.


# Usage

When a shopping chooses the Bitshares payment method, they will be presented with an
order summary as the next step (prices are shown in whatever currency they've selected
for shopping).  They will be presented with a button called "Pay with Bitshares."  This
button takes the shopper to a Bitshares invoice by opening the Bitshares wallet.  Once payment is received, the invoice gets updated as "Paid".


## WHMCS Support

* [Homepage](https://www.whmcs.com/)
* [Documentation](http://docs.whmcs.com/Main_Page)
* [SupportForums](http://forum.whmcs.com/)

# Contribute

To contribute to this project, please fork and submit a pull request.

# License

The MIT License (MIT)

Copyright (c) 2011-2014 Bitshares

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
