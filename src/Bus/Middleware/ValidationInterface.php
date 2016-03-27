<?php
namespace IComeFromTheNet\BookMe\Bus\Middleware;

/**
 * Commands the require validation can implements this interface
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
interface ValidationInterface
{
    
    /**
     * Return array of Validation rules
     * 
     * @return array
     */ 
    public function getRules();
    
    
    /**
     * Return name => value of this command
     * internal properties that require validation
     * 
     * @return array
     */ 
    public function getData();
    
}
/* End of File */