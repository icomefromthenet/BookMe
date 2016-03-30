<?php
namespace IComeFromTheNet\BookMe\Bus\Exception;

use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\Bus\Command\RegisterMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterTeamCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignTeamMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\WithdrawlTeamMemberCommand;

use League\Tactician\Exception\Exception as BusException;
use Doctrine\DBAL\DBALException;


/**
 * Custom Exception for Validation Middleware.
 * 
 * This is raised when exception fails
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class MembershipException extends BookMeException implements BusException
{
    /**
     * @var mixed
     */
    public $oCommand;
    
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedRegisterMember(RegisterMemberCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to create new schedule member', 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedRegisterTeam(RegisterTeamCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to create new schedule team using timeslot at datbase id '.$oCommand->getTimeSlotId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedAssignTeamMember(AssignTeamMemberCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to assign member at id '.$oCommand->getMemberId() .' to team at id '.$oCommand->getTeamId() .' For Schedule at id '.$oCommand->getScheduleId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedWithdrawlTeamMember(WithdrawlTeamMemberCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to withdrawl member at id '.$oCommand->getMemberId() .' to team at id '.$oCommand->getTeamId() .' For Schedule at id '.$oCommand->getScheduleId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    
    /**
     * Return the command that has failed validation
     * 
     * @return mixed
     */
    public function getCommand()
    {
        return $this->oCommand;
    }
    
    
}
/* End of File */