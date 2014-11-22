<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;


class MembershipAPITest extends BasicTest
{
    
    
    
    public function testAddMember()
    {
        $container = self::getContainer();
        $db        = $container->getDatabase();
        $username  = $container->getAppUser()->getUserIdentifier(); 
       
        # did method execute correctly   
        
        $memberID = $container->registerMembership();
        
        $this->assertNotEmpty($memberID);
        
        
        # does the record exist in table with returned id
        $result = $db->fetchAssoc('SELECT * FROM schedule_membership where membership_id = ?',array($memberID),array(\PDO::PARAM_INT));
        
        $this->assertNotEmpty($result);
        
        
        # is there an activity record for this entity id
        $result = $db->fetchAssoc('SELECT * FROM app_activity_log where entity_id = ? AND username = ?',array($memberID,$username),array(\PDO::PARAM_INT, \PDO::PARAM_STR));
        
        $this->assertNotEmpty($result);
        
    }
    
    
    protected function getBootstrapUser()
    {
        $m = $this->getMock('IComeFromTheNet\BookMe\Events\AppUserInterface');
        
        $m->expects($this->any())
          ->method('getUserIdentifier')
          ->will($this->returnValue('myuser')); 
        
        return $m;
    }
    
    
    
}
/* End of file */
    