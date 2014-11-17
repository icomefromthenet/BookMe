<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use \PHPUnit_Framework_TestCase;

class BasicTest extends PHPUnit_Framework_TestCase
{
    
    /**
      *  @var IComeFromTheNet\BookMe\BookMeService
      */
    public $project;

    
    //  ----------------------------------------------------------------------------
    
    /**
      *  Class Constructor 
      */
    public function __construct()
    {
        $this->preserveGlobalState = true;
        $this->runTestInSeperateProcess = false;
        
    }


    public function setUp()
    {
        
    }


    public function tearDown()
    {

    }

    //  ----------------------------------------------------------------------------
    
    /**
      *  Will Fetch the project object
      *
      *  @return Faker\Project
      */
    public function getContainer()
    {
        if($this->project === null) {
            
            # truncate and setup the schema
            $doctrine = $this->getDoctrineConnection();
            $eventDispatcher = $this->getEventDispatcher();
            $log = $this->getLogger();
            
            # bootstrap the container            
            $this->project  = new BookMeService($doctrine,$log,$eventDispatcher);
        }
        
        return $this->project;
    }

    //  -------------------------------------------------------------------------
    
    
    /**
    * Gets a db connection to the test database
    *
    * @access public
    * @return \Doctrine\DBAL\Connection
    */
    protected function getDoctrineConnection()
    {
        $config = new \Doctrine\DBAL\Configuration();
            
        $connectionParams = array(
                'dbname' => $GLOBALS['DB_DBNAME'],
                'user' => $GLOBALS['DB_USER'],
                'password' => $GLOBALS['DB_PASSWD'],
                'host' => 'localhost',
                'driver' => 'pdo_mysql',
            );
        
        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }
    
    
    protected function getLogger()
    {
        $sysLog = new \Monolog\Handler\TestHandler();
        // Create the main logger of the app
        $logger = new \Monolog\Logger('error');
        $logger->pushHandler($sysLog);
        #assign the log to the project
        return $logger;
    }
    
    
    protected function getEventDispatcher()
    {
        return new \Symfony\Component\EventDispatcher\EventDispatcher();    
    }
    
   
    
}
/* End of File */