<?php
namespace IComeFromTheNet\BookMe\Test;

use IComeFromTheNet\BookMe\Test\Base\TestInstallBase;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;


class SlotInstallTest extends TestInstallBase
{
    
    
    /**
     * @group install
     */ 
    public function testAddNewSlot()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new SlotAddCommand(12);
       
        $oCommandBus->handle($oCommand);
        
        $this->assertNotEmpty($oCommand->getTimeSlotId());
        
        $numberSlots = (int)((60*24) / 12);
        
        // Assert max date is equal
        
        $iCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("select count(open_minute) from bm_timeslot_day where timeslot_id = ? ",[$oCommand->getTimeSlotId()],0,[]);
       
       
        $this->assertEquals($numberSlots,$iCount); 
        
        
    }
    

    /**
     * @group install
     * @expectedException IComeFromTheNet\BookMe\Bus\Exception\SlotFailedException
     */ 
    public function testAddFailsOnDuplicate()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new SlotAddCommand(12);
       
        $oCommandBus->handle($oCommand);
        
        $oCommand  = new SlotAddCommand(12);
       
        $oCommandBus->handle($oCommand);
        
    }
    
    
    
}
/* end of file */
