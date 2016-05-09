<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use DateTime;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to add a new year to the calendar table
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CalAddYearCommand implements ValidationInterface, HasEventInterface
{

  /**
   * @var integer This is the number of calendar years to add 
   */
  protected $iYear;

  /**
   * @var DateTime starting cal year to add 
   */ 
  protected $oStartDate;
  
    
  public function __construct($iYears, DateTime $oStartDate = null)
  {
        $this->iYear = (integer) $iYears; 
        $this->oStartDate = $oStartDate;
  }
  
  
  /**
   * Return the number of calender years to add
   * 
   * @return integer 
   */ 
  public function getYears()
  {
    return $this->iYear;
  }
  
  /**
   * Return the starting year 
   * 
   * @return DateTime
   */ 
  public function getStartYear()
  {
    return $this->oStartDate;
  }
  
  //---------------------------------------------------------
  # validation interface
  
  
  public function getRules()
  {
      return [
        'integer' => [
            ['year']
        ]
        ,'min' => [
           ['year',1]
        ]
        ,'max' => [
           ['year',10]
        ]
      ];
  }
  
  
  public function getData()
  {
      return [
        'year' => $this->iYear
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
    return BookMeEvents::CALENDAR_ADD;  
  }
  
  
}
/* End of Clas */