<?php
namespace IComeFromTheNet\BookMe\Events;

use DateTime;
use IComeFromTheNet\BookMe\BookMeException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Maps the application events to the app logger
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class AppActivityLogHandler implements EventSubscriberInterface
{
    
    
    /**
     * @var AppUserInterface
     */ 
    protected $user;
    
   /**
    * @var AppLoggerInterface
    */ 
    protected $logger;
    
   
    
    //--------------------------------------------------------------------------
    # Constructor
    
    public function __construct(AppLoggerInterface $logger ,AppUserInterface $user) 
    {
        $this->logger   = $logger;
        $this->user     = $user;
    }
    
    
    //-------------------------------------------------------------------------
    # Event Handlers and EventSubscriberInterface
    
    public static function getSubscribedEvents()
    {
        return array(
            BookMeEvents::MemberRegistered => array('onMembershipRego',0) 
        );
    }
    
    public function onMembershipRego(Event $event)
    {
        
    }
    
    
    
    //--------------------------------------------------------------------------
    # Properties
    
  
    
    /**
     * Gets the user that booted the BookMe service
     * 
     * @access public
     * @return AppUserInterface
     */ 
    public function getAppUser()
    {
        return $this->user;
    }
    
    /**
     * Gets the app log writer
     * 
     * @return AppLoggerInterface
     */ 
    public function getLogger()
    {
        return $this->logger;
    }
    
}
/* End of Class */
