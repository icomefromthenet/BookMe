<?php
namespace IComeFromTheNet\BookMe;

use DateTime;
use Pimple\Container;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Valitron\Validator;
use League\Tactician\CommandBus;
use League\Tactician\Handler\Locator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\CommandEvents\EventMiddleware;
use League\Tactician\CommandEvents\Event\CommandHandled;
use Bezdomni\Tactician\Pimple\PimpleLocator;

use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotToggleStatusCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterTeamCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverTeamsCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignTeamMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\WithdrawlTeamMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\TakeBookingCommand;
use IComeFromTheNet\BookMe\Bus\Command\ClearBookingCommand;

use IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;
use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverSchedulesCommand;
use IComeFromTheNet\BookMe\Bus\Command\ResumeScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RefreshScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignRuleToScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RemoveRuleFromScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverTimeslotCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverRulesCommand;



use IComeFromTheNet\BookMe\Bus\Handler\CalAddYearHandler;
use IComeFromTheNet\BookMe\Bus\Handler\SlotAddHandler;
use IComeFromTheNet\BookMe\Bus\Handler\SlotToggleStatusHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RegisterMemberHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RegisterTeamHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RolloverTeamsHandler;
use IComeFromTheNet\BookMe\Bus\Handler\WithdrawlTeamMemberHandler;
use IComeFromTheNet\BookMe\Bus\Handler\AssignTeamMemberHandler;
use IComeFromTheNet\BookMe\Bus\Handler\TakeBookingHandler;
use IComeFromTheNet\BookMe\Bus\Handler\ClearBookingHandler;

use IComeFromTheNet\BookMe\Bus\Handler\ToggleScheduleCarryHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RolloverSchedulesHandler;
use IComeFromTheNet\BookMe\Bus\Handler\StartScheduleHandler;
use IComeFromTheNet\BookMe\Bus\Handler\StopScheduleHandler;
use IComeFromTheNet\BookMe\Bus\Handler\ResumeScheduleHandler;
use IComeFromTheNet\BookMe\Bus\Handler\CreateRuleHandler;
use IComeFromTheNet\BookMe\Bus\Handler\AssignRuleToScheduleHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RefreshScheduleHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RemoveRuleFromScheduleHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RolloverTimeslotHandler;
use IComeFromTheNet\BookMe\Bus\Handler\RolloverRulesHandler;

use IComeFromTheNet\BookMe\Bus\Decorator\MaxBookingsDecorator;

use IComeFromTheNet\BookMe\Bus\Listener\CommandHandled as CustomHandler;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidatePropMiddleware;
use IComeFromTheNet\BookMe\Bus\Middleware\ExceptionWrapperMiddleware;
use IComeFromTheNet\BookMe\Bus\Middleware\UnitOfWorkMiddleware;

use IComeFromTheNet\BookMe\Cron\CronToQuery;
use IComeFromTheNet\BookMe\Cron\SegmentParser;
use IComeFromTheNet\BookMe\Cron\SlotFinder;

