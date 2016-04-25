<?php
namespace IComeFromTheNet\BookMe\Bus\Exception;

use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;


use League\Tactician\Exception\Exception as BusException;
use Doctrine\DBAL\DBALException;


/**
 * Custom Exception for Rule Handlers
 * 
 * This is raised when exception fails
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class RuleException extends BookMeException implements BusException
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
    public static function hasFailedToCreateNewRule(CreateRuleCommand $oCommand, DBALException $oDatabaseException)
    {
        
        $sRepeatMinute = $oCommand->getRuleRepeatMinute();
        $sRepeatHour = $oCommand->getRuleRepeatHour();
        $sRepeatDayofweek = $oCommand->getRuleRepeatDayOfWeek();
        $sRepeatDayofmonth = $oCommand->getRuleRepeatDayOfMonth();
        $sRepeatMonth = $oCommand->getRuleRepeatMonth();
        $iOpeningSlot = $oCommand->getOpeningSlot();
        $iClosingSlot = $oCommand->getClosingSlot();
        $oEndtAtDate = $oCommand->getCalendarEnd();
        $oStartFromDate = $oCommand->getCalendarStart();
        $iRuleTypeDatabaseId = $oCommand->getRuleTypeId();
        
        $exception = new static(
            'Unable to create new rule ' , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToCreateDays(SlotAddCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to create new timeslot days for '. $oCommand->getSlotLength() .' ', 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToCreateYear(SlotAddCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to create new timeslot year for '. $oCommand->getSlotLength() .' ', 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedToToggleStatus(SlotToggleStatusCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to toggle timeslot status for '. $oCommand->getTimeSlotId() .' ', 0 , $oDatabaseException
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