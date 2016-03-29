<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RegisterMemberCommand;
use IComeFromTheNet\BookMe\Bus\Exception\MembershipException;


/**
 * Used to register a new member
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RegisterMemberHandler 
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
    
    
    public function handle(RegisterMemberCommand $oCommand)
    {
        
        $oDatabase          = $this->oDatabaseAdapter;
        $sMemberTableName   = $this->aTableNames['bm_schedule_membership'];
        $iMemberId           = null;
        
        
        $sSql = " INSERT INTO $sMemberTableName (membership_id, registered_date) VALUES (null, NOW()) ";

	    
	    try {
	    
	        $oDatabase->executeUpdate($sSql, [], []);
            
            $iMemberId = $oDatabase->lastInsertId();
            
            $oCommand->setMemberId($iMemberId);
                 
	    }
	    catch(DBALException $e) {
	        throw SlotFailedException::hasFailedRegisterMember($oCommand, $e);
	    }
    	
        
        
        return true;
    }
     
    
}
/* End of File */