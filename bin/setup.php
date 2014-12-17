<?php
# include composer autoloader
include __DIR__ .'/../vendor/autoload.php';

# setup the datbase and execute the install

# build schema
$sqlFile = realpath(__DIR__.'/../database/create.sh');
            
if(false === file_exists($sqlFile)) {
    throw new \Exception("The Database Create SQL file not found at $sqlFile");
}
            
$command = $sqlFile.' '.$GLOBALS['DB_DBNAME'] .' '.$GLOBALS['DB_USER'].' '.$GLOBALS['DB_PASSWD'];

ob_start();            
fwrite(STDOUT, 'Execute datbase build '.PHP_EOL);
system($command);
fwrite(STDOUT, ob_get_contents().PHP_EOL);
ob_end_clean();          
          
            
# execute install functions
fwrite(STDOUT, 'Execute bm_install_run()'.PHP_EOL);

$config = new \Doctrine\DBAL\Configuration();
            
$connectionParams = array(
                'dbname' => $GLOBALS['DB_DBNAME'],
                'user' => $GLOBALS['DB_USER'],
                'password' => $GLOBALS['DB_PASSWD'],
                'host' => 'localhost',
                'driver' => 'pdo_mysql'
            );
        
$doctrine = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
$doctrine->exec('set @bm_debug = true;');
$doctrine->executeQuery('call bm_install_run(?)',array($GLOBALS['CAL_LENGTH']));

# execute test data insert must be done after insert run
$sqlFile = realpath(__DIR__.'/../database/data.sh');
     
if(false === file_exists($sqlFile)) {
    throw new \Exception("The Database Create SQL file not found at $sqlFile");
}
            
$command = $sqlFile.' '.$GLOBALS['DB_DBNAME'] .' '.$GLOBALS['DB_USER'].' '.$GLOBALS['DB_PASSWD'];

ob_start();
fwrite(STDOUT, 'Execute test data '.PHP_EOL);
system($command);
fwrite(STDOUT, ob_get_contents().PHP_EOL);
ob_end_clean();