#!/usr/bin/env bash

# Constant for the database command to avoid string duplication
# This allows you to update credentials in one place
DB_EXEC="mysql --user=root --password=$MYSQL_ROOT_PASSWORD"

# Create the database
$DB_EXEC <<-EOSQL
    CREATE DATABASE IF NOT EXISTS testing;
EOSQL

# Use [[ for safer testing. It handles empty variables and 
# special characters better than the old [ syntax.
if [[ -n "$MYSQL_USER" ]]; then
    $DB_EXEC <<-EOSQL
        GRANT ALL PRIVILEGES ON \`testing%\`.* TO '$MYSQL_USER'@'%';
EOSQL
fi