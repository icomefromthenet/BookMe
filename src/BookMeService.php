<?php
namespace IComeFromTheNet\BookMe;

use DateTime;
use IComeFromTheNet\BookMe\BookMeContainer;
use IComeFromTheNet\BookMe\BookMeException;
use IComeFromTheNet\BookMe\BookMeEvents;


use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;
use IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterTeamCommand;
use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\ResumeScheduleCommand;

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
    public function addCalenderYears($iYearsToAdd)
    {
        $oCommand = new CalAddYearCommand($iYearsToAdd);
        
        return $this->getContainer()->getCommandBus()->handle($oCommand);
    }

    
    
    /**
     * Add a new timeslot to the database, if a duplicate exists an exception is thrown
     * 
     * @param integer $iTimeSlotLengthMinutes The slot length in minutes
     * @return integer the slot new database id
     * @access public
     * @throws BookMeException if duplicate exists or command failes for unknown reasons
     */ 
    public function addTimeslot($iTimeSlotLengthMinutes)
    {
        $oCommand = new SlotAddCommand($iTimeSlotLengthMinutes);
        
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
        $oCommand = new ToggleScheduleCarryCommand($iTimeslotDatabaseId);
        
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
     * Teams are used to group 1 to many schedules. 
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
    
    
    public function startSchedule($iMemberDatabaseId, $iTimeSlotDatabbaseId, $iCalendarYear)
    {
        $oCommand = new StartScheduleCommand($iMemberDatabaseId, $iTimeSlotDatabbaseId, $iCalendarYear);  
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return $oCommand->getScheduleId();
     
        
    }
    
    
    
    public function stopSchedule($iScheduleDatabaseId, DateTime $oStopDate)
    {
        $oCommand = new StopScheduleCommand($iScheduleDatabaseId, $oStopDate);  
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return true;
   
    }
    
    
    public function resumeSchedule($iScheduleDatabaseId)
    {
        $oCommand = new ResumeScheduleCommand($iScheduleDatabaseId);  
        
        $this->getContainer()->getCommandBus()->handle($oCommand);
       
        return true;
        
    }
    
    
    /**
     * 
     *   
     * A Member can belong to many teams over their lifetime but a member can only have a single
     * schedule for the current calender year and therefore belong to a signle team.
     *
     */ 
    public function assignTeamMember()
    {
        
    }
    
    
    
    public function withdrawlTeamMember()
    {
        
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