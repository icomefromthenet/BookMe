<?php
namespace IComeFromTheNet\BookMe\Tests;

use DateTime;
use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Entity\MemberEntity;
use IComeFromTheNet\BookMe\Entity\ScheduleGroupEntity;
use IComeFromTheNet\BookMe\Builder\MemberBuilder;
use IComeFromTheNet\BookMe\Builder\ScheduleGroupBuilder;

class EntityTest extends BasicTest
{
    
    
    
    public function testMemberEntity()
    {
        $createdDate = new DateTime();
        $memberID    = 1;
        $db          = $this->getContainer()->getDatabaseAdapter(); 
        
        # Test entity properties
        $entity      = new MemberEntity();
        $entity->setMemberID($memberID);
        $entity->setCreatedDate($createdDate);
        
        $this->assertEquals($entity->getMemberID(),$memberID);
        $this->assertEquals($entity->getCreatedDate(),$createdDate);
        
        # Test entity builder
        $builder     = new MemberBuilder($db);
        $data        = array(
                        'membership_id' => $memberID,
                        'registered_date' =>  $createdDate->format('Y-m-d H:i:s')
        );
        
        
        $sameEntity = $builder->build($data);
        $this->assertInstanceOf('IComeFromTheNet\BookMe\Entity\MemberEntity',$sameEntity);
        $this->assertEquals($sameEntity->getMemberID(),$memberID);
        $this->assertEquals($sameEntity->getCreatedDate(),$createdDate);
        
        
    }
    
    
    public function testScheduleGroupEntity()
    {
        $db  = $this->getContainer()->getDatabaseAdapter();
        
        $validTo    = $db->fetchColumn('SELECT CAST((NOW() + INTERVAL 7 DAY) AS DATE) as dte',array(),0);
        $validToDte = Datetime::createFromFormat('Y-m-d',$validTo);
        $validToDte->setTime(0,0,0);
        
        $this->assertEquals($validTo,$validToDte->format('Y-m-d'));
        
        $validFrom = $db->fetchColumn('SELECT CAST(NOW() AS DATE) as dte',array(),0);
        $validFromDte = Datetime::createFromFormat('Y-m-d',$validFrom);
        $validFromDte->setTime(0,0,0);
        
        $this->assertEquals($validFrom,$validFromDte->format('Y-m-d'));
        
        $groupID   = 1;
        $groupName = 'gp1';
        
        $builder = new ScheduleGroupBuilder($db);
        
        $data = array(
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
            'group_id' => $groupID,
            'group_name' => $groupName
        );
        
        # test basic entity properties
        $entity = new ScheduleGroupEntity();
        $entity->setValidTo($validToDte);
        $entity->setValidFrom($validFromDte);
        $entity->setName($groupName);
        $entity->setGroupID($groupID);
        
        
        $this->assertEquals($groupID,$entity->getGroupID());
        $this->assertEquals($groupName,$entity->getName());
        $this->assertEquals($validFromDte,$entity->getValidFrom());
        $this->assertEquals($validToDte,$entity->getValidTo());
        
        # test the entity builder
        $sameEntity = $builder->build($data);
        
        $this->assertEquals($groupID,$sameEntity->getGroupID());
        $this->assertEquals($groupName,$sameEntity->getName());
        $this->assertEquals($validFromDte,$sameEntity->getValidFrom());
        $this->assertEquals($validToDte,$sameEntity->getValidTo());
        
        
    }
    
    
    
    
    
    
}
/* End of file */
    