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
        $aSql[] = " SELECT null, ?, `ints`.`tick` as start, (ceil(`ints`.`tick`/`t`.`timeslot_length`) * 5)+1 as end ";
        $aSql[] = " FROM ( ";
        $aSql[] = "     SELECT 1 + (`a`.`i`*1000 + `b`.`i`*100 + `c`.`i`*10 + `d`.`i`) as tick ";
        $aSql[] = "     FROM ints a JOIN ints b JOIN ints c JOIN ints d  ";
        $aSql[] = "     WHERE (`a`.`i`*1000 + `b`.`i`*100 + `c`.`i`*10 + `d`.`i`) < (60*24) ";
        $aSql[] = " ) ints ";
        $aSql[] = " CROSS JOIN $sTimeSlotTableName t on `t`.`timeslot_id` = ? ";
        $aSql[] = " GROUP BY (ceil(`ints`.`tick`/`t`.`timeslot_length`)) ";
        $aSql[] = " ORDER BY 1 ";
             
        
        $sSql = implode(PHP_EOL,$aSql);
        
        
        try {
	    
	        $oIntType = Type::getType(Type::INTEGER);
	    
	        $oDatabase->executeUpdate($sSql, [$iTimeSlotId,$iTimeSlotId], [$oIntType,$oIntType]);
                 
	    }
	    catch(DBALException $e) {
	        throw SlotFailedException::hasFailedToCreateDays($oCommand, $e);
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
        
        
        return true;
    }
     
    
}
/* End of File */