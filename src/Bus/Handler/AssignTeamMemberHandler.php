<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\AssignTeamMemberCommand;
use IComeFromTheNet\BookMe\Bus\Exception\MembershipException;


/**
 * Used to assign a member to a team as a schedule only last one calendar year
 * we need to ensure that this relation matches exactly one schedule and with it
 * having a matching timeslot to the requirement of the team.
 * 
 * If a new schedule is created with different timeslot they no longer eligible to be
 * a member of this team.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class AssignTeamMemberHandler 
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
    
    
    public function handle(AssignTeamMemberCommand $oCommand)
    {
        
        $oDatabase              = $this->oDatabaseAdapter;
        $sTeamTableName         = $this->aTableNames['bm_schedule_team'];
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sTeamMemberTableName   = $this->aTableNames['bm_schedule_team_members'];
        
        $iTeamId           = $oCommand->getTeamId();
        $iMemberId         = $oCommand->getMemberId();
        $iScheduleId       = $oCommand->getScheduleId();
        
        $sSql  = " INSERT INTO $sTeamMemberTableName (`team_id`, `membership_id`, `registered_date`, `schedule_id`) ";
        $sSql .= " SELECT :iTeamId, s.membership_id, now(), s.schedule_id "; 
        $sSql .= " FROM  $sScheduleTableName  s";
        $sSql .= " WHERE s.schedule_id = :iScheduleId";
	    $sSql .= " AND s.membership_id = :iMemberId";
	    $sSql .= " AND EXISTS (SELECT 1 FROM $sTeamTableName t WHERE t.team_id = :iTeamId AND t.timeslot_id = s.timeslot_id)";
	    
	    try {
	    
	        $oIntegerType = Type::getType(Type::INTEGER);
	    
	        $aParams = [
	                ':iTeamId'     => $iTeamId,
	                ':iScheduleId' => $iScheduleId,
	                ':iMemberId'   => $iMemberId,  
	        ];
	    
	        $iAffected = $oDatabase->executeUpdate($sSql, $aParams, [$oIntegerType, $oIntegerType, $oIntegerType]);
            
            if(true === empty($iAffected)) {
                throw new DBALException('Check timeslot of team matches the schedule or member given that may not be the owner of the schedule');
            }
                 
	    }
	    catch(DBALException $e) {
	        throw MembershipException::hasFailedAssignTeamMember($oCommand, $e);
	    }
    	
        
        
        return true;
    }
     
    
}
/* End of File */