<?php

/* Database Config file */

return array (
  0 => 
  array (
    'type' => 'pdo_mysql',
    'schema' => 'c9',
    'user' => getenv('C9_USER'),
    'password' => null,
    'host' => getenv('IP'),
    'port' => 3306,
    'socket' => false,
    'path' => null,
    'memory' => null,
    'charset' => false,
    'connectionName' => 'DEVD',
    'migration_table' => 'migrations_data',
  ),
);


/* End of Config File */
