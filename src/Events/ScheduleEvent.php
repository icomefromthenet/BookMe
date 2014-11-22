<?php 
namespace IComeFromTheNet\BookMe\Events;


use Symfony\Component\EventDispatcher\Event;

/**
 * Generic event for a addition/removal/retire of a member schedule
 *
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
class ScheduleEvent extends CommonEvent
{
    
    
    protected $timeslotID;
    
    protected $memberID;
    
    protected $openingDate;
    
    protected $closingDate;
    
    
    
    public function __construct($entityID)
    {
        parent::__construct($entityID);
    }

}
/* End of Class */