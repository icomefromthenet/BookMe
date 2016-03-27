<?php
namespace IComeFromTheNet\BookMe\Test\Base;

use \PHPUnit_Extensions_Database_Operation_Factory;

/**
 * Install test should start with a clean slate and assume only records in
 * our database come from the migration file.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class TestInstallBase extends TestWithContainer
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
    
    
}
/* End of File */