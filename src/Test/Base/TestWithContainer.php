<?php
namespace IComeFromTheNet\BookMe\Test\Base;

use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Doctrine\DBAL\Schema\Schema;
use IComeFromTheNet\BookMe\BookMeContainer;
use IComeFromTheNet\BookMe\Test\Base\TestWithFixture;

class TestWithContainer extends TestWithFixture
{
    
  protected $oContainer;
  
  
  /**
   *  Return an instance of the container
   *
   *  @access public
   *  @return IComeFromTheNet\BookMe\BookMeContainer
   *
  */
  public function getContainer()
  {
    if(isset($this->oContainer) === false) {
        $this->oContainer = new BookMeContainer($this->getDoctrineConnection(),$this->getLogger(),$this->getEventDispatcher(),$this->getNow());
        $this->oContainer->boot();
        
        # register test services
        
      
    }
   
    return $this->oContainer;
  }
  
  /**
   *  docs
   *
   *  @access public
   *  @return Psr\Log\LoggerInterface
   *
  */
  
  protected function getLogger()
  {
     return new Logger('test-test',array(new TestHandler()));
  }
  
  /**
   *  Loads an eventdispatcher
   *
   *  @access protected
   *  @return Symfony\Component\EventDispatcher\EventDispatcherInterface
   *
  */
  protected function getEventDispatcher()
  {
    return new EventDispatcher();
  }
  
  /**
   *  Return a dateTime object
   *  Children Tests that want to bootstrap with
   *  fixed date should override this class
   *
   *  @access protected
   *  @return DateTime
   *
  */
  protected function getNow()
  {
    return new DateTime();
  }
  
 

}
/* End of File */