<?php
namespace IComeFromTheNet\BookMe\Builder;

use \DateTime;
use DBALGateway\Builder\BuilderInterface;
use IComeFromTheNet\BookMe\Entity\MemberEntity;


/**
 * Maps a Member from our Membership database. 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class MemberBuilder implements BuilderInterface
{
    
    /**
      *  Convert data array into entity
      *
      *  @return mixed
      *  @param array $data
      *  @access public
      */
    public function build($data)
    {
        $entity = new MemberEntity();
        
        $entity->setCreatedDate($data['created_date']);
        
        $entity->setMemberID((int)$data['member_id']);
        
        return $entity;
    }

    /**
      *  Convert and entity into a data array
      *
      *  @return array
      *  @access public
      */
    public function demolish($entity)
    {

    }
    
}
/* End of Class */