#!/bin/bash

# number of expected arg to the script
# {1} schema name
# {2} mysql user
# {3} mysql user password
# {4} test fule under database
EXPECTED_ARGS=4
E_BADARGS=65

# path to mysql cli client
MYSQL="$(which mysql)";

# script execute directory
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# test for expected args
if [ $# -ne $EXPECTED_ARGS ]
then
  echo "Usage: $0 dbname dbuser dbpass testfile_path";
  exit $E_BADARGS;
fi
 
# execute test sql file 
echo 'Execute Test';

$MYSQL -u ${2} -p${3} ${1}  < ${DIR}'/../database/'${4}'.mysql';


