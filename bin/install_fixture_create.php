<?php

use \DateTime;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Doctrine\DBAL\Schema\Schema;
use IComeFromTheNet\BookMe\BookMeContainer;

use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;


include __DIR__ .'/../vendor/autoload.php';
 
 
//-----------------------------------------------------
// Create the Datbase Connection
//
//-----------------------------------------------------

$config           = new \Doctrine\DBAL\Configuration();
$connectionParams = [];
          

$sFilePath = __DIR__ .'/../phpunit.xml';

if(false === is_file($sFilePath)) {
    echo 'php xml file not found';
    exit(1);
}

$xml = simplexml_load_file($sFilePath);

foreach ($xml->children() as $oElement) {
    if($oElement->getName() == 'php') {
        
        foreach ($oElement->children() as $oVars) {
            switch($oVars['name']) {
                case 'DEMO_DATABASE_USER' :
                   $connectionParams['user'] = getenv('C9_USER'); //$oVars['value'];
                break;
                case 'DEMO_DATABASE_PASSWORD':
                   $connectionParams['password'] = (string) $oVars['value'];
                break;
                case 'DEMO_DATABASE_SCHEMA':
                   $connectionParams['dbname'] = (string) $oVars['value'];
                break;
                case 'DEMO_DATABASE_PORT' :
                   $connectionParams['port']  = (string) $oVars['value'];
                break;
                case 'DEMO_DATABASE_HOST' :
                  $connectionParams['host']  = getenv('IP'); //$oVars['value'];
                break;
                case 'DEMO_DATABASE_TYPE' :
                  $connectionParams['driver']  = (string) $oVars['value'];
                break;
            }
        }        
    }
}


$oDatabase = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

//-----------------------------------------------------
// Create the Container
//
//-----------------------------------------------------


$oEvent = new EventDispatcher();

$oLogger = new Logger('test-test',array(new TestHandler()));

$oNow =  new DateTime();


$oContainer = new BookMeContainer($oDatabase,$oLogger,$oEvent,$oNow);
$oContainer->boot();
     
     
//-----------------------------------------------------
// Build Calendar
//
//-----------------------------------------------------

$oCalCommand = new CalAddYearCommand(1);

$oContainer->getCommandBus()->handle($oCalCommand);
     
     
//-----------------------------------------------------
// Build Timeslots
//
//-----------------------------------------------------     

$oSlotAddCommandA = new SlotAddCommand(5);

$oContainer->getCommandBus()->handle($oSlotAddCommandA);

$oSlotAddCommandB = new SlotAddCommand(10);

$oContainer->getCommandBus()->handle($oSlotAddCommandB);

$oSlotAddCommandC = new SlotAddCommand(15);

$oContainer->getCommandBus()->handle($oSlotAddCommandC);


$oSlotAddCommandD = new SlotAddCommand(20);

$oContainer->getCommandBus()->handle($oSlotAddCommandD);

