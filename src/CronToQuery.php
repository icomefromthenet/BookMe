<?php
namespace IComeFromTheNet\BookMe;

use Psr\Log\LoggerInterface;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\ParseCronException;


class CronToQuery
{
    
    /**
     * @var Psr\Log\LoggerInterface
     */ 
    protected $oAppLogger;
    
    
    
    
    public function __construct(LoggerInterface $oAppLogger)
    {
        $this->oAppLogger = $oAppLogger;
        
    }
    
    
    
    public function parse()
    {
        
        
    }
    
    

    public function parseSegment($sCronType, $sCronRegex)
    {
        
        $sFilteredCron      = '';
	    $iRangeOccurances   = null;
	    $i                  = 0;
    	$splitValue         = '';
    	$openValue          = 0;
        $closeValue         = 0;
        $incrementValue     = 0;
       	$minOpenValue       = 0;
    	$maxCloseValue      = 0;
    	$sFormatSTR         = '*';

	    //fetch the default min and max range for this cron section
	    $iMinOpenValue      = $this->testMin($sCronType);
	    $iMaxCloseValue     = $this->testMax($sCronType);
	
        
        if ($sFilteredCron == '*' ) {
            
            $this->oAppLogger->debug('filteredCron is eq *');
            
        } 
        else {
            $this->oAppLogger->debug('filteredCron will be parsed');
            
            // split our set and parse each range declaration.
            $iRangeOccurances = strlen(filteredCron) - strlen(str_replace($sFilteredCron, ',', ''))+1;
		    $i                = 1;
		    $sIncrementValue  = 0;

            $this->oAppLogger->debug("rangeOccurances eq to $iRangeOccurances");
            
            while($i <= $iRangeOccurances) {
                
                
                
            }
            
        }

        
        
    }

    
    

    // -------------------------------------------------------------------------
    # Parse Helpers
    
    /**
     * Parse pattern A
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse
     */ 
    public function getPatternA($sCronType,$sCronString)
    {
        
        switch($sCronType) {
            case 'minute':
                $sCronRegex = '^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})/([0-9]+)$';
            break;
            case 'hour':       
                $sCronRegex = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})-([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})/([0-9]+)$';
            break;
            case 'dayofmonth': 
                $sCronRegex = '^([0-6]{1})-([0-6]{1})/([0-9]+)$';
            break;
            case 'dayofweek': 
                $sCronRegex = '^([0-6]{1})-([0-6]{1})/([0-9]+)$';
            break;
            case 'month':      
                $sCronRegex  = '^([1-9]{1}|[1-2][1-2]{1})-([1-9]{1}|[1-2][1-2]{1})/([0-9]+)$';
            break;    
            default: throw ParseCronException::parseCronFailed("Unable to match $sCronType ",$sCronString);
        }
        
        return $sCronRegex;
        
    }
    
    
    /**
     * Parse Pattern B
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse 
     */ 
    public function getPatternB($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '^([0-5][0-9]{1}|[0-9]{1})/([0-9]+)$';
            break;
            case 'hour':       
                $sCronRegex = '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})/([0-9]+)$';
            break;
            case 'dayofmonth': 
                $sCronRegex = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})/([0-9]+)$';
            break;
            case 'dayofweek': 
                $sCronRegex = '^([0-6]{1})/([0-9]+)$';
            break;
            case 'month':      
                $sCronRegex = '^([1-9]{1}|[1-2][1-2]{1})/([0-9]+)$';
            break;    
            default: throw ParseCronException::parseCronFailed("Unable to match $sCronType ",$sCronString);
        }
        
        return $sCronRegex;
    }
    
    
    /**
     * Parse Pattern C
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse 
     */ 
    public function getPatternC($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})$';
            break;
            case 'hour':       
                $sCronRegex = '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})-([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$';
            break;
            case 'dayofmonth': 
                $sCronRegex = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})-([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})$';
            break;
            case 'dayofweek': 
                $sCronRegex = '^([0-6]{1})-([0-6]{1})$';
            break;
            case 'month':      
                $sCronRegex = '^([1-9]{1}|[1-2][1-2]{1})-([1-9]{1}|[1-2][1-2]{1})';
            break;    
            default: throw ParseCronException::parseCronFailed("Unable to match $sCronType ",$sCronString);
        }
        
        return $sCronRegex;
    } 
    
    
    /**
     * Parse Pattern D
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse 
     */ 
    public function getPatternD($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '^([0-5][0-9]?|[0-9]{1})$';
            break;
            case 'hour':       
                $sCronRegex = '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$';
            break;
            case 'dayofmonth': 
                $sCronRegex = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})$';
            break;
            case 'dayofweek': 
                $sCronRegex = '^([0-6]{1})$';
            break;
            case 'month':      
                $sCronRegex = '^([1-9]{1}|[1-2][1-2]{1})$';
            break;    
            default: throw ParseCronException::parseCronFailed("Unable to match $sCronType ",$sCronString);
        }
        
        return $sCronRegex;
    }
    
    
    /**
     * Parse Pattern E
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse 
     */ 
    public function getPatternE($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '^([*]{1})/([0-9]+)$';
            break;
            case 'hour':       
                $sCronRegex = '^([*]{1})/([0-9]+)$';
            break;
            case 'dayofmonth': 
                $sCronRegex = '^([*]{1})/([0-9]+)$';
            break;
            case 'dayofweek': 
                $sCronRegex = '^([*]{1})/([0-9]+)$';
            break;
            case 'month':      
                $sCronRegex = '^([*]{1})/([0-9]+)$';
            break;    
            default: throw ParseCronException::parseCronFailed("Unable to match $sCronType ",$sCronString);
        }
        
        return $sCronRegex;
    }
    
    
    
    /**
     * Find Min Value
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse 
     */ 
    public function testMin($sCronType,$sCronString)
    {
        
        switch($sCronType) {
            case 'minute':
                $iMinValue = 0;
            break;
            case 'hour':       
                $iMinValue = 0;
            break;
            case 'dayofmonth': 
                $iMinValue = 1;
            break;
            case 'dayofweek': 
                $iMinValue = 0;
            break;
            case 'month':      
                $iMinValue = 1;
            break;    
            default: throw ParseCronException::parseCronFailed("Unable to match $sCronType ",$sCronString);
        }
        
        return $iMinValue;
        
    }
    
    /**
     * Find Max Value
     * 
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse 
     */ 
    public function testMax($sCronType,$sCronString)
    {
        
        switch($sCronType) {
            case 'minute':
                $iMaxValue = 59;
            break;
            case 'hour':       
                $iMaxValue = 23;
            break;
            case 'dayofmonth': 
                $iMaxValue = 31;
            break;
            case 'dayofweek': 
                $iMaxValue = 6;
            break;
            case 'month':      
                $iMaxValue = 12;
            break;    
            default: throw ParseCronException::parseCronFailed("Unable to match $sCronType ",$sCronString);
        }
        
        return $iMaxValue;
        
    }
    
    
}
/* End of File */
