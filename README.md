BookMe
======

One very slow bus ride left me with some time for thought and this project is my proof of concept from my musings. This is not a full library solution and therefore has no composer download setup.
I hope that this project will help inspire if your given the task to design a reservation system.  

## A Quick Overview

The project has a few important concepts:

1. Calendar Tables
2. Timeslots
3. Members and Teams
4. Schedules 
5. Availability Rules
6. Bookings
7. Rollover


> Want the schema file in under database/schema.sql

### Calendar Tables

A Calendar table has one row for each day in a calendar year this table useful in queries that require a piviot/group by calendar dates.

### Timeslots

This project uses timeslots to split a single calendar day into even number of groups/slots. A slots length must be whole numbers e.g 5 minutes 10 minutes, 30 minutes

### Members and Teams

Each entity that requires a schedule is registered as a member and these members can be grouped into teams.

A member can only have one schedule per calendar year and all members of a team must use the same timeslot for their schedules.

### Schedules

As I wrote above a schedule lasts one calendar year and is the product of a single year in calendar table and a days timeslots. 
While a division of day won't change from year to year the slot boundres will be different with leap years considered. 

Each slot inside a schedule can be marked with rules that allow the booking routine to know if the slot is free or used.


### Availability Rules

Each Slot in a schedule can have the following rules applied

> Rules can be either **single day** or **repeat** A repeat rule is specified with a cron rule definition.


#### Availability

This rule says that a slot can be used for a booking.

#### Exclusion

This is used to stop a slot from being used for a booking if has an availability rule applied. e.g holiday rule

#### Inclusion Override

This rule overrides the exclusion and used to allow overtime periods in a schedule.

#### Closed.

The schedule has been marked closed e.g employee quits.

### Bookings

A booking is reserved one or more slots on a schedule. A booking must be within a single calendar day and does not repeat. 

A booking can be cleared by releasing slots and remove itself.

A booking can be in conflict if a later rule change has made these slots unavailable, I have built in a process to check for conflicts.


### Rollover

Range queries optmize poorly they can't use indexes and query performance quickly degrades worse smaller slots will cause a quicker degration.

I decided to limit the number of slots a query must page over by keeping schedules to a single year and by not for loading future schedule years.

The rollover process must:

1. Add a new calendar year. 
2. Build new set of timeslots for that year.
3. Create new schedules for the new year.
4. Copy any rules and apply them to new schedule (longest step).
5. Rollover teams for their new schedules (Teams are collection of members through current schedules).

See the Rollover unit test for examples. 

When design your own implementation you might decide that easier for you to generate future slots upfront and 
if have good hardware then its is the easier option.


Example
========

### Bootstrap the DI container and Service

```php

      // Bootstrap the service container and the service 

      $oContainer = new BookMeContainer($this->getDoctrineConnection(),$this->getLogger(),$this->getEventDispatcher(),$this->getNow());
      $oService = new BookMeService($oContainer);
      $oNow   = $this->getContainer()->getNow();
```

### Setup the Calendar and create Timeslots

```php
     
      // Create the Calendar 
      
      $oStartYear = clone $oNow;
      $oStartYear->setDate($oNow->format('Y'),1,1);
      
      $oService->addCalenderYears(1,$oStartYear);
      
      // Create Timeslots
      
      $iFiveMinuteTimeslot    = $oService->addTimeslot(5,$oNow->format('Y'));
      $iTenMinuteTimeslot     = $oService->addTimeslot(10,$oNow->format('Y'));
      $iSevenMinuteTimeslot    = $oService->addTimeslot(7,$oNow->format('Y'));

      $oService->toggleSlotAvability($iTenMinuteTimeslot);    

```

### Register Members and Teams and create Schedule for them

