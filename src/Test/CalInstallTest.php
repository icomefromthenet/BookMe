<?php
namespace IComeFromTheNet\BookMe\Test;

use IComeFromTheNet\BookMe\Test\Base\TestInstallBase;
use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;


class CalInstallTest extends TestInstallBase
{
    
    
    /**
     * @group install
     */ 
    public function testAddYear()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new CalAddYearCommand(1);
       
        $oCommandBus->handle($oCommand);
       
        
       
       
        $this->assertTrue(true);
    }
    
    
    
    
}
/* end of file */
