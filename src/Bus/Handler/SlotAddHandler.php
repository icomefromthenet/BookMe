<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;
use IComeFromTheNet\BookMe\Bus\Exception\SlotFailedException;


/**
 * Used to add a new timeslot and gernate the slot days
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class SlotAddHandler 
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
     * Insert the new timeslot into the header table
     * 
     * Default to active. 
     * 
     * @access public
     * @return void
     * @param SlotAddCommand    $oCommand   The command that is being processed
     * @throws IComeFromTheNet\BookMe\Exception\SlotFailedException if database error occurse
     * 
     */ 
    protected function addNewSlot(SlotAddCommand $oCommand)
    {
        $oDatabase          = $this->oDatabaseAdapter;
        $sTimeSlotTableName = $this->aTableNames['bm_timeslot'];
        $iTimeSlotId        = null;
        $iSlotLength        = $oCommand->getSlotLength();
        
        $sSql = " INSERT INTO $sTimeSlotTableName (timeslot_id, timeslot_length, is_active_slot) VALUES (null, ?, true)";

	    
	    try {
	    
	        $oDatabase->executeUpdate($sSql, [$iSlotLength], [Type::getType(Type::INTEGER)]);
            
            $iTimeSlotId = $oDatabase->lastInsertId();
            
            $oCommand->setTimeSlotId($iTimeSlotId);
                 
	    }
	    catch(DBALException $e) {
	        throw SlotFailedException::hasFailedToCreateNewTimeslot($oCommand, $e);
	    }
    	

    }
    
    /**
     * Generate the new timeslots day values.
     * 
     * @access public
     * @return void
     * @param SlotAddCommand    $oCommand   The timeslot that was created
     * @throws IComeFromTheNet\BookMe\Exception\SlotFailedException if no days are generated
     */ 
    protected function buildSlotDays(SlotAddCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sTimeSlotTableName     = $this->aTableNames['bm_timeslot'];
        $sTimeSlotDayTableName  = $this->aTableNames['bm_timeslot_day'];
        $iTimeSlotId            = $oCommand->getTimeSlotId();
        $aSql                   = [];
        
        
        $aSql[] = " INSERT INTO $sTimeSlotDayTableName (`timeslot_day_id`,`timeslot_id`,`open_minute`,`close_minute`) ";
        $aSql[] = " SELECT null, :iTimeSlotId, 0 as start,  `t`.`timeslot_length` as end ";
        $aSql[] = " FROM $sTimeSlotTableName t  ";
        $aSql[] = " WHERE `t`.`timeslot_id` = :iTimeSlotId ";                                                                                                                                  
        $aSql[] = " UNION ";
        $aSql[] = " SELECT null, :iTimeSlotId, `ints`.`tick` as start, `t`.`timeslot_length`+`ints`.`tick` as end  ";                                                                                      
        $aSql[] = " FROM ( ";                                                                                                                                                                             
        $aSql[] = "     SELECT 1 + (`a`.`i`*1000 + `b`.`i`*100 + `c`.`i`*10 + `d`.`i`) as tick   ";                                                                                                        
        $aSql[] = "     FROM ints a JOIN ints b JOIN ints c JOIN ints d ";                                                                                                  
        $aSql[] = "     WHERE (`a`.`i`*1000 + `b`.`i`*100 + `c`.`i`*10 + `d`.`i`) <= (60*24) "; 
        $aSql[] = "     ORDER BY 1";
        $aSql[] = " ) ints ";                                                                                                                                                                              
        $aSql[] = " CROSS JOIN $sTimeSlotTableName t on `t`.`timeslot_id` = :iTimeSlotId   ";                                                                                                                                
        $aSql[] = " WHERE mod(`ints`.`tick`, `t`.`timeslot_length`) = 0 ";                                                                                                            
        $aSql[] = " AND (`t`.`timeslot_length`+`ints`.`tick`) <=  (60*24) ";                                                                                                            
        $aSql[] = " ORDER BY start ";
            
             
        
        $sSql = implode(PHP_EOL,$aSql);
        
        
        try {
	    
	        $oIntType = Type::getType(Type::INTEGER);
	    
	        $oDatabase->executeUpdate($sSql, [':iTimeSlotId' => $iTimeSlotId], [$oIntType]);
                 
	    }
	    catch(DBALException $e) {
	        throw SlotFailedException::hasFailedToCreateDays($oCommand, $e);
	    }
        
              
    }
    
    /**
     * Generate the new timeslots year values.
     * 
     * Requires slot days to exist
     * 
     * @access public
     * @return void
     * @param SlotAddCommand    $oCommand   The timeslot that was created
     * @throws IComeFromTheNet\BookMe\Exception\SlotFailedException if no days are generated
     */ 
    protected function buildSlotYear(SlotAddCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sTimeSlotTableName     = $this->aTableNames['bm_timeslot'];
        $sTimeSlotDayTableName  = $this->aTableNames['bm_timeslot_day'];
        $sTimeslotYearTableName = $this->aTableNames['bm_timeslot_year'];
        $sCalenderTableName     = $this->aTableNames['bm_calendar'];
        $iTimeSlotId            = $oCommand->getTimeSlotId();
        $iCalYear               = $oCommand->getCalendarYear(); 
        $aSql                   = [];
        
        
        $aSql[] = " INSERT INTO $sTimeslotYearTableName (`timeslot_year_id`, `timeslot_id`, `opening_slot`, `closing_slot`, `y`, `m`, `d`, `dw`, `w`, `open_minute`, `close_minute`)  ";
        $aSql[] = " SELECT NULL, ?, (`c`.`calendar_date` + INTERVAL `s`.`open_minute` MINUTE) , (`c`.`calendar_date` + INTERVAL `s`.`close_minute` MINUTE), ";
        $aSql[] = " `c`.`y`, `c`.`m`, `c`.`d`, `c`.`dw`, `c`.`w`, `s`.`open_minute`, `s`.`close_minute` ";
        $aSql[] = " FROM $sCalenderTableName c ";
        $aSql[] = " CROSS JOIN $sTimeSlotDayTableName s on `s`.`timeslot_id` = ?";
        $aSql[] = " WHERE `c`.`y` = ? ";
           
        
        $sSql = implode(PHP_EOL,$aSql);
    
        
        try {
	    
	        $oIntType = Type::getType(Type::INTEGER);
	    
	        $oDatabase->executeUpdate($sSql, [$iTimeSlotId,$iTimeSlotId,$iCalYear], [$oIntType,$oIntType,$oIntType]);
                 
	    }
	    catch(DBALException $e) {
	        throw SlotFailedException::hasFailedToCreateYear($oCommand, $e);
	    }
        
              
    }
    
    
    
    
    
    public function __construct(array $aTableNames, Connection $oDatabaseAdapter)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        
        
    }
    
    
    public function handle(SlotAddCommand $command)
    {
        
        $this->addNewSlot($command);
        $this->buildSlotDays($command);
        
        $this->buildSlotYear($command);
        
        
        return true;
    }
     
    
}
/* End of File */