<?php
namespace IComeFromTheNet\BookMe\Events;

/**
 * Interface used map a user into the App Event Log which has 
 * a column called username to map the user who did the operation.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
interface AppUserInterface
{
    
    /**
     * Return the user identifer.
     * 
     * This should be able to identify the user who
     * bootstraped the BookMe Service in any external systems.
     * 
     * A username works best max 255 characters
     * 
     * @access public
     * @return string
     */  
    public function getUserIdentifier();
 
    
}
/* End of interface */