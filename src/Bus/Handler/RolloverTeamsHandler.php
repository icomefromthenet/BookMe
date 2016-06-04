<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RolloverTeamsCommand;
use IComeFromTheNet\BookMe\Bus\Exception\MembershipException;


/**
 * Used to rollover last years teams into new calender year
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RolloverTeamsHandler 
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
    
    
    public function handle(RolloverTeamsCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sTeamMembersTableName  = $this->aTableNames['bm_schedule_team_members'];
       
        $iNextCalenderYear      = $oCommand->getNextCalendarYear(); 
        $iCalendarYear          = $iNextCalenderYear -1;
        
       
        
        # Looking at schedules in the new calendar year that do not have a record in current table 
        
        $aSql[] = " REPLACE INTO $sTeamMembersTableName (`team_id`,`membership_id`,`schedule_id`,`registered_date`) ";
        $aSql[] = " SELECT `tm`.`team_id`, `tm`.`membership_id`, `s`.`schedule_id`, now() ";
        $aSql[] = " FROM $sTeamMembersTableName tm, $sScheduleTableName s ";
        $aSql[] = " WHERE `s`.`calendar_year` = ? ";
        $aSql[] = " AND `tm`.`membership_id` = `tm`.`membership_id` ";
        
        
        $sSql = implode(PHP_EOL,$aSql);
    
        
        try {
            
	        $oDateType = Type::getType(Type::DATE);
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	        # Step 2 Rollover the teams
	        $iNumberRolledOver = $oDatabase->executeUpdate($sSql,[$iNextCalenderYear], [$oIntType]);
	        
        	$oCommand->setRollOverNumber($iNumberRolledOver);
	       
                 
	    }
	    catch(DBALException $e) {
	        throw MembershipException::hasFailedRolloverTeam($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */