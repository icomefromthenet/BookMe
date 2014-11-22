<?php
namespace IComeFromTheNet\BookMe\Entity;

use \DateTime;

/**
 * Maps a Member from our Membership database. 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class MemberEntity
{
    
    /**
     * @var integer $memberID
     */ 
    protected $memberID;
    
    /**
     * @var DateTime $dateCreated
     */ 
    protected $dateCreated;
    
    
    
    public function setMemberID($memberID)
    {
        $this->memberID = (int) $memberID;
    }
    
    
    public function getMemberID()
    {
        return $this->memberID;
    }
    
    
    public function setCreatedDate(DateTime $created)
    {
        $this->dateCreated = $created;
    }
    
    
    public function getCreatedDate()
    {
        return $this->dateCreated;
    }
    
    
}
/* End of Class */