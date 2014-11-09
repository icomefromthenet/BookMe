<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use \PHPUnit_Framework_TestCase;

class BasicTest extends PHPUnit_Framework_TestCase
{
    
    /**
      *  @var IComeFromTheNet\BookMe\BookMeService
      */
    public static $project;

    
    //  ----------------------------------------------------------------------------
    
    /**
      *  Class Constructor 
      */
    public function __construct()
    {
        $this->preserveGlobalState = false;
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
    public function getProject()
    {
        if(self::$project === null) {
            $boot    = new BookMeService($this->getDoctrineConnection(),$this->getLogger());
            self::$project->boot();
        }
        
        return self::$project;
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
        if(self::$doctrine_connection === null) {
        
            $config = new \Doctrine\DBAL\Configuration();
            
            $connectionParams = array(
                'dbname' => $GLOBALS['DB_DBNAME'],
                'user' => $GLOBALS['DB_USER'],
                'password' => $GLOBALS['DB_PASSWD'],
                'host' => 'localhost',
                'driver' => 'pdo_mysql',
            );
        
           self::$doctrine_connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        }
        
        return self::$doctrine_connection;
        
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