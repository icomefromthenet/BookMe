<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\AssignRuleToScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Exception\RuleException;
use IComeFromTheNet\BookMe\Cron\CronToQuery;


/**
 * Used to save link a rule to a schedule
 * 
 * This handler will only save the link it will not refresh the schedule.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class AssignRuleToScheduleHandler
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
    
    
    public function handle(AssignRuleToScheduleCommand $oCommand)
    {
       
        $oDatabase              = $this->oDatabaseAdapter;
        $sRuleScheduleTableName = $this->aTableNames['bm_rule_schedule'];
        
        try {
            
            $aBind = [
                ':iRuleId'       => $oCommand->getRuleId(),
                ':iScheduleId'   => $oCommand->getScheduleId(),
                ':bIsRolllover'  => $oCommand->getRolloverFlag(),
            ];
            
            $aType = [
              ':iRuleId'        => TYPE::INTEGER,
              ':iScheduleId'    => TYPE::INTEGER,
              ':bIsRolllover'   => TYPE::BOOLEAN,
            ];
            
            $sSql  =" INSERT INTO $sRuleScheduleTableName (`rule_id`, `schedule_id`, `is_rollover`) ";
	        $sSql .=" VALUES (:iRuleId, :iScheduleId, :bIsRolllover)";   
	      
	        $iAffectedRows = $oDatabase->executeUpdate($sSql, $aBind, $aType);
	        
	        if($iAffectedRows !== 1) {
	            throw RuleException::hasFailedAssignRuleToSchedule($oCommand, null);
	        }
	        
        } catch (DBALException $e) {
            throw hasFailedAssignRuleToSchedule($oCommand,$e);
        }
	        
        return true;
    }
     
    
}
/* End of File */