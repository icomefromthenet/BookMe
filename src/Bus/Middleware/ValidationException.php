<?php
namespace IComeFromTheNet\BookMe\Bus\Middleware;

use IComeFromTheNet\BookMe\BookMeException;
use League\Tactician\Exception\Exception as BusException;


/**
 * Custom Exception for Validation Middleware.
 * 
 * This is raised when exception fails
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
class ValidationException extends BookMeException implements BusException
{
    /**
     * @var mixed
     */
    public $oCommand;
    
    /**
     * @var array of errors messages
     */ 
    public $aErrors;
    
    /**
     * @param mixed $invalidCommand
     *
     * @return static
     */
    public static function hasFailedValidation(ValidationInterface $oCommand, array $aErrors)
    {
        $exception = new static(
            'Validation has failed for command'. get_class($oCommand)
        );
        
        $exception->oCommand = $oCommand;
        $exception->aErrors  = $aErrors;
        
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
    
    /**
     * Return the errors found during validation
     * 
     * @return array
     */ 
    public function getValidationFailures()
    {
        return $this->aErrors;
    }
    
}
/* End of File */