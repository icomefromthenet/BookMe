<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use DateTime;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to add a schedule rule
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CreateRuleCommand implements ValidationInterface, HasEventInterface
{

    /**
    * @var string repeat dsl for minute
    */
    protected $sRepeatMinute;
    
    /**
    * @var string repeat dsl for hour
    */
    protected $sRepeatHour;
    
    /**
    * @var string repeat dsl for day in week
    */
    protected $sRepeatDayofweek;
    
    /**
    * @var string repeat dsl for day in month
    */
    protected $sRepeatDayofmonth;
    
    /**
    * @var string repeat dsl for month
    */
    protected $sRepeatMonth;

    /**
     * @var integer the opening slot during the day
     */ 
    protected $iOpeningSlot;
    
    /**
     * @var integer the last slot during the day
     */ 
    protected $iClosingSlot;
    
    /**
     * @var DateTime a date on which to stop repeating the rule
     */ 
    protected $oEndtAtDate;
    
    /**
     * @var DateTime a date on which to start repeating the rule
     */ 
    protected $oStartFromDate;
    
    /**
     * @var integer the database id of the rule_type
     */ 
    protected $iRuleTypeDatabaseId;
    
    /**
     * @var integer the database id of the rule once created
     */ 
    protected $iRuleDatabaseId;
    
    /**
     * @var integer the databse id of the timeslot
     */ 
    protected $iTimeslotDatabaseId;
    
    /**
     * @boolean if this rule is single day or repeat with multiple days
     */ 
    protected $bIsSingleDay;
    
    public function __construct(DateTime $oStartFromDate
                              , DateTime $oEndtAtDate
                              , $iRuleTypeDatabaseId
                              , $iTimeslotDatabaseId
                              , $iOpeningSlot
                              , $iClosingSlot
                              , $sRepeatDayofweek
                              , $sRepeatDayofmonth
                              , $sRepeatMonth
                              , $bIsSingleDay = false)
    {
        $this->sRepeatMinute        = '*';
        $this->sRepeatHour          = '*';
        $this->sRepeatDayofweek     = $sRepeatDayofweek;
        $this->sRepeatDayofmonth    = $sRepeatDayofmonth;
        $this->sRepeatMonth         = $sRepeatMonth; 
        
        
        $this->oStartFromDate       = $oStartFromDate;
        $this->oEndtAtDate          = $oEndtAtDate;
        $this->iRuleTypeDatabaseId  = $iRuleTypeDatabaseId;
        $this->iOpeningSlot         = $iOpeningSlot;
        $this->iClosingSlot         = $iClosingSlot;
        $this->iTimeslotDatabaseId  = $iTimeslotDatabaseId;
        $this->bIsSingleDay         = $bIsSingleDay;
    }
  
  
    /**
    * Return the rule type database id
    * 
    * @return integer 
    */ 
    public function getRuleTypeId()
    {
        return $this->iRuleTypeDatabaseId;
    }
  
   /**
    * Return the rule database id
    * 
    * @return integer 
    */ 
    public function getRuleId()
    {
        return $this->iRuleDatabaseId;
    }
    
    /**
     * Return the database id of the timeslot this 
     * rule belongs too.
     * 
     * @return integer
     */ 
    public function getTimeSlotId()
    {
        return $this->iTimeslotDatabaseId;
    }
  
  
    /**
     * Set the rule database id once created
     * 
     * @return void
     * @param integer   $iRuleDatabaseId
     */ 
    public function setRuleId($iRuleDatabaseId)
    {
        $this->iRuleDatabaseId = $iRuleDatabaseId;
    }
  
    /**
     * Return the repeat minute rule
     * 
     * @return string
     */ 
    public function getRuleRepeatMinute()
    {
        return $this->sRepeatMinute;
    }
    
    /**
     * Return the repeat hour rule
     * 
     * @return string
     */ 
    public function getRuleRepeatHour()
    {
        return $this->sRepeatHour;
    }
    
    /**
     * Return the repeat day of week rule
     * 
     * @return string
     */ 
    public function getRuleRepeatDayOfWeek()
    {
        return $this->sRepeatDayofweek;
    }
    
    /**
     * Return the repeat day month rule
     * 
     * @return string
     */ 
    public function getRuleRepeatDayOfMonth()
    {
        return $this->sRepeatDayofmonth;
    }
    
    /**
     * Return the repeat month rule
     * 
     * @return string
     */ 
    public function getRuleRepeatMonth()
    {
        return $this->sRepeatMonth;
    }
    
    /**
     * Return the opening slot minute
     * 
     * @return integer
     */ 
    public function getOpeningSlot()
    {
        return $this->iOpeningSlot;
    }
    
    /**
     * Return the closing slot minute
     * 
     * @return integer
     */ 
    public function getClosingSlot()
    {
        return $this->iClosingSlot;
    }
    
    /**
     * First day to start the repat rule
     * 
     * @return DateTime
     */ 
    public function getCalendarStart()
    {
        return $this->oStartFromDate;
    }
    
    /**
     * Last day to repat this rule on
     * 
     * @return DateTime
     */ 
    public function getCalendarEnd()
    {
        return $this->oEndtAtDate;
    }
    
    /**
     * Return the flag the determines if series is repeat
     * on multi day
     * 
     * @return boolean true if rule repeated
     */ 
    public function getIsSingleDay()
    {
        return $this->bIsSingleDay;   
    }
  
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'integer' => [
                ['rule_type_id'],['opening_slot'],['closing_slot'],['timeslot_id']
            ]
            ,'min' => [
               ['rule_type_id',1],['opening_slot',0],['timeslot_id',1]
            ]
            ,'max' => [
                ['closing_slot',(60*24)]
            ]
            ,'required' => [
               ['rule_type_id'],['repeat_dayofweek'],['repeat_dayofmonth'],['repeat_month'],['timeslot_id']
            ]
            ,'calendarSameYear' => [
                ['end_at','start_from']
            ]
            ,'boolean' => [
                ['is_single_day']    
            ]
            
        ];
    }
  
  
    public function getData()
    {
        return [
            'rule_type_id'      => $this->iRuleTypeDatabaseId,
            'start_from'        => $this->oStartFromDate,
            'end_at'            => $this->oEndtAtDate,
            'repeat_minute'     => $this->sRepeatMinute,
            'repeat_hour'       => $this->sRepeatHour,
            'repeat_dayofweek'  => $this->sRepeatDayofweek,
            'repeat_dayofmonth' => $this->sRepeatDayofmonth,
            'repeat_month'      => $this->sRepeatMonth,
            'opening_slot'      => $this->iOpeningSlot,
            'closing_slot'      => $this->iClosingSlot,
            'timeslot_id'       => $this->iTimeslotDatabaseId,
            'is_single_day'     => $this->bIsSingleDay,
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
        return BookMeEvents::RULE_CREATE;  
    }
  
  
}
/* End of Clas */