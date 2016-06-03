<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use DateTime;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to schedule a booking
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class TakeBookingCommand implements  HasEventInterface, ValidationInterface
{

    /**
     * @var integer bookings database id once scheduled
     */ 
    protected $iBookingDatabaseId;

    /**
     * @var integer is the schedule instance to join
     */ 
    protected $iScheduleId;
    
    /**
     * @var Datetime the opening slot in the schedule
     */ 
    protected $oOpeningSlot;
    
    /**
     * @var Datetime the closing slot in the schedule
     */ 
    protected $oClosingSlot;
    
    /**
     * @var Number of max bookings per calendar day
     */ 
    protected $iMaxBookings;
    
    
    public function __construct($iScheduleId, DateTime $oOpeningSlot, DateTime $oClosingSlot, $iMaxBookings = null)
    {
        $this->iScheduleId       = $iScheduleId;
        $this->oOpeningSlot      = $oOpeningSlot;
        $this->oClosingSlot      = $oClosingSlot;
        $this->iMaxBookings      = $iMaxBookings;
        
    }
    
    /**
     * Fetches the opening slot in this booking
     * 
     * @access public
     * @return DateTime     
     */ 
    public function getOpeningSlot()
    {
        return $this->oOpeningSlot;
    }
    
    
    /**
     * Fetches theclosing slot in this booking
     * 
     * @access public
     * @return Datetime 
     */ 
    public function getClosingSlot()
    {
        return $this->oClosingSlot;
    }
    
   
    
    /**
     * Fetches the database id of the schedule to use
     * 
     * @access public
     */ 
    public function getScheduleId()
    {
        return $this->iScheduleId;
    }
    
     /**
     * Fetches the max number of allowed bookings if check required
     * 
     * @access public
     */ 
    public function getMaxBookings()
    {
        return $this->iMaxBookings;
    }
    
    /**
     * Sets the database id of the booking once sucessfuly taken
     * 
     * @access public
     * @param integer   $iBookingId
     */ 
    public function setBoookingId($iBookingId)
    {
        $this->iBookingDatabaseId = $iBookingId;
    }
    
    /**
     * Fetches the database id of the booking once sucessfuly taken
     * 
     * @access public
     */ 
    public function getBookingId()
    {
        return $this->iBookingDatabaseId;
    }
    
    
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'integer' => [
                ['schedule_id']
            ]
            ,'min' => [
                ['schedule_id',1]
            ]
            ,'required' => [
                ['schedule_id'], ['opening_slot'], ['closing_slot']
            ]
        ];
    }
    
    
    public function getData()
    {
        return [
            'opening_slot' => $this->oOpeningSlot,
            'closing_slot' => $this->oClosingSlot,
            'schedule_id' => $this->iScheduleId,
        ];
    }
    
    //----------------------------------------------------------------
    # Has Event Interface
    
    public function getEvent()
    {
      return new CommandEvent($this);
    }
    
    
    public function getEventName()
    {
        return BookMeEvents::BOOKING_TAKEN;  
    }
    

}
/* End of Clas */