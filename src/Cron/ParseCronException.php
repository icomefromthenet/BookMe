<?php
namespace IComeFromTheNet\BookMe\Cron;

use IComeFromTheNet\BookMe\BookMeException;


/**
 * Custom Exception for Rule Parse Errors.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class ParseCronException extends BookMeException 
{
    
    
    /**
     * @param 
     *
     * @return static
     */
    public static function parseCronFailed($sMessage, $sCronString, BookMeException $e = null)
    {
        $exception = new static(
            "Failed to parse cron string $sCronString with error $sMessage", 0 ,$e );
        
        
        return $exception;
    }
    
    
    
}
/* End of File */