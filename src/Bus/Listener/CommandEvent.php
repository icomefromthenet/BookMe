<?php 
namespace IComeFromTheNet\BookMe\Bus\Listener;

use Symfony\Component\EventDispatcher\Event;

/**
 * The Event bus does not use the symfony2 event dispatcher this
 * event that be used by commands.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CommandEvent extends Event
{
    
    /**
     * @var HasEventInterface a command that has emmitted this event 
     */ 
    protected $oCommand;
    
    
    
    
    public function __construct(HasEventInterface $oCommand)
    {
        $this->oCommand = $oCommand;
    }
    
    
    
    /**
     * Return the command that caused this event to emit
     * 
     * @return HasEventInterface
     */ 
    public function getCommand()
    {
        return $this->oCommand;
    }
    
    
    
}
/* End of File */