<?php
namespace IComeFromTheNet\BookMe;

use Pimple\Container;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Book Me Service and DI Container
 *
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
class BookMeService extends Container
{
    
    
    public function __construct(Connection $dbal,LoggerInterface $logger,EventDispatcherInterface $dispatcher)
    {
        $this['database'] = $dbal;
        $this['logger']   = $logger;
        $this['eventDispatcher'] = $dispatcher;
        
    }
    
    //----------------------------------------
    //
    //
    //
    //----------------------------------------
    
    
    public function registerMembership()
    {
        
        
    }
    
    
    public function registerSchedule()
    {
        
        
    }
    
    public function createScheduleGroup()
    {
        
    }
    
    
    public function retireSchedule()
    {
        
        
    }
    
    public function retireScheduleGroup()
    {
        
    }

    //----------------------------------------
    //
    //
    //
    //----------------------------------------

    
    public function addScheduleGroupIncludeRule()
    {
        
    }
    
    public function retireScheduleGroupIncludeRule()
    {
        
    }
    
    public function addScheduleGroupExcludeRule()
    {
        
    }
    
    public function retireScheduleGroupExcludeRule()
    {
        
    }
    
    public function addScheduleIncludeRule()
    {
        
    }
    
    public function retireSchduleIncludeRule()
    {
        
    }
    
    public function addScheduleExcludeRule()
    {
        
    }
    
    public function retireSchduleExcludeRule()
    {
        
    }
    
    
    //----------------------------------------
    //
    //
    //
    //----------------------------------------

    
    public function loadSchedule()
    {
        
    }
    
    
    public function loadGroupSchedule()
    {
        
    }
    
    
    public function makeBooking()
    {
        
    }
    
    
    public function cancelBooking()
    {
        
        
    }

    public function loadConflictBookings()
    {
        
    }
    
    public function executeConflictBookingCheck()
    {
        
        
    }
    
    //----------------------------------------
    //
    //
    //
    //----------------------------------------

    
    public function loadBookingHistory()
    {
        
        
    }
    
    
    public function loadMembershipRegisterationHistory()
    {
        
    }
    
    
    public function loadScheduleRegistrationHistory()
    {
        
    }
    
    
    public function loadExclusionRulesHistory()
    {
        
    }
    
    public function loadInclusionRulesHistory()
    {
        
    }
    
    //----------------------------------------
    // External Services Properties
    // 1. Database
    // 2. Application Logger
    // 3. Event Dispatcher
    //----------------------------------------
    
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
    
    
}
/* End of File */