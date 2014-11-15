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
        $container = $this->getContainer();
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
        if(self::$project === null) {
            
            # truncate and setup the schema
            $doctrine = $this->getDoctrineConnection();
            $eventDispatcher = $this->getEventDispatcher();
            $log = $this->getLogger();
            
            # build schema
            $sqlFile = realpath(__DIR__.'/../../database/create.sh');
            
            if(false === file_exists($sqlFile)) {
                $this->assertFalse(false,"The Database Create SQL file not found at $sqlFile");
            }
            
            $command = $sqlFile.' '.$GLOBALS['DB_DBNAME'] .' '.$GLOBALS['DB_USER'].' '.$GLOBALS['DB_PASSWD'];
            
            fwrite(STDOUT, 'Execute datbase build '.PHP_EOL);
            ob_start();
            system($command);
            fwrite(STDOUT, ob_get_contents().PHP_EOL);
            
            # execute install functions
            fwrite(STDOUT, 'Execute bm_install_run()'.PHP_EOL);
            $doctrine->exec('set @bm_debug = true;');
            $doctrine->exec('call bm_install_run()');
            
            # bootstrap the container            
            self::$project  = new BookMeService($doctrine,$log,$eventDispatcher);
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