```php
  
      // Register new Members
  
      $iMemberOne   = $oService->registerMembership();
      $iMemberTwo   = $oService->registerMembership();
      $iMemberThree = $oService->registerMembership();
      $iMemberFour  = $oService->registerMembership();
    
      // Register new Teams    
    
      $iTeamOne     = $oService->registerTeam($iFiveMinuteTimeslot);
      $iTeamTwo     = $oService->registerTeam($iSevenMinuteTimeslot);
      
      
       // Schedules
      
      $iMemberOneSchedule   = $oService->startSchedule($iMemberOne,   $iFiveMinuteTimeslot, $oNow->format('Y'));
      $iMemberTwoSchedule   = $oService->startSchedule($iMemberTwo,   $iFiveMinuteTimeslot, $oNow->format('Y'));
      $iMemberThreeSchedule = $oService->startSchedule($iMemberThree, $iFiveMinuteTimeslot, $oNow->format('Y'));
      $iMemberFourSchedule  = $oService->startSchedule($iMemberFour,  $iFiveMinuteTimeslot, $oNow->format('Y'));
      
      // Stop a schedule
      
      $oService->stopSchedule($iMemberFourSchedule,$oNow->setDate($oNow->format('Y'),6,1));
      
      // Assign members to team 1 as their using $iFiveMinuteTimeslot
      
      $oService->assignTeamMember($iMemberOne,$iTeamOne,$iMemberOneSchedule);
      $oService->assignTeamMember($iMemberTwo,$iTeamOne,$iMemberTwoSchedule);
     
      $oService->assignTeamMember($iMemberThree,$iTeamOne,$iMemberThreeSchedule);
      $oService->assignTeamMember($iMemberFour,$iTeamOne,$iMemberFourSchedule);
```

### Register availability rules

```php
      // Create some Rules 
      
      $oSingleDate = clone $oNow;
      $oSingleDate->setDate($oNow->format('Y'),1,14);
        
      $oDayWorkDayRuleStart = clone $oNow;
      $oDayWorkDayRuleStart->setDate($oNow->format('Y'),1,1);
      
      $oDayWorkDayRuleEnd = clone $oNow;
      $oDayWorkDayRuleEnd->setDate($oNow->format('Y'),12,31);
      
      $oHolidayStart = clone $oNow;
      $oHolidayStart->setDate($oNow->format('Y'),8,7);
      
      $oHolidayEnd   = clone $oNow; 
      $oHolidayEnd->setDate($oNow->format('Y'),8,14);
      
      
      $iNineAmSlot = (12*9) *5;
      $iFivePmSlot = (12*17)*5;
      $iTenPmSlot  = (12*20)*5;    
        
      $iRepeatWorkDayRule    = $oService->createRepeatingWorkDayRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot,'1-5','*','2-12');
      $iSingleWorkDayRule    = $oService->createSingleWorkDayRule($oSingleDate,$iFiveMinuteTimeslot,$iFivePmSlot,$iTenPmSlot); 
      
      $iMidaySlot = (12*12)*5;
      $iOnePmSlot = (12*13)*5;
      
      $iEightPmSlot  = (12*18)*5;
      $iEightThirtyPmSlot = ((12*18) + 6)*5;
      
      $iRepeatBreakRule      = $oService->createRepeatingBreakRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iMidaySlot,$iOnePmSlot,'1-5','*','2-12');
      $iSingleBreakRule      = $oService->createSingleBreakRule($oSingleDate,$iFiveMinuteTimeslot,$iEightPmSlot,$iEightThirtyPmSlot); 
            
            
      $iRepeatHolidayRule    = $oService->createRepeatingHolidayRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot,'*','28-30','*');    
      $iSingleHolidayRule    = $oService->createSingleHolidayRule($oHolidayStart,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot);             
    
    
      $iRepeatOvertimeRule   = $oService->createRepeatingOvertimeRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot,'*','28-30','*');
      $iSingleOvertimeRule   = $oService->createSingleOvertmeRule($oHolidayStart,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot);
      
      
      // Link Rules to Schedule
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberOneSchedule,false);
    
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberTwoSchedule,false);
      
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberThreeSchedule,false);
      
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberFourSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberFourSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberFourSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberFourSchedule,false);
      
      //  Refresh the Members Schedules
      
      $oService->resfreshSchedule($iMemberOneSchedule);
      $oService->resfreshSchedule($iMemberTwoSchedule);
      $oService->resfreshSchedule($iMemberThreeSchedule);
      $oService->resfreshSchedule($iMemberFourSchedule);

```
### Take and Cancel Booking

```php

      // Take and clear the same booking     

      $oOpen  =  clone $oNow;
      $oOpen->setDate($oNow->format('Y'),1,14);
      $oOpen->setTime(17,0,0);
      
      $oClose = clone $oNow;
      $oClose->setDate($oNow->format('Y'),1,14);
      $oClose->setTime(17,20,0);
      
      $iBookingId = $oService->takeBooking($iMemberOneSchedule,$oOpen,$oClose);
      
      // Cancel the booking
      
      $oService->cancelBooking($iBookingId);
      

```
