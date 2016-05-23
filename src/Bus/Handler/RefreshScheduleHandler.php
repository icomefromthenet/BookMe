<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RefreshScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;
use IComeFromTheNet\BookMe\Cron\CronToQuery;


/**
 * Reapply rules series to a schedule
 * 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RefreshScheduleHandler
{
    
    /**
     * @var array   a map internal table names to external names
     */ 
    protected $aTableNames;
    
    /**
     * @var Doctrine\DBAL\Connection    the database adpater
     */ 
    protected $oDatabaseAdapter;
    
    
    
    protected function resetSchedule(RefreshScheduleCommand $oCommand)
    {
        $oDatabase          = $this->oDatabaseAdapter;
        $sScheduleSlotTable = $this->aTableNames['bm_schedule_slot'];
        
            
            $aBind = [
                ':iScheduleId'   => $oCommand->getScheduleId(),
            ];
            
            $aType = [
              ':iScheduleId'    => TYPE::INTEGER,
            ];
            
            # Step 1 clear existing value, as this is done in a single transaction won't cause issues if run live.
            # were net rest is_closed if a schedule is stopped it should remain stopped.
            # and where not clearing bookings 
            
	        $aSql[] = " UPDATE  $sScheduleSlotTable ";
	        $aSql[] = " SET `is_available` = false, `is_excluded` = false, `is_override` = false ";
	        $aSql[] = " WHERE schedule_id = :iScheduleId " ;
	
	        $sSql = implode(PHP_EOL,$aSql);
	
	        $oDatabase->executeUpdate($sSql, $aBind, $aType);
	        
	        // not checking for rows affected when the scheudle is new this update will not change any rows and
	        // will throw an error in error.
	     
    }
    

    public function __construct(array $aTableNames, Connection $oDatabaseAdapter)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
      
    }
    
    
    public function handle(RefreshScheduleCommand $oCommand)
    {
       
        $oDatabase          = $this->oDatabaseAdapter;
        $sRuleScheduleTable = $this->aTableNames['bm_rule_schedule'];
        $sRuleTable         = $this->aTableNames['bm_rule'];
        $sRuleTypeTable     = $this->aTableNames['bm_rule_type'];
        $sRuleSeriesTable   = $this->aTableNames['bm_rule_series'];
        
        
        $sScheduleTable     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTable = $this->aTableNames['bm_schedule_slot'];
        
        
        $aSql               = [];
        $sSql               = '';
        
        try {
            
            $aBind = [
                ':iScheduleId'   => $oCommand->getScheduleId(),
            ];
            
            $aType = [
              ':iScheduleId'    => TYPE::INTEGER,
            ];
            
            # Step 1 clear existing value, as this is done in a single transaction won't cause issues if run live.
            
            
            $this->resetSchedule($oCommand);
            
            
            # Step 2 refresh the schedule by combing the values from rule series.
           
            # This query will take the rules that apply to this schedule ($sRuleScheduleTable) and fetch
            # the rules details ($sRuleTable,$sRuleTable) it will then fetch details of the schedule where
            # updating and apply a limit to only select rules which have same timeslot as schedule.
            
            # The query will then explode the rule list into series by joining on the series table ($sRuleSeriesTable)
            # the slots in this series will mirror the slots in the schedule so they can be joined without a
            # range query. 
            
            # Since a series could have for example two work day rules in same schedule that affect the same slot 
            # we have to group the slots in the series together so they return only one row per slot.
            
            # By using the rules type information the query decides which columns in ($sScheduleSlotTable) to assign on.
            
            # When the query joins the inline view (crs) to the update table it using pk of the $sScheduleSlotTable table [schedule_id,slot_close]
            # which I hope is faster.
        
            
            $aSql[] = " UPDATE $sScheduleSlotTable sl ";
	        $aSql[] = " INNER JOIN ( ";
	        $aSql[] = "     SELECT `s`.`schedule_id`, `rss`.`slot_open`, `rss`.`slot_close`, "; 
	        $aSql[] = "             sum(IF(`rt`.`is_work_day` = true,1,0)) as is_available,  ";
	        $aSql[] = "             sum(IF(`rt`.`is_exclusion` = true,1,0)) as is_excluded, ";
	        $aSql[] = "             sum(IF(`rt`.`is_inc_override` = true,1,0)) as is_override ";
	        $aSql[] = "     FROM $sRuleScheduleTable rs ";
	        $aSql[] = "     JOIN $sRuleTable r on `r`.`rule_id` = `rs`.`rule_id` ";
	        $aSql[] = "     JOIN $sScheduleTable s on `s`.`schedule_id` = `rs`.`schedule_id` AND `r`.`timeslot_id` = `s`.`timeslot_id` "; 
	        $aSql[] = "     JOIN $sRuleTypeTable rt on `rt`.`rule_type_id` = `r`.`rule_type_id` ";
	        $aSql[] = "     JOIN $sRuleSeriesTable rss on `rss`.`rule_type_id` = `r`.`rule_type_id` AND `rss`.`rule_id` = `rs`.`rule_id` ";
	        $aSql[] = "     WHERE `rs`.`schedule_id` = :iScheduleId ";
	        $aSql[] = "     GROUP BY `s`.`schedule_id`,`rss`.`slot_open`, `rss`.`slot_close` ) crs";
	        $aSql[] = "        ON  `sl`.`schedule_id` = `crs`.`schedule_id` AND `crs`.`slot_close` = `sl`.`slot_close` ";
	        $aSql[] = " SET `sl`.`is_available` = IF(`crs`.`is_available` > 0,true,false), "; 
	        $aSql[] = "     `sl`.`is_excluded` = IF(`crs`.`is_excluded` > 0,true,false), ";
	        $aSql[] = "     `sl`.`is_override` = IF(`crs`.`is_override` > 0,true,false) ";
	        $aSql[] = " WHERE `sl`.`schedule_id` = :iScheduleId ";
	
	        $sSql = implode(PHP_EOL,$aSql);
	
	        $iAffectedRows = $oDatabase->executeUpdate($sSql, $aBind, $aType);
	        
	        if($iAffectedRows <= 1) {
	            throw ScheduleException::hasFailedRefreshSchedule($oCommand, null);
	        }
	        
        } catch (DBALException $e) {
            throw ScheduleException::hasFailedRefreshSchedule($oCommand,$e);
        }
	        
        return true;
    }
     
    
}
/* End of File */