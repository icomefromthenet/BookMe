<?php
namespace IComeFromTheNet\BookMe;


class BookMeService
{


    //----------------------------------------
    // Membership, Schedules and Schedule Groups.
    // 
    // A Member is our internal representation of an external entity it may be people, meeting rooms, hotel rooms.
    // any resource that requirs a schedule.
    //
    // Schedule is our abstraction represing the timeline of availablity that a member has. 
    //
    // Schedule Groups is our abstraction for the relationships between members for example people might have teams,workgruops,departments
    // as these relations change over time we relate them to our schedule abstraction and not member abstraction.
    //
    //----------------------------------------
    
    /**
     * Register an entity for scheduling. This may be a user account, a resource
     * 
     * This does not store a reference to that entity this will be the responsibility
     * of library user.
     * 
     * A membership does not expire and can not be disabled. To stop scheduling for the
     * entity all schedules should be retired.
     * 
     * @return integer the membership number
     */
    public function registerMembership()
    {
        $membershipID = null;
        $db           = $this->getDatabaseAdapter();
        
        try {
            # start a transaction 
            $db->beginTransaction();
             
            $db->executeQuery('call bm_add_membership(@membershipID)',array(),array());
            
            $membershipID = (int) $db->fetchColumn('SELECT @membershipID as member',array(),0);
            
            # dispatch the event is sucessful
            $this->getEventDispatcher()->dispatch(BookMeEvents::MemberRegistered,new MembershipEvent($membershipID));    
            
            # no errors commit the transation
            $db->commit();
            
        } catch(DBHException $e) {
            # from the stored procedure execution
            $db->rollBack();
            throw new BookMeException($e->getMessage(),0,$e);
            
        } catch(BookMeException $e) {
            # come back from our logger
            $db->rollBack();
            throw $e;
        }
        
        return $membershipID;
        
    }
    
    /**
     * Setup a schedule for a member.
     * 
     * To sucessfuly create a schedule the following must be true.
     * 
     * There can be only a single schedule for a given memeber during a given interval.
     * A member can have a 2nd or third schedule but only 1 can be open.
     * 
     * 1. Schedule must reference a member.
     * 2. Schedule must reference a group.
     * 3. For each day the schedule is open the group it references must be open too. (Group Validtime must encompass this schedules validtime)
     * 4. All other schedules must be retired, as of the opening date.  
     * 5. The open/close interval must be 1 day or more.
     * 
     * @param integer $memberId   The Member
     * @param integer $groupId    The Schedule Group 
     * @param DateTime $openDate  The date to open this schedule from will default to NOW()
     * @param DateTime $closeDate The last date this schedule is valid too, will default o 3000-01-01
     * @return integer the ID of the new schedule
     * 
     */ 
    public function registerSchedule($memberId,$groupId, DateTime $openDate = null, DateTime $closeDate = null)
    {
        
        
    }
    
    /**
     * Retire a schedule.
     * 
     * Bookings can no longer be taken for this schdule after the cut off date.
     * There must be no bookings after the cutoff date.
     * The cutoff date can not be during another member schedules validtime.
     * 
     * @param integer $scheduleID
     * @param DateTime $closeDate
     */ 
    public function retireSchedule($scheduleID, DateTime $closeDate)
    {
        
        
    }
    
    /**
     * This will delete an unsed schedule from the database.
     * 
     * If this schedule is used in booking than the FK relation will
     * stop the delete operation.
     * 
     * This can be used to remove mistakenly added future schedules.
     * eg. if you added a schedule to start next week but deided to change
     * the schedule group as no books you can delete it instead
     * 
     */ 
    public function removeUnusedSchedule($scheduleID)
    {
        
        
    }
    
    
    
    /**
     * Create a schedule group that are used to group schedules.
     * 
     * A schedule only have single group.
     * 
     * The opening/closing interval must be 1 day or more.
     * A group name does not have to be unique.
     * 
     * @param string    $name           char(100)
     * @param string    $description    char(255)
     * @param DateTime  $openDate       an opening date, default to NOW()
     * @param DateTime  $closeDate      a closing date, default to 3000-01-01
     * @return integer                  the Id of the new group
     */
    public function createGroup($name,$description,DateTime $openDate = null,DateTime $closeDate = null)
    {
        
    }
    
    /**
     * Retire A schedule group.
     * 
     * This group can no longer be refernced by schedules and  group exclusion/inclusion rules.
     * 
     * Pre-req
     * 1. All Schedules that reference this group are closed before the given date.
     * 2. If all schedules must be closed all bookings are satisfied as of same date.
     * 
     * @param integer  $groupId     The group database ID
     * @param DateTime $closeDate   The close date, default to NOW()
     * 
     */ 
    public function retireGroup($groupId, Datetime $closeDate = null)
    {
        
    }

    //----------------------------------------
    //
    //
    //
    //----------------------------------------

    
    public function addGroupIncludeRule()
    {
        
    }
    
    public function retireGroupIncludeRule()
    {
        
    }
    
    public function addGroupExcludeRule()
    {
        
    }
    
    public function retireGroupExcludeRule()
    {
        
    }
    
    public function addMemberIncludeRule()
    {
        
    }
    
    public function addMemberExcludeRule()
    {
        
    }
    
    public function retireMemberIncludeRule()
    {
        
    }
    
    
    public function retireMemberExcludeRule()
    {
        
    }
    
    
    //----------------------------------------
    //
    //
    //
    //----------------------------------------

    
    public function loadSchedule()
    {
        
    }
    
    
    public function loadGroupSchedule()
    {
        
    }
    
    
    public function makeBooking()
    {
        
    }
    
    
    public function cancelBooking()
    {
        
        
    }

    public function loadConflictBookings()
    {
        
    }
    
    public function executeConflictBookingCheck()
    {
        
        
    }
    
    //----------------------------------------
    //
    //
    //
    //----------------------------------------

    
    public function loadBookingHistory()
    {
        
        
    }
    
    
    public function loadMembershipRegisterationHistory()
    {
        
    }
    
    
    public function loadScheduleRegistrationHistory()
    {
        
    }
    
    
    public function loadExclusionRulesHistory()
    {
        
    }
    
    public function loadInclusionRulesHistory()
    {
        
    }


}
/* End of File */