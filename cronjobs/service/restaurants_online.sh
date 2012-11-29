#!/bin/sh


DIR=`dirname $0`

#echo $DIR

php $DIR/restaurants_online.php -e production
php $DIR/restaurants_online_old.php -e production