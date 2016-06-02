<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\WithdrawlTeamMemberCommand;
use IComeFromTheNet\BookMe\Bus\Exception\MembershipException;


/**
 * Used to remove a member from a team 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class WithdrawlTeamMemberHandler 
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
    
    
    public function handle(WithdrawlTeamMemberCommand $oCommand)
    {
        
        $oDatabase              = $this->oDatabaseAdapter;
        $sTeamMemberTableName   = $this->aTableNames['bm_schedule_team_members'];
        
        $iTeamId           = $oCommand->getTeamId();
        $iMemberId         = $oCommand->getMemberId();
        $iScheduleId       = $oCommand->getScheduleId();
  
  
        $sSql  =" DELETE FROM $sTeamMemberTableName ";
        $sSql .=" WHERE `team_id`     = :iTeamId ";
        $sSql .=" AND `schedule_id`   = :iScheduleId ";
        $sSql .=" AND `membership_id` = :iMemberId ";      
  
	    try {
	    
	        $oIntegerType = Type::getType(Type::INTEGER);
	    
	        $aParams = [
	                ':iTeamId'     => $iTeamId,
	                ':iScheduleId' => $iScheduleId,
	                ':iMemberId'   => $iMemberId,  
	        ];
	    
	       $iRowsRemoved =  $oDatabase->executeUpdate($sSql, $aParams, [$oIntegerType, $oIntegerType, $oIntegerType]);

            if(true === empty($iRowsRemoved)) {
                throw new DBALException('Unable to find Team membership to remove');
            }

	    }
	    catch(DBALException $e) {
	        throw MembershipException::hasFailedWithdrawlTeamMember($oCommand, $e);
	    }
    
        
        return true;
    }
     
    
}
/* End of File */