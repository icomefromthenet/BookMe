<?php
namespace IComeFromTheNet\BookMe\Events;

/**
 * Interface used to represent application event logger.
 * We could log to a database table or use a file logger.
 * 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
interface AppLoggerInterface
{
    
    /**
     * Write the event log
     * 
     * @access  protected
     * @param   string  The name of the activity
     * @param   string  A description of the activity
     * @param   string  The user who did the activity
     * @param   integer  An optional entity id. 
     * 
     */ 
    public function writeLog($activityName,$activityDescription,$username,$entityID = null);
        
    /**
     * Ensure that values are within acceptable ranges.
     * 
     * For example doctrine will not valdiate sizes for varchar fields
     * 
     * @param   string  $param      The database colum to validate
     * @param   mixed   $value      The value to validate
     * @throws BookMeException if error found
     */ 
    public function validate($param,$value);
    
        
}
/* End of interface */