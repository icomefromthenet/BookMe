<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;


class AppDatabaseLoggerTest extends BasicTest
{
    
    
    
    public function testContainerConstructor()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];
        
        # implements the correct interface
        $this->assertInstanceOf('IComeFromTheNet\BookMe\Events\AppLoggerInterface',$logger);
    
        # test database    
        $this->assertInstanceOf('Doctrine\DBAL\Connection',$logger->getDatabaseAdapter());
    }
    
    
   
    public function testValidateIngoresUnknown()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];

        $this->assertEquals(null,$logger->validate('fake_column','aaa'));
    }
    
    
    /**
     * 
     * @expectedException IComeFromTheNet\BookMe\BookMeException
     * @expectedExceptionMessage The param activity_name must be under or equal to length 32 and not empty
     */ 
    public function testValidateFailsForInvalidActivityName()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];

        $str = str_repeat('a',60);


        $this->assertTrue((mb_strlen($str) > 32),'test string is not greater than 32 characters');

        $logger->validate('activity_name',$str);
        
    }
    
    
    public function testValidateSucessfulForActivityName()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];

        $this->assertTrue($logger->validate('activity_name',str_repeat('a',32)));
        $this->assertTrue($logger->validate('activity_name',str_repeat('a',25)));
        
    }
    
    
     /**
     * 
     * @expectedException IComeFromTheNet\BookMe\BookMeException
     * @expectedExceptionMessage The param activity_description must be under or equal to length 255 and not empty
     */ 
    public function testValidateFailsForInvalidActivityDescription()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];

        $str = str_repeat('a',256);
        

        $logger->validate('activity_description',$str);
        
    }
    
    public function testValidateSucessfulForActivityDescription()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];

        $this->assertTrue($logger->validate('activity_description',str_repeat('a',255)));
        $this->assertTrue($logger->validate('activity_description',str_repeat('a',100)));
        
    }
    
    
     /**
     * 
     * @expectedException IComeFromTheNet\BookMe\BookMeException
     * @expectedExceptionMessage The param username must be under or equal to length 255 and not empty
     */ 
    public function testValidateFailsForInvalidUsernamen()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];

        $str = str_repeat('a',256);
        

        $logger->validate('username',$str);
        
    }
    
    public function testValidateSucessfulForUsername()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];

        $this->assertTrue($logger->validate('username',str_repeat('a',255)));
        $this->assertTrue($logger->validate('username',str_repeat('a',100)));
        
    }
    
    
    public function testWriteLogValid() 
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];
        $db = $container->getDatabase();
        
        $entityID             = 1;
        $username             = 'lewis dyer';
        $activityDescription  = 'The service was added';
        $activityName         = 'service_added';
        
        $insert = (int) $logger->writeLog($activityName,$activityDescription,$username,$entityID);
        
        $this->assertGreaterThan('0',$insert);
        
        # query and verify
        $result = $db->fetchAssoc('SELECT * FROM app_activity_log WHERE activity_id = ?',array($insert),array(\PDO::PARAM_INT));
        $this->assertEquals($activityName ,$result['activity_name']);
        $this->assertEquals($activityDescription ,$result['activity_description']);
        $this->assertEquals($entityID ,(int)$result['entity_id']);
        $this->assertEquals($username ,$result['username']);
        $this->assertNotNull($result['activity_date']);
        
    }
    
    /**
     * 
     * @expectedException IComeFromTheNet\BookMe\BookMeException
     * @expectedExceptionMessage The param username must be under or equal to length 255 and not empty
     */ 
    public function testWriteLogInvalid()
    {
        $container = self::getContainer();
        $logger = $container['appDatabaseLogger'];
        $db = $container->getDatabase();
        
        $entityID             = 1;
        $username             = str_repeat('a',300);
        $activityDescription  = 'The service was added';
        $activityName         = 'service_added';
        
        $insert = (int) $logger->writeLog($activityName,$activityDescription,$username,$entityID);
        
        
        
    }
  
    protected function getBootstrapUser()
    {
        $m =  $this->getMock('IComeFromTheNet\BookMe\Events\AppUserInterface');
                    
        $m->expects($this->any())
            ->method('getUserIdentifier')
            ->will($this->returnValue(1));
                    
        return $m;
    }
    
    
    
    
}
/* End of file */
    