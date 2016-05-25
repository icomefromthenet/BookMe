<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RemoveRuleFromScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Exception\RuleException;
use IComeFromTheNet\BookMe\Cron\CronToQuery;


/**
 * Used to unlink a rule from schedule
 * 
 * This handler will only remove the link relationship it will not refresh the schedule.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RemoveRuleFromScheduleHandler
{
    
    /**
     * @var array   a map internal table names to external names
     */ 
    protected $aTableNames;
    
    /**
     * @var Doctrine\DBAL\Connection    the database adpater
     */ 
    protected $oDatabaseAdapter;
    
    

    public function __construct(array $aTableNames, Connection $oDatabaseAdapter)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
      
    }
    
    
    public function handle(RemoveRuleFromScheduleCommand $oCommand)
    {
       
        $oDatabase              = $this->oDatabaseAdapter;
        $sRuleScheduleTableName = $this->aTableNames['bm_rule_schedule'];
        
        try {
            
            $aBind = [
                ':iRuleId'       => $oCommand->getRuleId(),
                ':iScheduleId'   => $oCommand->getScheduleId(),
            ];
            
            $aType = [
              ':iRuleId'        => TYPE::INTEGER,
              ':iScheduleId'    => TYPE::INTEGER,
            ];
            
            $sSql  =" DELETE FROM $sRuleScheduleTableName ";
	        $sSql .=" WHERE `rule_id` = :iRuleId AND `schedule_id` = :iScheduleId";   
	      
	        $iAffectedRows = $oDatabase->executeUpdate($sSql, $aBind, $aType);
	        
	        if($iAffectedRows !== 1) {
	            throw RuleException::hasFailedRemoveRuleFromSchedule($oCommand, null);
	        }
	        
        } catch (DBALException $e) {
            throw hasFailedRemoveRuleFromSchedule($oCommand,$e);
        }
	        
        return true;
    }
     
    
}
/* End of File */