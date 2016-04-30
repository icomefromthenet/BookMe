<?php
namespace IComeFromTheNet\BookMe\Cron;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Cron\ParseCronException;
use IComeFromTheNet\BookMe\Cron\SlotFinderException;
use IComeFromTheNet\BookMe\Cron\SegmentParser;
use IComeFromTheNet\BookMe\Cron\SlotFinder;
use IComeFromTheNet\BookMe\Cron\ParsedRange;


/**
 * Parse a cron expression into repeat slots.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
class CronToQuery
{
    
    /**
     * @var Psr\Log\LoggerInterface
     */ 
    protected $oAppLogger;
    
    /**
     * @var Doctrine\DBAL\Connection
     */ 
    protected $oDatabase;
    
    /**
     * @var map of internal database table names to actual names
     */ 
    protected $aTables;
    
    /**
     * @var SegmentParser 
     */ 
    protected $oSegmentParser;
    
    /**
     * @var SlotFinder 
     */ 
    protected $oSlotFinder;
    

    protected function flatten($array) 
    {
        if (!is_array($array)) {
            // nothing to do if it's not an array
            return array($array);
        }
    
        $result = array();
        foreach ($array as $value) {
            // explode the sub-array, and add the parts
            $result = array_merge($result, $this->flatten($value));
        }
    
        return $result;
    }


    public function parse(CreateRuleCommand $oCommand)
    {
        $oLogger        = $this->oAppLogger;
        $oDatabase      = $this->oDatabase;
        $sSeriesTable   = $this->aTables['bm_rule_series'];
        $sRuleTmpTable  = $this->aTables['bm_tmp_rule_series'];
        $aRanges        = [];
        $aFlatRanges    = [];
        $aSql           = [];
        $sSql           = '';
        $aBinds         = [];
        $oSegmentParser = $this->oSegmentParser;
        $oSlotFinder    = $this->oSlotFinder;
        
        $oLogger->debug('CronToQuery starting parse');

        
        // Split the cron string
        
        // 1 = minutes 
        // 2 = hour    
        // 3 = Day Month
        // 4 = Month
        // 5 = Day of week 

        //$aRanges[] = $oSegmentParser->parseSegment(ParsedRange::TYPE_MINUTE,  $oCommand->getRuleRepeatMinute());
        
        //$aRanges[] = $oSegmentParser->parseSegment(ParsedRange::TYPE_HOUR,    $oCommand->getRuleRepeatHour());
                
        $aRanges[] = $oSegmentParser->parseSegment(ParsedRange::TYPE_DAYOFMONTH,  $oCommand->getRuleRepeatDayOfMonth());
        
        $aRanges[] = $oSegmentParser->parseSegment(ParsedRange::TYPE_MONTH,       $oCommand->getRuleRepeatMonth());
        
        $aRanges[] = $oSegmentParser->parseSegment(ParsedRange::TYPE_DAYOFWEEK,   $oCommand->getRuleRepeatDayOfWeek());
        
        $aFlatRanges = $this->flatten($aRanges);
        
        foreach($aFlatRanges as $oRange) {
            $oRange->validate();
        }
        
        
        // Run the ranges through the finder
        
        $oLogger->debug('CronToQuery starting finsSlots');
 
        
        $oSlotFinder->findSlots($oCommand,$aFlatRanges);
        
        // Insert finder result into rule series
        
        
        $oLogger->debug('CronToQuery building rule series from findslot results');
 
        try {


            
            $aSql[] = " INSERT INTO $sSeriesTable (`rule_id`, `rule_type_id`, `cal_year`, `slot_open`,`slot_close`) ";
            $aSql[] = " SELECT :iRuleId, :iRuleTypeId, y, opening_slot, closing_slot ";
            $aSql[] = " FROM $sRuleTmpTable ";
            
            $aBinds = [':iRuleId' => $oCommand->getRuleId(),':iRuleTypeId' => $oCommand->getRuleTypeId()];
            
            $sSql = implode(PHP_EOL, $aSql);
                
            $oIntType = TYPE::getType(TYPE::INTEGER);
            
            $iRowsAffected = $oDatabase->executeUpdate($sSql,$aBinds,[$oIntType,$oIntType]);
            
            if($iRowsAffected == 0) {
                 throw SlotFinderException::hasFailedToBuildRuleSeries($oCommand);
            }
            
            
        } catch(DBALException $e) {
            throw SlotFinderException::hasFailedToBuildRuleSeriesQuery($oCommand,$e);
        }
        
        $oLogger->debug('CronToQuery finished building rule series found '.$iRowsAffected.' slots');

        
        return $iRowsAffected;
    }
    
    

    


    public function __construct(LoggerInterface $oAppLogger, Connection $oDatabase, array $aTables, SegmentParser $oSegmentParser, SlotFinder $oSlotFinder)
    {
        $this->oAppLogger       = $oAppLogger;
        $this->oDatabase        = $oDatabase;
        $this->aTables          = $aTables;
        $this->oSegmentParser   = $oSegmentParser;
        $this->oSlotFinder      = $oSlotFinder;
    }
    
}
/* End of File */
