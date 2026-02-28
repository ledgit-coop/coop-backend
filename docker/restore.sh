#!/bin/bash

printf "Backup path (backup.sql): "
read BACKUP_PATH
BACKUP_PATH="${BACKUP_PATH:=backup.sql}"

printf "Please provide a database (docma_db): "
read DATABASE
DATABASE="${DATABASE:=docma_db}"

printf "Please provide a username (db_admin): "
read -s USERNAME
USERNAME="${USERNAME:=db_admin}"
printf "\n"

printf "Please provide a password (password): "
read -s PASSWORD
PASSWORD="${PASSWORD:=password}"
printf "\n"

printf "Do you want to clean up the database? [y/n] (n): "
read REMOVE
REMOVE="${REMOVE:=n}"

printf "\n"

if [ "$REMOVE" != "${REMOVE#[Yy]}" ] ; then
    printf "Cleaning up the database...\n"
    docker exec -i docms_mysql mysql -u $USERNAME --password=$PASSWORD -e "DROP DATABASE IF EXISTS $DATABASE; CREATE DATABASE $DATABASE;"
    printf "\n"
fi

# restoring backup
printf "Restoring database...\n"
if cat $BACKUP_PATH | docker exec -i docms_mysql mysql -u root --password=$PASSWORD $DATABASE ; then
    printf "\n"
    printf "Successfully restored!\n"
else
    printf "\n"
    printf "Error occurred during restoration.\n"
fi
