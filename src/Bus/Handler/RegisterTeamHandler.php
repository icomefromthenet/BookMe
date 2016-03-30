<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RegisterTeamCommand;
use IComeFromTheNet\BookMe\Bus\Exception\MembershipException;


/**
 * Used to register a new member
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RegisterTeamHandler 
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
    
    
    public function handle(RegisterTeamCommand $oCommand)
    {
        
        $oDatabase         = $this->oDatabaseAdapter;
        $sTeamTableName    = $this->aTableNames['bm_schedule_team'];
        $iTeamId           = null;
        $iTimeSlotId       = $oCommand->getTimeSlotId();
        
        $sSql = " INSERT INTO $sTeamTableName (team_id, timeslot_id ,registered_date) VALUES (null, ? ,NOW()) ";

	    
	    try {
	    
	        $oDatabase->executeUpdate($sSql, [$iTimeSlotId], [Type::getType(Type::INTEGER)]);
            
            $iTeamId = $oDatabase->lastInsertId();
            
            $oCommand->setTeamId($iTeamId);
                 
	    }
	    catch(DBALException $e) {
	        throw SlotFailedException::hasFailedRegisterTeam($oCommand, $e);
	    }
    	
        
        
        return true;
    }
     
    
}
/* End of File */