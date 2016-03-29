<?php
namespace IComeFromTheNet\BookMe\Bus\Listener;

use League\Tactician\CommandEvents\Event\CommandHandled as TacticianCommandHandled;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Allows a command to emmit an event.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CommandHandled
{
    /**
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */ 
    protected $oEventDispatcher;
    
    
    
    public function __construct(EventDispatcherInterface $oEventDispatcher)
    {
        $this->oEventDispatcher = $oEventDispatcher;
    }
    
    
    /**
     * Will fetch the symfony2 event to emit if the command implements
     * the correct interface
     * 
     * @return void
     * @param TacticianCommandHandled   $oCommand   The success command from the event bus
     */ 
    public function handle(TacticianCommandHandled $oCommand)
    {
        $oInnerCommand = $oCommand->getCommand();
        
        
        if($oInnerCommand instanceof HasEventInterface) {
            
            $this->oEventDispatcher->dispatch($oInnerCommand->getEventName(),$oInnerCommand->getEvent());
        }
           
    }
    
    
}
/* End of class */