<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;


/**
 * Used to toggle the carry over flag of a schedule if disabled a new
 * schedule will not be created during a rollover
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class ToggleScheduleCarryHandler 
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
    
    
    public function handle(ToggleScheduleCarryCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $iScheduleId            = $oCommand->getScheduleId();
        $aSql                   = [];
        
        
        $aSql[] = " UPDATE $sScheduleTableName  ";
        $aSql[] = " SET  is_carryover = IF(is_carryover = true,false,true)";
        $aSql[] = " WHERE schedule_id = ? ";
             
        
        $sSql = implode(PHP_EOL,$aSql);
        
        
        try {
	    
	        $oIntType = Type::getType(Type::INTEGER);
	    
	        $iRowsAffected = $oDatabase->executeUpdate($sSql, [$iScheduleId], [$oIntType]);
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not match a schedule to toggle please check database id');
	        }
                 
	    }
	    catch(DBALException $e) {
	        throw ScheduleException::hasFailedToggleScheduleCarry($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */