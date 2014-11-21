<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Events\AppActivityLogHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use IComeFromTheNet\BookMe\Events\BookMeEvents;
use IComeFromTheNet\BookMe\Events\MembershipEvent;

class AppEventHandlerTest extends BasicTest
{
    
    
    
    public function testContainerConstructor()
    {
        $container = self::getContainer();
        $handler = $container['appActivityLog'];
        
        $this->assertInstanceOf('IComeFromTheNet\BookMe\Events\AppActivityLogHandler',$handler);
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface',$handler);
        
    }
    
    

    
    public function testWriteMemberRegisteredEvent()
    {
        $container  = self::getContainer();
        $event      = new MembershipEvent(100);
        $logger     = $this->getMock('IComeFromTheNet\BookMe\Events\AppLoggerInterface');
        $user       =  $this->getMock('IComeFromTheNet\BookMe\Events\AppUserInterface');
        $dispatcher = new EventDispatcher();
        $handler    = new AppActivityLogHandler($logger,$user);
        
        # Setup the mocks we want to test that the handler calls the logger write method
        # with expected params
        
        $user->expects($this->once())
             ->method('getUserIdentifier')
             ->will($this->returnValue('myuser'));
             
        $logger->expects($this->once())
               ->method('writeLog')
               ->with($this->equalTo(BookMeEvents::MemberRegistered)
                      ,$this->equalTo('Added new membership with id 100')
                      ,$this->equalTo('myuser')
                      ,$this->equalTo(100))
               ->will($this->returnValue(1));
        
        $dispatcher->addSubscriber($handler);
        
        $dispatcher->dispatch(BookMeEvents::MemberRegistered,$event);
        
    }
    

    
}
/* End of Class */