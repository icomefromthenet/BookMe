<?php
namespace IComeFromTheNet\BookMe;

use DateTime;
use IComeFromTheNet\BookMe\BookMeContainer;
use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\BookMeEvents;


use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;
use IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotToggleStatusCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterTeamCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignTeamMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\WithdrawlTeamMemberCommand;


use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\ResumeScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignRuleToScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RemoveRuleFromScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RefreshScheduleCommand;

use IComeFromTheNet\BookMe\Bus\Command\TakeBookingCommand;
use IComeFromTheNet\BookMe\Bus\Command\ClearBookingCommand;

/**
 * Core Library Service.
 * 
 * Before this library can be used you must setup the schema and inserted any
 * basic data e.g the INTS DB Table needs seed vales.
 * 
 * Before you can take your first booking but after built the schema and seed you must
 * 
 * 1. Add 1 to many Calendar Years (recommend 10 at most).
 * 2. Add 1 timeslot e.g 5 minutes.
 * 3. Register 1 member.
 * 4. Create a schedule for that member.
 * 5. Create at least 1 Avability Rule.
 * 
 * 
 * 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class BookMeService
{

    /**
     * @var IComeFromTheNet\BookMe\BookMeContainer 
    */
    protected $oContainer;
    
    
    /**
     * Class Constructor
     * 
     * @param   BookMeContainer     $oContainer     The Service Container
     */ 
    public function __construct(BookMeContainer $oContainer)
    {
        $this->oContainer = $oContainer;
    }



    //----------------------------------------
    // Calendar, Timeslots 
    // 
    //
    //----------------------------------------

    /**
     * Add a new calendar years to the calender tables.
     * 
     * @param integer $iYearsToAdd  The number of years to add to calender.
     * @throws BookMeException
     * @return Boolean 
     * @access public
     */ 
    public function addCalenderYears($iYearsToAdd, DateTime $oStartYear = null)
    {
        $oCommand = new CalAddYearCommand($iYearsToAdd, $oStartYear);
        
        return $this->getContainer()->getCommandBus()->handle($oCommand);
    }

    
    
    /**
     * Add a new timeslot to the database, if a duplicate exists an exception is thrown
     * 
     * @param integer $iTimeSlotLengthMinutes   The slot length in minutes
     * @param integer $iCalYear                 The Calendar year to add this slot onto
     * @return integer                          The slot new database id
     * @access public
     * @throws BookMeException if duplicate exists or command failes for unknown reasons
     */ 
    public function addTimeslot($iTimeSlotLengthMinutes, $iCalYear)
    {
        $oCommand = new SlotAddCommand($iTimeSlotLengthMinutes,$iCalYear);
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getTimeSlotId();
    }
    
    
    
    
    /**
     * Toggle between a timeslot between active and inactive
     * 
     * @return boolean true if command successful
     * @throws BookMeException if their are no updates
     * 
     */ 
    public function toggleSlotAvability($iTimeslotDatabaseId)
    {
        $oCommand = new SlotToggleStatusCommand($iTimeslotDatabaseId);
        
        return $this->getContainer()->getCommandBus()->handle($oCommand);
    }
    
    
    


    //----------------------------------------
    // Membership, Schedules and Teams
    //
    //----------------------------------------
    
    /**
     * Register an existing entity for scheduling. 
     * 
     * This does not store a reference to that  existing entity this will be the responsibility
     * of library user.
     * 
     * A membership does not expire and can not be disabled. To stop scheduling for the
     * entity all schedules should be retired by adding an exclusion rule for the remainder of the
     * schedule calendar year.
     * 
     * @return integer the membership database id
     * @access public
     * @throws IComeFromTheNet\BookMe\Bus\Exception\MembershipException if operation fails
     */
    public function registerMembership()
    {
        $oCommand = new RegisterMemberCommand();
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return $oCommand->getMemberId();
        
    }
    
    /**
     * Register a new team.
     * 
     * Each Schedule Assigned to a team must have the same timeslot
     * 
     * @access public
     * @return integer  The new team database id
     * @throws IComeFromTheNet\BookMe\Bus\Exception\MembershipException if operation fails
     * 
     */ 
    public function registerTeam($iTimeslotDatabaseId)
    {
        $oCommand = new RegisterTeamCommand($iTimeslotDatabaseId);   
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return $oCommand->getTeamId();
     
        
    }
    
    
    /**
     * Start a new schedule for a member. 
     * 
     * @param integer   $iMemberDatabaseId      The member to use
     * @param integer   $iTimeSlotDatabbaseId   The timslot which split a calendar day
     * @param integer   $iCalendarYear          The Calendar year to use
     * 
     */
    public function startSchedule($iMemberDatabaseId, $iTimeSlotDatabbaseId, $iCalendarYear)
    {
        $oCommand = new StartScheduleCommand($iMemberDatabaseId, $iTimeSlotDatabbaseId, $iCalendarYear);  
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return $oCommand->getScheduleId();
     
        
    }
    
    
    /**
     * Stop a schedule from taking new bookings and prevent from being rollover
     * 
     * @param integer   $iScheduleDatabaseId    The Schedule to close
     * @param DateTime  $oStopDate              The date during the calendar year to stop from
     */ 
    public function stopSchedule($iScheduleDatabaseId, DateTime $oStopDate)
    {
        $oCommand = new StopScheduleCommand($iScheduleDatabaseId, $oStopDate);  
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return true;
   
    }
    
    /**
     * Opens a closed schedule to take new books and rollover.
     * 
     * @param integer   $iScheduleDatabaseId    The schedule to open.
     */ 
    public function resumeSchedule($iScheduleDatabaseId)
    {
        $oCommand = new ResumeScheduleCommand($iScheduleDatabaseId);  
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return true;
        
    }
    
    
    /**
     * Assigns a member to a team
     * 
     * Note:
     *  1. A Member can only have 1 schedule per calendar year and belong to one timeslot per year
     *  2. Team members must share the same timeslot.
     *  3. While a member can belong to many teams each team must share the same timeslot.
     * 
     * @param   integer     $iMemberDatabaseId      The member to assign
     * @param   integer     $iTeamDatabaseId        The team to use
     * @param   integer     $iScheduleId            The Schedule to use
     *
     */ 
    public function assignTeamMember($iMemberDatabaseId, $iTeamDatabaseId, $iScheduleId)
    {
        
        $oCommand = new AssignTeamMemberCommand($iMemberDatabaseId, $iTeamDatabaseId, $iScheduleId);
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
         
        return true;
    }
    
    
    /**
     * Remove a member from a team
     * 
     * @param   integer     $iMemberDatabaseId     The member to assign
     * @param   integer     $iTeamDatabaseId       The Team to remove from
     * @param   integer     $iScheduleId           The Schedule to use
     */ 
    public function withdrawlTeamMember($iMemberDatabaseId, $iTeamDatabaseId, $iScheduleId)
    {
        
        $oCommand = new WithdrawlTeamMemberCommand($iMemberDatabaseId, $iTeamDatabaseId, $iScheduleId);
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return true;
    }
    
    
    //----------------------------------------
    // Rules
    //
    //----------------------------------------
   
    /**
     * Create a rule that marks slots as open and ready for work, this rule apply to a single calendar day
     * 
     * @param DateTime  $oDate               The Calendar date to apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     */ 
    public function createSingleWorkDayRule(DateTime $oDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot)
    {
        $oStartDate = clone $oDate;
        $oEndDate  = clone $oDate;
        
        $oCommand = new CreateRuleCommand($oStartDate, $oEndDate, 1, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, '*', '*', '*', true);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
        
    }
   
    /**
     * Create a rule that marks slots as open and ready for work, this rule apply to many calendar days
     * 
     * @param DateTime  $oStartFromDate      The Calendar date to start apply this rule to.
     * @param DateTime  $oEndtAtDate         The Calendar date to stop apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     * @param string    $sRepeatDayofweek   The day of week cron def
     * @param string    $sRepeatDayofmonth  The day of month cron def
     * @param string    $sRepeatMonth       The month cron def
     */ 
    public function createRepeatingWorkDayRule(DateTime $oStartFromDate, DateTime $oEndtAtDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek, $sRepeatDayofmonth, $sRepeatMonth)
    {
        $oCommand = new CreateRuleCommand($oStartFromDate, $oEndtAtDate, 1, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek,$sRepeatDayofmonth,$sRepeatMonth,false);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
        
    }
    
    /**
     * Create a rule that marks slots as closed/busy, this rule apply to a single calendar day
     * 
     * @param DateTime  $oDate               The Calendar date to apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     */ 
    public function createSingleBreakRule(DateTime $oDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot) 
    {
        $oStartDate = clone $oDate;
        $oEndDate  = clone $oDate;
        
        $oCommand = new CreateRuleCommand($oStartDate, $oEndDate, 2, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, '*', '*', '*', true);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
        
    }     
    
    /**
     * Create a rule that marks slots as closed, this rule apply to many calendar days
     * 
     * @param DateTime  $oStartFromDate      The Calendar date to start apply this rule to.
     * @param DateTime  $oEndtAtDate         The Calendar date to stop apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     * @param string    $sRepeatDayofweek   The day of week cron def
     * @param string    $sRepeatDayofmonth  The day of month cron def
     * @param string    $sRepeatMonth       The month cron def
     */ 
    public function createRepeatingBreakRule(DateTime $oStartFromDate, DateTime $oEndtAtDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek, $sRepeatDayofmonth, $sRepeatMonth)
    {
        
        $oCommand = new CreateRuleCommand($oStartFromDate, $oEndtAtDate, 2, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek,$sRepeatDayofmonth,$sRepeatMonth,false);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
        
    }
   
    /**
     * Create a rule that marks slots as closed, this rule apply to a single calendar day
     * 
     * @param DateTime  $oDate               The Calendar date to apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     */ 
    public function createSingleHolidayRule(DateTime $oDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot)
    {
        $oStartDate = clone $oDate;
        $oEndDate  = clone $oDate;
        
        $oCommand = new CreateRuleCommand($oStartDate, $oEndDate, 3, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, '*', '*', '*', true);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
    }
    
    /**
     * Create a rule that marks slots as closed for work, this rule apply to many calendar days
     * 
     * @param DateTime  $oStartFromDate      The Calendar date to start apply this rule to.
     * @param DateTime  $oEndtAtDate         The Calendar date to stop apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     * @param string    $sRepeatDayofweek   The day of week cron def
     * @param string    $sRepeatDayofmonth  The day of month cron def
     * @param string    $sRepeatMonth       The month cron def
     */ 
    public function createRepeatingHolidayRule(DateTime $oStartFromDate, DateTime $oEndtAtDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek, $sRepeatDayofmonth, $sRepeatMonth)
    {
        
        
        $oCommand = new CreateRuleCommand($oStartFromDate, $oEndtAtDate, 3, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek,$sRepeatDayofmonth,$sRepeatMonth,false);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
        
    }
    
    /**
     * Create a rule that marks slots as open and ready for work event if marked as a break/holiday, this rule apply to a single calendar day
     * 
     * @param DateTime  $oDate               The Calendar date to apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     */ 
    public function createSingleOvertmeRule(DateTime $oDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot)
    {
        $oStartDate = clone $oDate;
        $oEndDate  = clone $oDate;
        
        $oCommand = new CreateRuleCommand($oStartDate, $oEndDate, 4, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, '*', '*', '*', true);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
        
    }
    
    /**
     * Create a rule that marks slots as open and ready for work even if marked for break/holiday, this rule apply to many calendar days
     * 
     * @param DateTime  $oStartFromDate      The Calendar date to start apply this rule to.
     * @param DateTime  $oEndtAtDate         The Calendar date to stop apply this rule to.
     * @param integer   $iTimeslotDatabaseId The database id of the timeslot
     * @param integer   $iOpeningSlot        The slot number during the day to start 
     * @param integer   $iClosingSlot        The closing slot number to stop after
     * @param string    $sRepeatDayofweek   The day of week cron def
     * @param string    $sRepeatDayofmonth  The day of month cron def
     * @param string    $sRepeatMonth       The month cron def
     */ 
    public function createRepeatingOvertimeRule(DateTime $oStartFromDate, DateTime $oEndtAtDate, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek, $sRepeatDayofmonth, $sRepeatMonth)
    {
        
        $oCommand = new CreateRuleCommand($oStartFromDate, $oEndtAtDate, 4, $iTimeslotDatabaseId, $iOpeningSlot, $iClosingSlot, $sRepeatDayofweek,$sRepeatDayofmonth,$sRepeatMonth,false);
        
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getRuleId();
        
    }
    
    
    /**
     * Create a relation between a rule and a schedule, on the next schedule refresh this rule
     * will be applied
     * 
     * @param   Integer     $iRuleDatabaseId        The rule to link to a schedule
     * @param   Integer     $iScheduleDatabaseId    The schedule to link the rule to
     * @param   Boolean     $bRolloverRule          If This rule should be rolled over come new schedule year
     * 
     */ 
    public function assignRuleToSchedule($iRuleDatabaseId, $iScheduleDatabaseId, $bRolloverRule = false)
    {
        $oCommand = new AssignRuleToScheduleCommand($iScheduleDatabaseId,$iRuleDatabaseId,$bRolloverRule);    
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return true;
    }
    
    /**
     * This will delete a realtion bewtween a rule and a schedule, on the next schedule refresh it
     * will be removed.
     * 
     * @param   Integer     $iRuleDatabaseId        The rule to unlink from a schedule
     * @param   Integer     $iScheduleDatabaseId    The schedule to unlink the rule from
     * 
     */ 
    public function removeRuleFromSchedule($iRuleDatabaseId, $iScheduleDatabaseId)
    {
        $oCommand = new RemoveRuleFromScheduleCommand($iScheduleDatabaseId, $iRuleDatabaseId);
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return true;
    }
    
    
    /**
     * Apply rules to a schedule and remove any those not related.
     * 
     * This should be called after all relations are done
     * 
     * @param   Integer     $iScheduleDatabaseId    The schedule to refresh 
     */ 
    public function resfreshSchedule($iScheduleDatabaseId)
    {
        $oCommand = new RefreshScheduleCommand($iScheduleDatabaseId);
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return true;
    }
    
    
    //----------------------------------------
    // Booking
    //
    //----------------------------------------
   
    /**
     * This will create a booking reserving the chosen slots on the schedule
     * 
     * If slots can not be reserved then an exception will be thrown
     * 
     * @param integer       $iScheduleId        The database id of the schedule to use      
     * @param DateTime      $oOpeningSlot       The opening of the first Slot
     * @param DateTime      $oClosingSlot       The closing date the last slot. 
     * 
     * @return integer the booking database id
     */ 
    public function takeBooking($iScheduleId, DateTime $oOpeningSlot, DateTime $oClosingSlot)
    {
         $oCommand = new TakeBookingCommand($iScheduleId, $oOpeningSlot, $oClosingSlot);
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return $oCommand->getBookingId();
    }
    
    /**
     * This will remove a booking which free the assigned slots
     * 
     * 1. Remove the booking record
     * 2. Clear booking from schedule
     * 3. Remove any conflict list
     * 
     * @param integer $iBookingId   The Booking Database id
     * 
     */ 
    public function cancelBooking($iBookingId)
    {
        $oCommand = new ClearBookingCommand($iBookingId);
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
        
        return true;
    }
   
    
    //--------------------------------------------------------------------------
    # Accessors
    
    /**
     * Fetch this services DI container
     * 
     * @return IComeFromTheNet\BookMe\BookMeContainer
     */ 
    public function getContainer()
    {
        return $this->oContainer;
    }


}
/* End of File */