<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Bus\Exception\RuleException;
use IComeFromTheNet\BookMe\Cron\CronToQuery;

/**
 * Used to save a new rule.
 * 
 * 1. Save rule to the database
 * 2. Call CronToQuery to build a rule slot series.
 * 
 * This does NOT Apply a rule to a schedule.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CreateRuleHandler 
{
    
    /**
     * @var array   a map internal table names to external names
     */ 
    protected $aTableNames;
    
    /**
     * @var Doctrine\DBAL\Connection    the database adpater
     */ 
    protected $oDatabaseAdapter;
    
    /**
     * @var IComeFromTheNet\BookMe\Cron\CronToQuery
     */ 
    protected $oCronToQuery;
    
    /**
     * Save a rule to the database
     * 
     * @return void
     * @throws RuleException if unable to save the rule
     */ 
    protected function saveRule(CreateRuleCommand $oCommand)
    {
        $oDatabase          = $this->oDatabaseAdapter;
        $sRuleTableName     = $this->aTableNames['bm_rule'];
        
        
        try {
            
            $aBind = [
                ':iRuleTypeId'      => $oCommand->getRuleTypeId(),
                ':repeatMinute'     => $oCommand->getRuleRepeatMinute(),
                ':repeatHour'       => $oCommand->getRuleRepeatHour(),
                ':repeatDayOfWeek'  => $oCommand->getRuleRepeatDayOfWeek(),
                ':repeatDayOfMonth' => $oCommand->getRuleRepeatDayOfMonth(),
                ':repeatMonth'      => $oCommand->getRuleRepeatMonth(),
                ':oStartFrom'       => $oCommand->getCalendarStart(),
                ':oEndAt'           => $oCommand->getCalendarEnd(),
                ':iTimeslotId'      => $oCommand->getTimeSlotId(),
                ':iOpenSlot'        => $oCommand->getOpeningSlot(), 
                ':iCloseSlot'       => $oCommand->getClosingSlot(),
                ':iCalYear'         => $oCommand->getCalendarStart()->format('Y'),
                ':bIsSingleDay'     => $oCommand->getIsSingleDay(),
            ];
            
            $aType = [
              ':iRuleTypeId'        => TYPE::INTEGER,
              ':repeatMinute'       => TYPE::STRING,
              ':repeatHour'         => TYPE::STRING,
              ':repeatDayOfWeek'    => TYPE::STRING,
              ':repeatDayOfMonth'   => TYPE::STRING,
              ':repeatMonth'        => TYPE::STRING,
              ':oStartFrom'         => TYPE::DATE,
              ':oEndAt'             => TYPE::DATE,
              ':iTimeslotId'        => TYPE::INTEGER,
              ':iOpenSlot'          => TYPE::INTEGER,
              ':iCloseSlot'         => TYPE::INTEGER,
              ':iCalYear'           => TYPE::INTEGER,
              ':bIsSingleDay'       => TYPE::BOOLEAN,
            ];
            
            $sSql  =" INSERT INTO $sRuleTableName (`rule_id`, `rule_type_id`, `repeat_minute`, `repeat_hour`, `repeat_dayofweek`, `repeat_dayofmonth`, `repeat_month`, `start_from`, `end_at`, `timeslot_id`, `open_slot`, `close_slot`, `cal_year`, `is_single_day`) ";
	        $sSql .=" VALUES (null, :iRuleTypeId, :repeatMinute, :repeatHour, :repeatDayOfWeek, :repeatDayOfMonth, :repeatMonth, :oStartFrom, :oEndAt, :iTimeslotId, :iOpenSlot, :iCloseSlot, :iCalYear, :bIsSingleDay )";   
	        
	        
	        $iAffectedRows = $oDatabase->executeUpdate($sSql, $aBind, $aType);
	        
	        if($iAffectedRows !== 1) {
	            throw RuleException::hasFailedToCreateNewRule($oCommand, null);
	        }
	        
	        $iRuleId = $oDatabase->lastInsertId();
	        
	        $oCommand->setRuleId($iRuleId);
	        
	    }
	    catch(DBALException $e) {
	        throw RuleException::hasFailedToCreateNewRule($oCommand, $e);
	    }
       
        
    }
    

    public function __construct(array $aTableNames, Connection $oDatabaseAdapter, CronToQuery $oCronToQuery)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        $this->oCronToQuery     = $oCronToQuery;
        
    }
    
    
    public function handle(CreateRuleCommand $oCommand)
    {
        // Save the Rule to the database
        
        $this->saveRule($oCommand);
        
        // Save the rule series
        
        $this->oCronToQuery->parse($oCommand);
        
        return true;
    }
     
    
}
/* End of File */