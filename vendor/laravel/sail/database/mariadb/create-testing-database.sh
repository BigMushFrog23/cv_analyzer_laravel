#!/usr/bin/env bash

# Define the DB command once to avoid repeating the binary path and credentials
DB_CMD="/usr/bin/mariadb --user=root --password=$MYSQL_ROOT_PASSWORD"

$DB_CMD <<-EOSQL
    CREATE DATABASE IF NOT EXISTS testing;
EOSQL

# Use [[ ]] for safer, more modern conditional testing
if [[ -n "$MYSQL_USER" ]]; then
    $DB_CMD <<-EOSQL
        GRANT ALL PRIVILEGES ON \`testing%\`.* TO '$MYSQL_USER'@'%';
EOSQL
fi