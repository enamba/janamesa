#!/bin/bash

if [ $# -lt 2 ]; then
    echo "apply.sh database user (password) (hostname) (port)"
    exit 0
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST="localhost"
DB_PORT="3306"

if [ $# = 4 ]; then
    DB_HOST=$4
fi

if [ $# = 5 ]; then
    DB_HOST=$4
    DB_PORT=$5
fi

CUR_DIR=`dirname $0`;

for SCRIPT in $(ls ${CUR_DIR}/*.sql); do
    echo "Applying view: $SCRIPT"
    mysql -u $DB_USER -p$DB_PASS -h $DB_HOST -P $DB_PORT $DB_NAME < $SCRIPT
done
