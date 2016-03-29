<?php
namespace IComeFromTheNet\BookMe\Test;

use IComeFromTheNet\BookMe\Test\Base\TestDefaultBase;
use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Bus\Command\SlotToggleStatusCommand;


class SlotCommandsTest extends TestDefaultBase
{
    
    
    protected $aFixtures = ['slot_command_before.php'];
    
    /**
     * @group default
     */ 
    public function testToggleEnabled()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand = new SlotToggleStatusCommand(5);
       
        $oCommandBus->handle($oCommand);
       
        $oBooleanType = Type::getType(TYPE::BOOLEAN);
        $oIntergerType = Type::getType(TYPE::INTEGER);
    
        $mResult =  $oContainer->getDatabaseAdapter()->fetchColumn('SELECT is_active_slot FROM bm_timeslot where timeslot_id = ?',[5],0,[$oIntergerType]);
        $mResult = $oBooleanType->convertToPHPValue($mResult,$oContainer->getDatabaseAdapter()->getDatabasePlatform());
       
        $this->assertTrue($mResult);  
        
    }
    

    /**
     * @group default
     */ 
    public function testToggleDisabled()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand = new SlotToggleStatusCommand(1);
       
        $oCommandBus->handle($oCommand);
       
       
        $oCommandBus->handle($oCommand);
       
        $oBooleanType = Type::getType(TYPE::BOOLEAN);
        $oIntergerType = Type::getType(TYPE::INTEGER);
    
        $mResult =  $oContainer->getDatabaseAdapter()->fetchColumn('SELECT is_active_slot FROM bm_timeslot where timeslot_id = ?',[5],0,[$oIntergerType]);
        $mResult = $oBooleanType->convertToPHPValue($mResult,$oContainer->getDatabaseAdapter()->getDatabasePlatform());
       
        $this->assertFalse($mResult);  
     
        
    }

    
    
}
/* end of file */
