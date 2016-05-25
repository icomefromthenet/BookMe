<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RolloverTimeslotCommand;
use IComeFromTheNet\BookMe\Bus\Exception\SlotFailedException;


/**
 * Used to build slot years when rolling over schedules
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RolloverTimeslotHandler 
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
    
    
    public function handle(RolloverTimeslotCommand $oCommand)
    {
        
        $oDatabase              = $this->oDatabaseAdapter;
        $sTimeSlotTableName     = $this->aTableNames['bm_timeslot'];
        $sTimeSlotDayTableName  = $this->aTableNames['bm_timeslot_day'];
        $sTimeslotYearTableName = $this->aTableNames['bm_timeslot_year'];
        $sCalenderTableName     = $this->aTableNames['bm_calendar'];
        $iNewCalYear            = $oCommand->getCalendarYearRollover();
        $aSql                   = [];
        
        
        $aSql[] = " INSERT INTO $sTimeslotYearTableName (`timeslot_year_id`, `timeslot_id`, `opening_slot`, `closing_slot`, `y`, `m`, `d`, `dw`, `w`, `open_minute`, `close_minute`)  ";
        $aSql[] = " SELECT NULL, `st`.`timeslot_id`, (`c`.`calendar_date` + INTERVAL `s`.`open_minute` MINUTE) , (`c`.`calendar_date` + INTERVAL `s`.`close_minute` MINUTE), ";
        $aSql[] = " `c`.`y`, `c`.`m`, `c`.`d`, `c`.`dw`, `c`.`w`, `s`.`open_minute`, `s`.`close_minute` ";
        $aSql[] = " FROM $sTimeSlotTableName st ";
        $aSql[] = " JOIN $sTimeSlotDayTableName s on `s`.`timeslot_id` = `st`.`timeslot_id` ";
        $aSql[] = " CROSS JOIN $sCalenderTableName c ON `c`.`y` = ?";
        $aSql[] = " WHERE `st`.`is_active_slot` = true ";
           
        
        $sSql = implode(PHP_EOL,$aSql);
    
        
        try {
	    
	        $oIntType = Type::getType(Type::INTEGER);
	    
	        $iAffected = $oDatabase->executeUpdate($sSql, [$iNewCalYear], [$oIntType]);
	        
	        if($iAffected == 0) {
	            throw SlotFailedException::hasFailedToRollover($oCommand, $e);
	        }
	        
	        $oCommand->setRollOverNumber($iAffected);
	        
                 
	    }
	    catch(DBALException $e) {
	        throw SlotFailedException::hasFailedToRollover($oCommand, $e);
	    }
      
        
        return true;
    }
     
    
}
/* End of File */