<?php
namespace IComeFromTheNet\BookMe\Tests;

use DateTime;
use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Entity\MemberEntity;
use IComeFromTheNet\BookMe\Builder\MemberBuilder;

class EntityTest extends BasicTest
{
    
    
    
    public function testMemberEntity()
    {
        $createdDate = new DateTime();
        $memberID    = 1;
        
        
        # Test entity properties
        $entity      = new MemberEntity();
        $entity->setMemberID($memberID);
        $entity->setCreatedDate($createdDate);
        
        $this->assertEquals($entity->getMemberID(),$memberID);
        $this->assertEquals($entity->getCreatedDate(),$createdDate);
        
        # Test entity builder
        $builder     = new MemberBuilder();
        $data        = array(
                        'member_id' => $memberID,
                        'created_date' =>  $createdDate
                    );
        
        $sameEntity = $builder->build($data);
        
        $this->assertEquals($sameEntity->getMemberID(),$memberID);
        $this->assertEquals($sameEntity->getCreatedDate(),$createdDate);
        
    }
    
    
    
}
/* End of file */
    