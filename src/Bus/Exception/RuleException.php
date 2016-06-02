<?php
namespace IComeFromTheNet\BookMe\Bus\Exception;

use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignRuleToScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RemoveRuleFromScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverRulesCommand;


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
    public static function hasFailedToCreateNewRule(CreateRuleCommand $oCommand, DBALException $oDatabaseException = null)
    {
        
        $sRepeatMinute       = $oCommand->getRuleRepeatMinute();
        $sRepeatHour         = $oCommand->getRuleRepeatHour();
        $sRepeatDayofweek    = $oCommand->getRuleRepeatDayOfWeek();
        $sRepeatDayofmonth   = $oCommand->getRuleRepeatDayOfMonth();
        $sRepeatMonth        = $oCommand->getRuleRepeatMonth();
        $iOpeningSlot        = $oCommand->getOpeningSlot();
        $iClosingSlot        = $oCommand->getClosingSlot();
        $oEndtAtDate         = $oCommand->getCalendarEnd();
        $oStartFromDate      = $oCommand->getCalendarStart();
        $iRuleTypeDatabaseId = $oCommand->getRuleTypeId();
        
        $exception = new static(
            sprintf('Unable to create new rule %s %s %s %s %s starting at %s and ending at %s with starting slot %s ending at slot %s',
                    $sRepeatMinute,$sRepeatHour,$sRepeatDayofweek,$sRepeatDayofmonth,$sRepeatMonth,
                    $oStartFromDate->format('d-m-Y'),
                    $oEndtAtDate->format('d-m-Y'),
                    $iOpeningSlot,
                    $iClosingSlot
            ) , 0 , $oDatabaseException
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
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedAssignRuleToSchedule(AssignRuleToScheduleCommand $oCommand, DBALException $oDatabaseException = null)
    {
        
        $exception = new static(
            sprintf('Unable to link schedule at %s to rule at %s ',$oCommand->getScheduleId(), $oCommand->getRuleId()), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
        
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedRemoveRuleFromSchedule(RemoveRuleFromScheduleCommand $oCommand, DBALException $oDatabaseException = null)
    {
        
        $exception = new static(
            sprintf('Unable to unlink rule at %s from schedule at %s ', $oCommand->getRuleId(),$oCommand->getScheduleId()), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
        
    }
    
     /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedRolloverRules(RolloverRulesCommand $oCommand, DBALException $oDatabaseException = null)
    {
        
        $exception = new static(
            sprintf('Unable to rollover rules for calendar year at %s ', $oCommand->getNextCalendarYear()), 0 , $oDatabaseException
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