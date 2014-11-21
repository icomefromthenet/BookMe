<?php
namespace IComeFromTheNet\BookMe\Events;
use Symfony\Component\EventDispatcher\Event;

/**
 * Common Event
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CommonEvent extends Event 
{
    protected $entityID;
    
    
    public function getEntityID()
    {
        return $this->entityID;
    }
    
    public function __construct($entityID)
    {
        $this->entityID = $entityID;
    }
    
}
/* End of File */