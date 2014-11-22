<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;


class UtilPackageTest extends BasicTest
{
    
    
    public function testValidDateRangeFunction()
    {
        
        $db = $this->getDoctrineConnection();
        
        # test that past date -> future date
        $e1 = $db->fetchColumn('select utl_is_valid_date_range(NOW() - INTERVAL 2 day,date_add(now(),INTERVAL 7 DAY)) as v',array(),0);
        $this->assertEquals(0,(int)$e1);
        
        # past date -> current date
        $e2 = $db->fetchColumn('select utl_is_valid_date_range(NOW() - INTERVAL 1 day,NOW()) as v',array(),0);
        $this->assertEquals(0,(int)$e2);
        
        # current date -> current date
        $e3 = $db->fetchColumn('select utl_is_valid_date_range(NOW(),NOW()) as v',array(),0);
        $this->assertEquals(1,(int)$e3);
        
        # current date -> Future date
        $e4 = $db->fetchColumn('select utl_is_valid_date_range(NOW(),date_add(now(),INTERVAL 7 DAY)) as v',array(),0);
        $this->assertEquals(1,(int)$e4);
        
        # Future date -> Future date
        $e5 = $db->fetchColumn('select utl_is_valid_date_range(date_add(now(),INTERVAL 7 DAY),date_add(now(),INTERVAL 14 DAY)) as v',array(),0);
        $this->assertEquals(1,(int)$e5);
        
    }
   
   
   
}
/* End of class */

