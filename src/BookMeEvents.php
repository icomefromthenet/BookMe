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
     * When a new member is regis is added and days generate this event will
     * be emitted. 
     */ 
    const MEMBER_REGISTER = 'bookme.member.register';
    
}
/* End of File */