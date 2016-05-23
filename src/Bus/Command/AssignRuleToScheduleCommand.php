<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to add a new member to a team
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class AssignRuleToScheduleCommand implements  ValidationInterface
{

 
    /**
    * @var integer the schedule database id
    */ 
    protected $iScheduleDatabaseId;
    
    /**
     * @var integer the database id of the rule
     */ 
    protected $iRuleDatabaseId;
    
    /**
     * @var boolean if this rule should be rolled over in near year
     */ 
    protected $bIsRollover;
    
    
    
    public function __construct($iScheduleDatabaseId, $iRuleDatabaseId, $bIsRollover)
    {
        $this->iScheduleDatabaseId = $iScheduleDatabaseId;
        $this->iRuleDatabaseId     = $iRuleDatabaseId;
        $this->bIsRollover         = $bIsRollover;

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
    
    
    /**
     * Fetch the rollover status to be assigned
     * 
     * @return boolean 
     */ 
    public function getRolloverFlag()
    {
        return $this->bIsRollover;
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
            ,'boolean' => [
                ['is_rollover']
            ]
        ];
    }
    
    
    public function getData()
    {
        return [
            'rule_id' => $this->iRuleDatabaseId,
            'schedule_id' => $this->iScheduleDatabaseId,
            'is_rollover' => $this->bIsRollover,
        ];
    }
    
    
   

}
/* End of Clas */