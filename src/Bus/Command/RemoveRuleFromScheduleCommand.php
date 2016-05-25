<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to remove a rule from a members schedule
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RemoveRuleFromScheduleCommand implements  ValidationInterface
{

 
    /**
    * @var integer the schedule database id
    */ 
    protected $iScheduleDatabaseId;
    
    /**
     * @var integer the database id of the rule
     */ 
    protected $iRuleDatabaseId;
    
   
    
    public function __construct($iScheduleDatabaseId, $iRuleDatabaseId)
    {
        $this->iScheduleDatabaseId = $iScheduleDatabaseId;
        $this->iRuleDatabaseId     = $iRuleDatabaseId;
      
    }
    
    
    /**
    * Fetch the database id of the rule to link on
    * 
    * @access public
    */ 
    public function getRuleId()
    {
      return $this->iRuleDatabaseId;
    }
    
  
    /**
     * Fetches the database id of the schedule to use
     * 
     * @access public
     */ 
    public function getScheduleId()
    {
        return $this->iScheduleDatabaseId;
    }
    
    
    
    
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'integer' => [
                ['rule_id'], ['schedule_id']
            ]
            ,'min' => [
                ['rule_id',1], ['schedule_id',1]
            ]
            ,'required' => [
                ['rule_id'], ['schedule_id']
            ]
           
        ];
    }
    
    
    public function getData()
    {
        return [
            'rule_id' => $this->iRuleDatabaseId,
            'schedule_id' => $this->iScheduleDatabaseId,
        ];
    }
    
    
   

}
/* End of Clas */