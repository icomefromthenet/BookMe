<?php
namespace IComeFromTheNet\BookMe\Entity;

use \DateTime;

/**
 * Maps a Schedule Group from our Schedule database. 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class ScheduleGroupEntity
{
    
    /**
     * @var integer database  identifier
     */
    protected $groupID;
    
    /**
     * @var string A human friendly name of the group
     */ 
    protected $groupName;
    
    /**
     * @var DateTime the first date group is valid
     */ 
    protected $validFrom;
    
    /**
     * @var DateTime the last date group valid
     */ 
    protected $validTo;
    
    
    public function setGroupID($groupID)
    {
        $this->groupID = (int)$groupID;
    }
    
    public function getGroupID()
    {
        return $this->groupID;
    }
    
    public function setName($name)
    {
        $this->groupName = (string)$name;
    }
    
    
    public function getName()
    {
        return $this->groupName;
    }
    
   
    public function setValidFrom(DateTime $validFrom)
    {
        $this->validFrom = $validFrom;
    }
    
    public function getValidFrom()
    {
        return $this->validFrom;
    }
    
    public function setValidTo(DateTime $validTo = null)
    {
        $this->validTo = $validTo;
    }
    
    public function getValidTo()
    {
        return $this->validTo;
    }
    
}
/* End of Class */