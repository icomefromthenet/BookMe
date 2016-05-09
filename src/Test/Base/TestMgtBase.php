<?php
namespace IComeFromTheNet\BookMe\Test\Base;

use RuntimeException;
use \PHPUnit_Extensions_Database_Operation_Factory;

/**
 * Test methods within the rule module
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class TestMgtBase extends TestWithContainer
{
    
    protected $aFixtures = ['truncate_fixture.php'];
    
    
    /**
     * Returns the database operation executed in test setup.
     *
     * @return PHPUnit_Extensions_Database_Operation_DatabaseOperation
     */
    protected function getSetUpOperation()
    {
        return PHPUnit_Extensions_Database_Operation_Factory::TRUNCATE();
    }
    
    
     /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function setUp()
    {
        parent::setUp();
     
        // Need to execute operations which use our own services to do setups
        $this->handleEventPostFixtureRun();
    }
    
   
   protected function handleEventPostFixtureRun()
   {
       throw new RuntimeException('This method must be implemented');
   }    

    
    
}
/* End of File */