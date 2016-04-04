<?php
namespace IComeFromTheNet\BookMe\Bus\Exception;

use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;
use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverSchedulesCommand;

use League\Tactician\Exception\Exception as BusException;
use Doctrine\DBAL\DBALException;


/**
 * Custom Exception for Schedule Errors.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class ScheduleException extends BookMeException implements BusException
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
    public static function hasFailedToggleScheduleCarry(ToggleScheduleCarryCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to toggle carry status of a schedule at id '.$oCommand->getScheduleId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedStopSchedule(StopScheduleCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to stop schedule and blackout availability for schedule at id  '.$oCommand->getScheduleId(), 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedStartSchedule(StopScheduleCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to start schedule and create slots for member at id  '.$oCommand->getMemberId()
            .' For calender year '.$oCommand->getCalendarYear().' using timeslot at id '.$oCommand->getTimeSlotId()
            , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedRolloverSchedule(RolloverSchedulesCommand $oCommand, DBALException $oDatabaseException)
    {
        $exception = new static(
            'Unable to rollover schedules for calendar year '.$oCommand->getCalendarYearRollover()
            , 0 , $oDatabaseException
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