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
    
    /**
     * When a schedule is started, note this not emitted when a schedule is carried over
     * from a previous period into new period
     */ 
    const SCHEDULE_START = 'bookme.schedule.start';
    
    /**
     * When a schedule has been stopped, no more appointments and no carryover! 
     */ 
    const SCHEDULE_STOP = 'bookme.schedule.stop';
    
    /**
     * When a stopped schedule is resumed 
     * 
     */ 
    const SCHEDULE_RESUME = 'bookme.schedule.resume';
    
    /**
     * When a rollover a schedule (copy carryon to the next calendar year) 
     */ 
    const SCHEDULE_ROLLOVER = 'bookme.schedule.rollover';
    
    /**
     * When a rollover of teams members  (create new relations for new schedules)
     */ 
    const TEAM_ROLLOVER = 'bookme.team.rollover';
    
    /**
     * When a booking has been sucessfuly scheduled
     */ 
    const BOOKING_TAKEN = 'bookme.booking.taken';
    
    /**
     * When a booking has been sucessfuly removed from schedule
     */ 
    const BOOKING_CLEARED = 'bookme.booking.cleared';
    
    /**
     * When emitt when a conflict check has completed
     */ 
    const BOOKING_CONFLICT = 'bookme.booking.conflict';
    
    /**
     * When a rollover rules
     */ 
    const RULE_ROLLOVER = 'bookme.rule.rollover';
    
    /**
     * When a create a new rule
     */ 
    const RULE_CREATE = 'bookme.rule.create';
    
    
    /**
     * When a rule is removed
     */ 
    const RULE_REMOVE = 'bookme.rule.remove';
}
/* End of File */