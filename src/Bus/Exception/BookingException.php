<?php
namespace IComeFromTheNet\BookMe\Bus\Exception;

use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\Bus\Command\TakeBookingCommand;
use IComeFromTheNet\BookMe\Bus\Command\ClearBookingCommand;

use League\Tactician\Exception\Exception as BusException;
use Doctrine\DBAL\DBALException;


/**
 * Custom Exception for Schedule Errors.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class BookingException extends BookMeException implements BusException
{
    /**
     * @var mixed
     */
    public $oCommand;
    
    
    /**
     * @param TakeBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToReserveSlots(TakeBookingCommand $oCommand, DBALException $oDatabaseException= null)
    {
        $exception = new static(
            'Unable to reserve schedule slots for schedule at id '.$oCommand->getScheduleId() 
            .' time from '.$oCommand->getOpeningSlot()->format('Y-m-d H:i:s') 
            .' until '.$oCommand->getClosingSlot()->format('Y-m-d H:i:s') , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
        
    /**
     * @param TakeBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToFindSlots(TakeBookingCommand $oCommand, DBALException $oDatabaseException= null)
    {
        $exception = new static(
            'Unable to find slots schedule slots for schedule at id '.$oCommand->getScheduleId() 
            .' time from '.$oCommand->getOpeningSlot()->format('Y-m-d H:i:s') 
            .' until '.$oCommand->getClosingSlot()->format('Y-m-d H:i:s') , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    
    /**
     * @param TakeBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToTakeBooking(TakeBookingCommand $oCommand, DBALException $oDatabaseException = null)
    {
        $exception = new static(
            'Unable to make booking for schedule at id '.$oCommand->getScheduleId() 
            .' time from '.$oCommand->getOpeningSlot()->format('Y-m-d H:i:s') 
            .' until '.$oCommand->getClosingSlot()->format('Y-m-d H:i:s') , 0 , $oDatabaseException
        );
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param ClearBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToClearBooking(ClearBookingCommand $oCommand, DBALException $oDatabaseException= null)
    {
        $exception = new static(
            'Unable to make booking at id '.$oCommand->getBookingId() , 0 , $oDatabaseException
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