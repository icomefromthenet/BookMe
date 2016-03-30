<?php 
namespace IComeFromTheNet\BookMe;

/**
 * Events That this library will emmit
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 * 
 */ 
final class BookMeEvents
{
    
    /**
     * When a new year is added to the calender this event will
     * be emitted. 
     */ 
    const CALENDAR_ADD = 'bookme.calendar.add';
    
    
    /**
     * When a new slot is added and days generate this event will
     * be emitted. 
     */ 
    const SLOT_ADD = 'bookme.slot.add';
    
    /**
     * When a new member is registerd 
     */ 
    const MEMBER_REGISTER = 'bookme.member.register';
    
    /**
     * When a new team is registerd 
     */ 
    const TEAM_REGISTER = 'bookme.team.register';
    
    /**
     * When a member is assigned to a team 
     */ 
    const TEAM_MEMBER_ASSIGN = 'bookme.team.assign';
    
    /**
     * When a member is removed from a team  
     */ 
    const TEAM_MEMBER_WITHDRAWL = 'bookme.team.withdrawl';
    
}
/* End of File */