<?php 
namespace IComeFromTheNet\BookMe\Cron;

use DateTime;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;


/**
 * Custom Exception for Slot Finder Errors.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class SlotFinderException extends BookMeException
{
   
     protected $oCommand;
   
   
     /**
     * @param TakeBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToFindSlots(CreateRuleCommand $oCommand, DBALException $oDatabaseException = null)
    {
        
        $sMessage = printf('Unable to find slots for timeslot at id %s for Opening date %s to Closing Date %s For Opening Minute %s to Closing Minute %s'
                ,$oCommand->getTimeSlotId()
                ,$oCommand->getCalendarStart()->format('d/m/Y')
                ,$oCommand->getCalendarEnd()->format('d/m/Y')
                ,$oCommand->getOpeningSlot()
                ,$oCommand->getClosingSlot());
        
        $exception = new static($sMessage, 0);
        
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
   
    
    /**
     * @param TakeBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToFindSlotsQuery(CreateRuleCommand $oCommand, DBALException $oDatabaseException)
    {
          $sMessage = printf('Timeslot finder query has failed for timeslot at id %s for Opening date %s to Closing Date %s For Opening Minute %s to Closing Minute %s'
                ,$oCommand->getTimeSlotId()
                ,$oCommand->getCalendarStart()->format('d/m/Y')
                ,$oCommand->getCalendarEnd()->format('d/m/Y')
                ,$oCommand->getOpeningSlot()
                ,$oCommand->getClosingSlot());
        
        $exception = new static($sMessage, 0, $oDatabaseException);
      
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
     /**
     * @param TakeBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToBuildRuleSeries(CreateRuleCommand $oCommand, DBALException $oDatabaseException = null)
    {
          $sMessage = printf('Failed to find slots for rule at timeslot at id %s for Opening date %s to Closing Date %s For Opening Minute %s to Closing Minute %s'
                ,$oCommand->getTimeSlotId()
                ,$oCommand->getCalendarStart()->format('d/m/Y')
                ,$oCommand->getCalendarEnd()->format('d/m/Y')
                ,$oCommand->getOpeningSlot()
                ,$oCommand->getClosingSlot());
        
        $exception = new static($sMessage, 0, $oDatabaseException);
      
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
    /**
     * @param TakeBookingCommand $oCommand
     *
     * @return static
     */
    public static function hasFailedToBuildRuleSeriesQuery(CreateRuleCommand $oCommand, DBALException $oDatabaseException)
    {
          $sMessage = printf('Failed build rule query for timeslot at id %s for Opening date %s to Closing Date %s For Opening Minute %s to Closing Minute %s with database error %s'
                ,$oCommand->getTimeSlotId()
                ,$oCommand->getCalendarStart()->format('d/m/Y')
                ,$oCommand->getCalendarEnd()->format('d/m/Y')
                ,$oCommand->getOpeningSlot()
                ,$oCommand->getClosingSlot()
                ,$oDatabaseException->getMessage());
        
        $exception = new static($sMessage, 0, $oDatabaseException);
      
        $exception->oCommand = $oCommand;
        
        return $exception;
    }
    
  
    public function getCommand()
    {
        return $this->oCommand;
    }
    
}
/* End of File */