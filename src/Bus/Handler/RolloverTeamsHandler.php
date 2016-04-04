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
    
    /**
     * @var integer the number of times to try and obtain a lock
     */ 
    protected $iLockRetry;

    
    
    protected function lockTeamTables()
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sTeamMembersTableName  = $this->aTableNames['bm_schedule_team_members'];
        $bLockObtained          = false;
        $iLockCount             = $this->iLockRetry;
        
        do {
            
            try {
                
                $sLockSql = " LOCK TABLES $sTeamMembersTableName WRITE, $sTeamMembersTableName WRITE as tm ";
     
           
                $bLockObtained = $oDatabase->executeUpdate($sLockSql,[],[]);
                
            } catch(DBALException $e) {
                $iLockCount = $iLockCount - 1;
            }
            
        } while($bLockObtained === false && $iLockCount > 0);
        
        return $bLockObtained;
        
    }
    
    protected function unlockTeamTables()
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sTeamMembersTableName  = $this->aTableNames['bm_schedule_team_members'];
        
                
        $sLockSql = " UNLOCK TABLES ";

        return $oDatabase->executeUpdate($sLockSql,[],[]);
        
    }
    
    public function __construct(array $aTableNames, Connection $oDatabaseAdapter, $iLockRetry = 3)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        $this->iLockRetry       = $iLockRetry;
        
    }
    
    
    public function handle(RolloverTeamsCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sTeamMembersTableName  = $this->aTableNames['bm_schedule_team_members'];
       
        $iCalendarYear          = $oCommand->getCalendarYearRollover();
        $iNextCalenderYear      = $iCalendarYear +1;
       
        
        # Rollover last calendar year but exclude any that have been done already we know those done already by looking
        # at the carryover flag on schedule table.
        
        $aSql[] = " INSERT INTO $sTeamMembersTableName (`team_id`,`membership_id`,`schedule_id`,`registered_date`) ";
        $aSql[] = " SELECT `tm`.`team_id`, `tm`.`membership_id`, `s`.`schedule_id`, now() ";
        $aSql[] = " FROM $sTeamMembersTableName tm, $sScheduleTableName s ";
        $aSql[] = " WHERE `s`.`is_carryover` = true AND `s`.`calendar_year` = ? ";
        $aSql[] = " AND `s`.`schedule_id` = `tm`.`schedule_id` ";
        
        $sSql = implode(PHP_EOL,$aSql);
        
        
        try {
            
            # Step 1 Obtain a write locks
            if($this->lockTeamsTables()) {
                throw MembershipException::hasFailedRolloverTeam($oCommand, new DBALException("Unable to get lock on $sTeamMembersTableName table"));
            }
            
	        $oDateType = Type::getType(Type::DATE);
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	        # Step 2 Rollover the teams
	        $iNumberRolledOver = $oDatabase->executeUpdate($sSql,[$iNextCalenderYear] , [$oIntType]);
	        
	        
	        $oCommand->setRollOverNumber($iNumberRolledOver);
	        
	        # Step 3 Clear the write locks
	        $this->unlockTeamTables();
                 
	    }
	    catch(DBALException $e) {
	        throw MembershipException::hasFailedRolloverTeam($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */