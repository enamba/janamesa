#!/bin/bash

#first we call our php script to regenerate all plz
#php ringe.php

if [ -f './ftp.csv' ]
then
    cat ./ftp.csv | while read line; do
        DOMAIN=$(echo $line | awk -F';' '{print $1}' | sed -e 's/ //g')
        HOST=$(echo $line | awk -F';' '{print $2}' | sed -e 's/ //g')
        USER=$(echo $line | awk -F';' '{print $4}' | sed -e 's/ //g')
        PASS=$(echo $line | awk -F';' '{print $5}' | sed -e 's/ //g')

        DIR=`echo "${DOMAIN}" | sed -e 's/[.]/-/g'`
        INDEX=`echo "${DOMAIN}" | sed -e 's/[.]/_/g'`
        
        if [ ! -f "./index/${INDEX}.html" ];then
            echo "COULD NOT FIND INDEX FILE FOR $DOMAIN in ./index/${INDEX}.html"
        else
            echo "starting to update content for domain ${DOMAIN}..."
            #uploading using ncftp
            #ncftpget -DD -u $USER -p $PASS $HOST /tmp/ /html/index.html
            cp ./index/${INDEX}.html /tmp/index.html
            ncftpput -R -u $USER -p $PASS $HOST /html/${DIR}/ /tmp/index.html
            ncftpput -R -u $USER -p $PASS $HOST /html/${DIR}/ ./templates/standard/impressum.htm
            ncftpput -R -u $USER -p $PASS $HOST /html/${DIR}/ ./style
            ncftpput -R -u $USER -p $PASS $HOST /html/${DIR}/ ./plz/*
            ncftpput -R -u $USER -p $PASS $HOST /html/${DIR}/ ./images
        fi

    done
fi
