<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RolloverRulesCommand;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Bus\Exception\RuleException;
use IComeFromTheNet\BookMe\Cron\CronToQuery;


/**
 * Used to rollover rules into the new year
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RolloverRulesHandler 
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
   
   
   
    protected function rolloverRules(RolloverRulesCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $iNextCalYear           = $oCommand->getNextCalendarYear();
        $iCalYear               = $iNextCalYear - 1;
       
        $sRuleScheduleTable     = $this->aTableNames['bm_rule_schedule'];  
        $sRuleTable             = $this->aTableNames['bm_rule']; 
        $sScheduleTable         = $this->aTableNames['bm_schedule'];
        
        $sSql = '';
        $aSql = [];
        
        
        // This assumes that the schedules that have been created in this new calendar year already have their is_carryover flag nulled by the rollover schedule routine
        // Where only looking for rules that require a rollover and belong to a schedule in last calendar year that was rolledover in earlier operation. 
        $aSql[] = " INSERT INTO $sRuleTable (rule_id, rule_type_id, timeslot_id, repeat_minute, repeat_hour, repeat_dayofweek, repeat_dayofmonth, repeat_month,  start_from, end_at, open_slot, close_slot, cal_year, is_single_day, carry_from_id)";
        $aSql[] = " SELECT NULL, `r`.`rule_type_id`, `r`.`timeslot_id`, `r`.`repeat_minute`,  `r`.`repeat_hour`, ";
        $aSql[] = "        `r`.`repeat_dayofweek`, `r`.`repeat_dayofmonth`, `r`.`repeat_month`, ";
        $aSql[] = " date_add(`r`.`start_from`,INTERVAL 1 YEAR), date_add(`r`.`end_at`, INTERVAL 1 YEAR), ";
        $aSql[] = "        `r`.`open_slot`, `r`.`close_slot`,  :iNextCalendarYear, `r`.`is_single_day`, `r`.`rule_id` ";
        $aSql[] = " FROM $sRuleTable r ";
        $aSql[] = " WHERE `r`.rule_id IN ( ";
                                        $aSql[] = " SELECT distinct(`rs`.`rule_id`) AS rule_id ";
                                        $aSql[] = " FROM   $sRuleScheduleTable  rs ";
                                        $aSql[] = " JOIN   $sScheduleTable  s  ON `s`.`schedule_id` = `rs`.`schedule_id` ";
                                        $aSql[] = " WHERE  `s`.`calendar_year` = :iCalendarYearRollover AND `s`.`is_carryover` = true ";
                                        $aSql[] = " AND `rs`.`is_rollover`  = true ";
                                       
        $aSql[] = " )";
        
        
       
                
	        $oIntType  = Type::getType(Type::INTEGER);
	        $sSql      = implode($aSql,PHP_EOL);
            
        
            $iNumberNewRules = $oDatabase->executeUpdate($sSql,[':iCalendarYearRollover' => $iCalYear,':iNextCalendarYear' => $iNextCalYear],[$oIntType,$oIntType]);
            
	        if($iNumberNewRules == 0) {
	            throw RuleException::hasFailedRolloverRules($oCommand);
	        }
	        
	   
        
    }
    
    
    
    protected function rebuildRuleSeries(RolloverRulesCommand $oCommand) 
    {
        # find rules that where created
        $oDatabase    = $this->oDatabaseAdapter;
        $oIntegerType = TYPE::getType(TYPE::INTEGER);
        $oDateType    = TYPE::getType(TYPE::DATETIME);
        $oBoolType    = TYPE::getType(TYPE::BOOLEAN);
        $oPlatform    = $this->oDatabaseAdapter->getDatabasePlatform();
        
        $iNextCalYear           = $oCommand->getNextCalendarYear();
        $iCalYear               = $iNextCalYear - 1;
       
        $sRuleScheduleTable     = $this->aTableNames['bm_rule_series'];  
        $sRuleTable             = $this->aTableNames['bm_rule']; 
        $sScheduleTable         = $this->aTableNames['bm_schedule'];
        
        $aBinds = [
            ':iNextCalYear' => $iNextCalYear
        ];
        
        $aTypes = [
              ':iNextCalYear' => TYPE::INTEGER
        ];
        
        // This query will find rules in the calendar year that do not have series information. 
        
        $aQuery[]  = " SELECT `rule_id`, `rule_type_id`, `timeslot_id`, `repeat_minute`, `repeat_hour`,`repeat_dayofweek`, `repeat_dayofmonth` ,`repeat_month`, ";
        $aQuery[]  = "        `start_from`, `end_at`, `open_slot`, `close_slot`, `cal_year`,`is_single_day`,`carry_from_id` ";
        $aQuery[]  = " FROM $sRuleTable r ";
        $aQuery[]  = " WHERE `r`.`cal_year` = :iNextCalYear ";
        $aQuery[]  = " AND NOT EXISTS (SELECT 1 FROM bm_rule_series rs WHERE r.rule_id = rs.rule_id) ";
        
        $sQuery = implode($aQuery,PHP_EOL);
        
        $oQuery = $oDatabase->executeQuery($sQuery,$aBinds,$aTypes);
        
        # Process each rule to build the new series.
        
        while ($row = $oQuery->fetch()) {
            $iRuleId                = $oIntegerType->convertToPHPValue($row['rule_id'],$oPlatform);
            $oStartFromDate         = $oDateType->convertToPHPValue($row['start_from'],$oPlatform);
            $oEndtAtDate            = $oDateType->convertToPHPValue($row['end_at'],$oPlatform);
            $iTimeslotDatabaseId    = $oIntegerType->convertToPHPValue($row['timeslot_id'],$oPlatform);
            $iRuleTypeId            = $oIntegerType->convertToPHPValue($row['rule_type_id'],$oPlatform);
            $iOpeningSlot           = $oIntegerType->convertToPHPValue($row['open_slot'],$oPlatform);
            $iClosingSlot           = $oIntegerType->convertToPHPValue($row['close_slot'],$oPlatform);
            $sRepeatDayofweek       = $row['repeat_dayofweek'];
            $sRepeatDayofmonth      = $row['repeat_dayofmonth'];
            $sRepeatMonth           = $row['repeat_month'];
            $bIsSingleDay           = $oBoolType->convertToPHPValue($row['is_single_day'],$oPlatform);
            
            $oNewCommand = new CreateRuleCommand($oStartFromDate, $oEndtAtDate, $iRuleTypeId, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek,$sRepeatDayofmonth,$sRepeatMonth,$bIsSingleDay);
            $oNewCommand->setRuleId($iRuleId);
            
            $this->oCronToQuery->parse($oNewCommand);
            
        }
        
        
    }
    
    
    
    
     public function __construct(array $aTableNames, Connection $oDatabaseAdapter, CronToQuery $oCronToQuery)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        $this->oCronToQuery     = $oCronToQuery;
        
    }
    
    
    public function handle(RolloverRulesCommand $oCommand)
    {
         try {
        
            $this->rolloverRules($oCommand);
            
            $this->rebuildRuleSeries($oCommand);
                  
	    }
	    catch(DBALException $e) {
	        throw RuleException::hasFailedRolloverRules($oCommand, $e);
	      
	    } 
        
        return true;
    }
     
    
}
/* End of File */