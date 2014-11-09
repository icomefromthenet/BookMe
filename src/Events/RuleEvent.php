<?php
namespace IComeFromTheNet\BookMe\Events;
use Symfony\Component\EventDispatcher\Event;

/**
 * Generic event for a rule change
 *
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
class RuleEvent extends Event
{
    const MemberInclusionRule = 'member.inclusion';
    const MemberExclusionRule = 'member.exclusion';
    const GroupInclusionRule  = 'group.inclusion';
    const GroupExclusionRule  = 'group.exclusion';
    
    const createdAction = 'created';
    const retiredAction = 'retired';
    
    
    
    protected $ruleType;
    
    protected $ruleAction;
    
    protected $ruleId;
    
    
    public function __construct($ruleType,$ruleAction, $ruleIdentifer)
    {
        if(false === in_array($ruleType,array(
                                              self::MemberExclusionRule
                                              ,self::MemberInclusionRule
                                              ,self::GroupExclusionRule
                                              ,self::GroupInclusionRule))) {
            throw new BookMeException("The rule type:: $ruleType is not one of the allowed");
        }
        
        if(false === in_array($ruleAction,array(self::createdAction,self::retiredAction))) {
            throw new BookMeException("The rule action::$ruleAction is not one of the allowed");
        }
        
        $this->ruleAction = $ruleAction;
        $this->ruleType   = $ruleType;
        $this->ruleId     = $ruleIdentifer;
        
    }
    
    
    public function getRuleType()
    {
        return $this->ruleType;
    }
    
    
    public function getAction()
    {
        return $this->ruleAction;
    }
    
    
    public function getRuleIdentifer()
    {
        return $this->ruleId;
    }
    
    
}
/* End of File */