/**
 * Book Me DI Container
 * 
 * Your Database Admin must allow user variables for this code to function.
 *
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
class BookMeContainer extends Container
{
    
    
    
    
    

    public function __construct(Connection $dbal,LoggerInterface $logger,EventDispatcherInterface $dispatcher, DateTime $oNow)
    {
        $this['database'] = $dbal;
        $this['logger']   = $logger;
        $this['eventDispatcher'] = $dispatcher;
        $this['booted'] = false;
        $this['now'] = $oNow;
        
    }
    
    /**
     * Gets the Service ready for operation.
     * 
     * 1. Build Dependency Graph
     * 2. Wire up event handlers
     * 
     * Will only boot once.
     * 
     * @return $this;
     * @access public
     */ 
    public function boot(array $aTableNames = array())
    {
        if(false === $this['booted']) {
        
            # Custom Validators
        
            Validator::addRule('calendarSameYear', function($field, $value, array $params, array $fields) {
                 
                 $vtime = ($value instanceof \DateTime) ? $value->format('Y') : date('Y',$value);
                 $ptime = ($fields[$params[0]] instanceof \DateTime) ? $fields[$params[0]]->format('Y') : date('Y',$fields[$params[0]]);
                 
                 return $vtime == $ptime;
                
            }, 'Calendar Year do not match');
        
        
        
            # default table name map
            
            $this['tableMap'] = array_merge(array(
                'bm_calendar'           => 'bm_calendar',
                'bm_calendar_weeks'     => 'bm_calendar_weeks',
                'bm_calendar_months'    => 'bm_calendar_months',
                'bm_calendar_quarters'  => 'bm_calendar_quarters',
                'bm_calendar_years'     => 'bm_calendar_years',
                
                'bm_timeslot'           => 'bm_timeslot',
                'bm_timeslot_day'       => 'bm_timeslot_day',
                'bm_timeslot_year'      => 'bm_timeslot_year',
                
                'bm_schedule_membership'   => 'bm_schedule_membership',
                'bm_schedule_team'         => 'bm_schedule_team',
                'bm_schedule_team_members' => 'bm_schedule_team_members',
                'bm_schedule'              => 'bm_schedule',
                'bm_schedule_slot'         => 'bm_schedule_slot',
                
                'bm_booking'               => 'bm_booking',
                'bm_booking_conflict'      => 'bm_booking_conflict',
                
                'bm_rule_type'             => 'bm_rule_type',
                'bm_rule'                  => 'bm_rule',
                'bm_rule_series'           => 'bm_rule_series',
                'bm_rule_schedule'         => 'bm_rule_schedule',
                
                 // Temp tables 
                'bm_tmp_rule_series'        => 'bm_tmp_rule_series',
                
                
            ),$aTableNames);
        
        
            $this['commandBus.handler'] = function($c) {
                return new CustomHandler($c->getEventDispatcher());
            };
        
        
            # Command Bus Handlers
            
            $this['handlers.cal.addyear'] = function($c) {
                return new  CalAddYearHandler($c->getTableMap(), $c->getDatabaseAdapter()); 
                
            };
            
            $this['handlers.slot.add'] = function($c) {
                return new SlotAddHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.slot.toggle'] = function($c) {
                return new SlotToggleStatusHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.member.register'] = function($c) {
                return new RegisterMemberHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
          
            $this['handlers.team.register'] = function($c) {
                return new RegisterTeamHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.team.rollover'] = function($c) {
                return new RolloverTeamsHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.team.assign'] = function($c) {
                return new AssignTeamMemberHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.team.withdrawl'] = function($c) {
                return new WithdrawlTeamMemberHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.schedule.toggle'] = function($c) {
                return new ToggleScheduleCarryHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.booking.take'] = function($c) {
                return new MaxBookingsDecorator(new TakeBookingHandler($c->getTableMap(), $c->getDatabaseAdapter()),$c->getTableMap(), $c->getDatabaseAdapter());
            };
            
            $this['handlers.schedule.clear'] = function($c) {
                return new ClearBookingHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.schedule.rollover'] = function($c) {
                return new RolloverSchedulesHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
        
            $this['handlers.schedule.start'] = function($c) {
                return new StartScheduleHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
        
            $this['handlers.schedule.stop'] = function($c) {
                return new StopScheduleHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.schedule.resume'] = function($c) {
                return new ResumeScheduleHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
        
            $this['handlers.schedule.refresh'] = function($c) {
                 return new RefreshScheduleHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
        
        
            $this['handlers.rule.create'] = function($c) {
                return new CreateRuleHandler($c->getTableMap(), $c->getDatabaseAdapter(), $c->getCronToQuery());  
            };
            
            $this['handlers.rule.assign'] = function($c) {
                return new AssignRuleToScheduleHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.rule.remove'] = function($c) {
                return new RemoveRuleFromScheduleHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
            $this['handlers.slot.rollover'] = function($c) {
                return new RolloverTimeslotHandler($c->getTableMap(), $c->getDatabaseAdapter());  
            };
            
             $this['handlers.rule.rollover'] = function($c) {
                return new RolloverRulesHandler($c->getTableMap(), $c->getDatabaseAdapter(), $c->getCronToQuery());  
            };
          
            
            
            # Command Bus
            
            $this['commandBus'] = function($c){
                
                $aLocatorMap = [
                    CalAddYearCommand::class            => 'handlers.cal.addyear',
                    SlotAddCommand::class               => 'handlers.slot.add',
                    SlotToggleStatusCommand::class      => 'handlers.slot.toggle',
                    RegisterMemberCommand::class        => 'handlers.member.register',
                    RegisterTeamCommand::class          => 'handlers.team.register',
                    RolloverSchedulesCommand::class     => 'handlers.schedule.rollover',
                    RolloverTeamsCommand::class         => 'handlers.team.rollover',
                    TakeBookingCommand::class           => 'handlers.booking.take',
                    ClearBookingCommand::class          => 'handlers.schedule.clear',
                    ToggleScheduleCarryCommand::class   => 'handlers.schedule.toggle',
                    StartScheduleCommand::class         => 'handlers.schedule.start',
                    StopScheduleCommand::class          => 'handlers.schedule.stop',
                    ResumeScheduleCommand::class        => 'handlers.schedule.resume',
                    CreateRuleCommand::class            => 'handlers.rule.create', 
                    AssignRuleToScheduleCommand::class  => 'handlers.rule.assign',
                    RefreshScheduleCommand::class       => 'handlers.schedule.refresh',
                    RemoveRuleFromScheduleCommand::class => 'handlers.rule.remove',
                    RolloverTimeslotCommand::class      => 'handlers.slot.rollover',
                    RolloverRulesCommand::class         => 'handlers.rule.rollover',
                    AssignTeamMemberCommand::class      => 'handlers.team.assign',
                    WithdrawlTeamMemberCommand::class   => 'handlers.team.withdrawl',
                ];
        
             
                // Create the Middleware that loads the commands
             
                $oCommandNamingExtractor = new ClassNameExtractor();
                $oCommandLoadingLocator  = new PimpleLocator($c, $aLocatorMap);
                $oCommandNameInflector   = new HandleInflector();
                    
                $oCommandMiddleware      = new CommandHandlerMiddleware($oCommandNamingExtractor,$oCommandLoadingLocator,$oCommandNameInflector);
                
                // Create exrta Middleware 
 
                $oEventMiddleware       = new EventMiddleware();
                $oEventMiddleware->addListener(
                	'command.handled',
                	function (CommandHandled $event) use ($c) {
                    	$c->getBusEventHandler()->handle($event);
                	}
                );
                
                
                $oLockingMiddleware     = new LockingMiddleware();
                $oValdiationMiddleware  = new ValidatePropMiddleware();
                $oExceptionMiddleware   = new ExceptionWrapperMiddleware();
                $oUnitOfWorkMiddleware  = new UnitOfWorkMiddleware($c->getDatabaseAdapter());
        
                // create the command bus
        
                $oCommandBus = new CommandBus([
                            $oExceptionMiddleware,
                            $oEventMiddleware,
                            $oLockingMiddleware,
                            $oUnitOfWorkMiddleware,
                            $oValdiationMiddleware,
                            $oCommandMiddleware
                ]);
                
                return $oCommandBus;
                
            };
            
        
            # Cron to Query
            $this['slotFinder'] = function($c) {
                return new SlotFinder($c->getLogger(), $c->getDatabase(), $c->getTableMap());
                
            };
            
            $this['cronSegmentParser'] = function($c) {
              return new SegmentParser($c->getLogger());  
            };
        
            $this['cronToQuery'] = function($c) {
                return new CronToQuery($c->getLogger(), $c->getDatabase(), $c->getTableMap(), $c->getCronSegementParser(),$c->getSlotFinder());
            };
            
            
        }
        
        
        return $this;
        
    }
    
    
    
    

    
    
    
    //-------------------------------------------------------------------
    # Internal Services
    
    
    /**
     * Returns this command bus
     * 
     * @return League\Tactician\CommandBus
     */ 
    public function getCommandBus()
    {
        return $this['commandBus'];
    }
    
    
    public function getTableMap()
    {
        return $this['tableMap'];
    }
    
    /**
     * Return the custom event bus listener
     * 
     * @return IComeFromTheNet\BookMe\Bus\Listener\CommandHandled
     */ 
    public function getBusEventHandler()
    {
        return $this['commandBus.handler'];
    }
    
    //--------------------------------------------------------------------
    # External Dependecies
    
    /**
     * Loads the doctrine database
     *
     * @return Doctrine\DBAL\Connection
     */
    public function getDatabase()
    {
        return $this['database'];
    }
    
    /**
     * Loads the doctrine database
     *
     * @return Doctrine\DBAL\Connection
     */
    public function getDatabaseAdapter()
    {
        return $this['database'];
    }
    
    
    /**
     * Loads the application log
     *
     * @return Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this['logger'];
    }
    
    /**
     * Loads the application log
     *
     * @return Symfony\Component\EventDispatcher\EventDispatcherInterface;
     */
    public function getEventDispatcher()
    {
        return $this['eventDispatcher'];
    }
    
    /**
     * Return the assigned processing date ie NOW()
     * 
     * return DateTime
     */ 
    public function getNow()
    {
        return $this['now'];
    }
    
    /**
     * Load the cronToQuery parser used n rules engine
     * 
     * @return CronToQuery
     */ 
    public function getCronToQuery()
    {
        return $this['cronToQuery'];
    }
    
    /**
     * Return the cron slot finder
     * 
     * @return SlotFinder
     */ 
    public function getSlotFinder()
    {
        return $this['slotFinder'];
    }
    
    /**
     * Return the cron segment parser
     * 
     * @return SegmentParser
     */ 
    public function getCronSegementParser()
    {
        return $this['cronSegmentParser'];
    }
    
}
/* End of File */