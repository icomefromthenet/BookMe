<?php
namespace IComeFromTheNet\BookMe;

use DateTime;
use Pimple\Container;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use League\Tactician\CommandBus;
use League\Tactician\Handler\Locator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use Bezdomni\Tactician\Pimple\PimpleLocator;

use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;


use IComeFromTheNet\BookMe\Bus\Handler\CalAddYearHandler;


use IComeFromTheNet\BookMe\Bus\Middleware\ValidatePropMiddleware;
use IComeFromTheNet\BookMe\Bus\Middleware\ExceptionWrapperMiddleware;

/**
 * Book Me DI Container
 * 
 * Your Database Admin must allow user variables for this code to function.
 *
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
class BookMeContainer extends Container
{
    
    
    
    
    

    public function __construct(Connection $dbal,LoggerInterface $logger,EventDispatcherInterface $dispatcher, DateTime $oNow)
    {
        $this['database'] = $dbal;
        $this['logger']   = $logger;
        $this['eventDispatcher'] = $dispatcher;
        $this['booted'] = false;
        $this['now'] = $oNow;
        
    }
    
    /**
     * Gets the Service ready for operation.
     * 
     * 1. Build Dependency Graph
     * 2. Wire up event handlers
     * 
     * Will only boot once.
     * 
     * @return $this;
     * @access public
     */ 
    public function boot(array $aTableNames = array())
    {
        if(false === $this['booted']) {
        
        
            # default table name map
            $this['tableMap'] = array_merge(array(
                'bm_calendar' => 'bm_calendar'
                
                
            ),$aTableNames);
        
        
        
            # Command Bus Handlers
            
            $this['handlers.cal.addyear'] = function($c) use ($aFinalTableNames) {
                return new  CalAddYearHandler($c->getTableMap(), $c->getDatabaseAdapter()); 
                
            };

            
            
            # Command Bus
            
            $this['commandBus'] = function($c){
                
                $aLocatorMap = [
                    CalAddYearCommand::class => 'handlers.cal.addyear',
                ];
        
             
                // Create the Middleware that loads the commands
             
                $oCommandNamingExtractor = new ClassNameExtractor();
                $oCommandLoadingLocator  = new PimpleLocator($c, $aLocatorMap);
                $oCommandNameInflector   = new HandleInflector();
                    
                $oCommandMiddleware      = new CommandHandlerMiddleware($oCommandNamingExtractor,$oCommandLoadingLocator,$oCommandNameInflector);
                
                // Create exrta Middleware 
 
                $oLockingMiddleware     = new LockingMiddleware();
                $oValdiationMiddleware  = new ValidatePropMiddleware();
                $oExceptionMiddleware   = new ExceptionWrapperMiddleware();
        
                // create the command bus
        
                $oCommandBus = new CommandBus([
                            $oExceptionMiddleware,
                            $oLockingMiddleware,
                            $oValdiationMiddleware,
                            $oCommandMiddleware
                ]);
                
                return $oCommandBus;
                
            };
            
        }
        
        
        return $this;
        
    }
    
    
    
    

    
    
    
    //-------------------------------------------------------------------
    # Internal Services
    
    
    /**
     * Returns this command bus
     * 
     * @return League\Tactician\CommandBus
     */ 
    public function getCommandBus()
    {
        return $this['commandBus'];
    }
    
    
    public function getTableMap()
    {
        return $this['tableMap'];
    }
    
    //--------------------------------------------------------------------
    # External Dependecies
    
    /**
     * Loads the doctrine database
     *
     * @return Doctrine\DBAL\Connection
     */
    public function getDatabase()
    {
        return $this['database'];
    }
    
    /**
     * Loads the doctrine database
     *
     * @return Doctrine\DBAL\Connection
     */
    public function getDatabaseAdapter()
    {
        return $this['database'];
    }
    
    
    /**
     * Loads the application log
     *
     * @return Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this['logger'];
    }
    
    /**
     * Loads the application log
     *
     * @return Symfony\Component\EventDispatcher\EventDispatcherInterface;
     */
    public function getEventDispatcher()
    {
        return $this['eventDispatcher'];
    }
    
    /**
     * Return the assigned processing date ie NOW()
     * 
     * return DateTime
     */ 
    public function getNow()
    {
        return $this['now'];
    }
    
    
}
/* End of File */