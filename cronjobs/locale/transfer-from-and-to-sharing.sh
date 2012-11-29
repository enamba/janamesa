#!/bin/bash

# @author Felix Haferkorn <haferkorn@lieferando.de>
# @since 27.10.2011
#

if [ $# -lt 1 ]; then
    echo "USAGE: transfer-from-and-to-sharing.sh <absolute Workspace-DIR>"
    exit
fi

WORKSPACE=$1
DATE=`date +%Y-%m-%d-%H-%M-%S`
CURRENTDIR='Aktuell'

mkdir /mnt/share-temporaer/OLD/$DATE
cp -R /mnt/share-temporaer/$CURRENTDIR/* /mnt/share-temporaer/OLD/$DATE/
cd $WORKSPACE/src/
git checkout master
git pull origin master
sh $WORKSPACE/src/bin/locale/gen.sh
rm -R /mnt/share-temporaer/$CURRENTDIR/*
cp -R $WORKSPACE/src/application/locales/* /mnt/share-temporaer/$CURRENTDIR/