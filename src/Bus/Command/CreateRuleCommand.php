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
    
    
    
    
    public function __construct(DateTime $oStartFromDate, DateTime $oEndtAtDate, $iRuleTypeDatabaseId,   $sRepeatMinute, $sRepeatHour, $sRepeatDayofweek, $sRepeatDayofmonth, $sRepeatMonth)
    {
        $this->sRepeatMinute        = $sRepeatMinute;
        $this->sRepeatHour          = $sRepeatHour;
        $this->sRepeatDayofweek     = $sRepeatDayofweek;
        $this->sRepeatDayofmonth    = $sRepeatDayofmonth;
        $this->sRepeatMonth         = $sRepeatMonth; 
        
        $this->oStartFromDate       = $oStartFromDate;
        $this->oEndtAtDate          = $oEndtAtDate;
        $this->iRuleTypeDatabaseId  = $iRuleTypeDatabaseId;
    
    }
  
  
    /**
    * Return the rule database id
    * 
    * @return integer 
    */ 
    public function getRuleId()
    {
        return $this->iRuleTypeDatabaseId;
    }
  
    /**
     * Set the rule database id once created
     * 
     * @return void
     * @param integer   $iRuleTypeDatabaseId
     */ 
    public function setRuleId($iRuleTypeDatabaseId)
    {
        $this->iRuleTypeDatabaseId = $iRuleTypeDatabaseId;
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
    
  
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'integer' => [
                ['rule_type_id']
            ]
            ,'min' => [
               ['rule_type_id',1]
            ]
            ,'required' => [
               ['rule_type_id'],['start_from'],['end_at']
            ]
            ,'dateAfter' => 
            ['end_at',$this->oStartFromDate]
            
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