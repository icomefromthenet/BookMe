<?php
namespace IComeFromTheNet\BookMe\Bus\Middleware;

use IComeFromTheNet\BookMe\BookMeException;
use League\Tactician\Middleware;


/**
 * This middle ware will ensure any exceptions are wrapped with this components
 * custom exception.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class ExceptionWrapperMiddleware implements Middleware
{

  
  
    /**
     * Will Validate the command if it implements the valdiation interface
     * 
     * @throws IComeFromTheNet\BookMe\Bus\Middleware\ValidationException
     * @param mixed $oCommand
     * @param callable $next
     * 
     */ 
    public function execute($oCommand, callable $next)
    {
        
        try {
        
            $returnValue = $next($oCommand);
        
        } catch(\RuntimeException $e) {
            throw new BookMeException($e->getMessage(),0,$e);
        }
        
        return $returnValue;
    }
  
  
  
}
/* End of Clas */