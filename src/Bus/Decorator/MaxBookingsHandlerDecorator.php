<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\TakeBookingCommand;
use IComeFromTheNet\BookMe\Bus\Exception\BookingException;


/**
 * Used to reserve remaing slots any empty slots with max booking rule once value is reached.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class MaxBookingHandlerDecerator 
{
    
    /**
     * @var array   a map internal table names to external names
     */ 
    protected $aTableNames;
    
    /**
     * @var Doctrine\DBAL\Connection    the database adpater
     */ 
    protected $oDatabaseAdapter;
    
    
    protected $oHandler;
    
    
    public function __construct($oInternalHander ,array $aTableNames, Connection $oDatabaseAdapter)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        $this->oHandler         = $oInternalHander;    
        
    }
    
    
    public function handle(TakeBookingCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sBookingTableName      = $this->aTableNames['bm_booking'];
        
        $iScheduleId            = $oCommand->getScheduleId();
        $oCloseDate             = $oCommand->getClosingSlot();
        $oOpenDate              = $oCommand->getOpeningSlot();     
        
        
        
        $this->oHandler->handle($oCommand);
        
        # Have we exceeded max booking rile
        
        # Yes, then lock empty slots for the day
        
        # Mark empty slots with max booking
        
        
        return true;
    }
     
    
}
/* End of File */