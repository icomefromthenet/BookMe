<?php
namespace IComeFromTheNet\BookMe\Test\Base;

use \PHPUnit_Extensions_Database_DataSet_CompositeDataSet;

/**
 * Teste test assume we have the calender and slots already populated via
 * a common install fixture.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class TestDefaultBase extends TestWithContainer
{
    
    
    
    /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function setUp()
    {
        $this->getDoctrineConnection()->beginTransaction();
        
        parent::setUp();
     
        $this->getDoctrineConnection()->commit();
     
    }
    
    public function getDataSet($fixtures = array()) 
    {
        
        // get custom fixtures
        
        $oParentDataset = parent::getDataSet($fixtures);    
        
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet();
        
        // load common install fixture
        
        $sFixturePath = __DIR__ .'/../fixture/basic_fixture.xml';
        
        $oCommonFixture = $this->createMySQLXMLDataSet($sFixturePath);
        
       // combine both fixtures into a composite dataset
       $compositeDs->addDataSet($oCommonFixture);
       $compositeDs->addDataSet($oParentDataset);
       
        
        return $compositeDs;
    }
    
    
}
/* End of File */