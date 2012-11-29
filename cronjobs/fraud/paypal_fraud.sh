#!/bin/sh


DIR=`dirname $0`

#echo $DIR

php $DIR/refund_paypal.php -e production
php $DIR/add_to_whitelist.php -e production