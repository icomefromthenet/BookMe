<?php
namespace IComeFromTheNet\BookMe\Events;

/**
 * Defines the event names for the application
 *
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
final class BookMeEvents
{
    
    const MemberRegistered          = 'bookme.member.registration';
    
    const ScheduleRegistered        = 'bookme.schedule.registration';
    const ScheduleRetired           = 'bookme.schedule.retired';
    
    const SchduleGroupCreated       = 'bookme.group.created';
    const SchduleGroupRetired       = 'bookme.group.retired';
    
    const BookingMade               = 'bookme.booking.created';
    const BookingCanceled           = 'bookme.booking.retired';
    
    const GroupExclusionRuleCreated = 'bookme.group.rule.exclusion.created';
    const GroupExclusionRuleRetired = 'bookme.group.rule.exclusion.retired';
    
    const GroupInclusionRuleCreated = 'bookme.group.rule.inclusion.created';
    const GroupInclusionRuleRetired = 'bookme.group.rule.inclusion.retired';
    
    const MemberExclusionRuleCreated = 'bookme.member.rule.exclusion.created';
    const MemberExclusionRuleRetired = 'bookme.member.rule.exclusion.retired';
    
    const MemberInclusionRuleCreated = 'bookme.member.rule.inclusion.created';
    const MemberInclusionRuleRetired = 'bookme.member.rule.inclusion.retired';
    
}
/* End of File */