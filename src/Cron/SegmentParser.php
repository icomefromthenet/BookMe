<?php
namespace IComeFromTheNet\BookMe\Cron;

use DateTime;
use Psr\Log\LoggerInterface;
use IComeFromTheNet\BookMe\Cron\ParseCronException;


/**
 * Parse each cron segment into ranges that can be used in a query.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class SegmentParser
{
    
    /**
     * @var Psr\Log\LoggerInterface
     */ 
    protected $oAppLogger;
    
    /**
     * Class Constructor
     * 
     * @param LoggerInterface   $oAppLogger The app logger class
     */ 
    public function __construct(LoggerInterface $oAppLogger)
    {
        $this->oAppLogger = $oAppLogger;
    }

    /**
     * Parse a cron segment into ParsedRanges
     * 
     * @return array(ParsedRange)
     * @param string    $sCronType  The type of the segment on of the ParsedRange::TYPE_* constants
     * @param string    $sCronExpr  The cron segment to parse
     */ 
    public function parseSegment($sCronType, $sCronExpr)
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
        $aRanges            = [];
        
	    //fetch the default min and max range for this cron segment
	    $iMinOpenValue      = $this->getSegmentMin($sCronType, $sCronExpr);
	    $iMaxCloseValue     = $this->getSegmentMax($sCronType,  $sCronExpr);
	
	    
	
	    $this->oAppLogger->debug('Processing cron segment {sCronType} with value {sValue}'
	                             ,['sCronType' => $sCronType, 'sValue' => $sCronExpr]);
	
        
        if ($sCronExpr == '*' ) {
            
            $this->oAppLogger->debug('CronSegment is eq *');
            
                    
            // insert the default range into the parsed ranges table
	       $aRanges[] = new ParsedRange(1,$iMinOpenValue,$iMaxCloseValue, 1, $sCronType);
            
            
        } 
        else {
            $this->oAppLogger->debug('CronSegment will be parsed');
        
            // iterate over the segment
            // a cron segement can have child segments that are seperated by ','
            // we need to pick them out
           
            // if remove the child seperators we will know the number of child segments
            $iRangeOccurances = strlen($sCronExpr) - strlen(str_replace(',', '', $sCronExpr))+1;
		    $i                = 1;
		    $sIncrementValue  = 0;

            $this->oAppLogger->debug("rangeOccurances eq to $iRangeOccurances");
            
            $aChildSegments = explode(',',$sCronExpr);
            
            while($i <= $iRangeOccurances) {
                  
                  $sSplitValue = $aChildSegments[$i-1];
                  $aMatches    = [];
                  
                  
                 switch(true) {
                    case preg_match($this->getPatternA($sCronType, $sCronExpr),$sSplitValue,$aMatches) :
                        //test for range with increment e.g 01-59/39
        
                        $sFormatSTR      = '##-##/##';
                      	$sOpenValue      = $aMatches[2];
					    $sCloseValue     = $aMatches[1];				
					    $sIncrementValue = $aMatches[3];
		              
                    break;
        			case preg_match($this->getPatternB($sCronType, $sCronExpr),$sSplitValue,$aMatches) :
		                //test for a scalar with increment e.g 6/3 (this short for 6-59/3)
				
	    				$formatSTR      = '##/##';
		    			$sOpenValue     = $aMatches[0];
			    		$closeValue     = $iMaxCloseValue;
				    	$incrementValue = $aMatches[1];
				    	
				    break;
			    	case preg_match($this->getPatternC($sCronType, $sCronExpr),$sSplitValue,$aMatches) :				
				    	//test a range with e.g 34-59
					
					    $sFormatSTR     =  '##-##';
					    $openValue      = $aMatches[2];
					    $closeValue     = $aMatches[1];				
					    $incrementValue = 1;
					
				    break;
				    case preg_match($this->getPatternD($sCronType, $sCronExpr),$sSplitValue,$aMatches) :
				        //test for a scalar value
    										
    					$sFormatSTR     =  '##';
    					$openValue      = $aMatches[2];
    					$closeValue     = $aMatches[1];	
    					$incrementValue = 1;
    					
    				break;
    				case preg_match($this->getPatternE($sCronType, $sCronExpr),$sSplitValue,$aMatches):
    				    //test for a * with increment e.g */5
    					
    					$sFormatSTR     =  '*/##';
    					$openValue      = $iMinOpenValue;
    					$closeValue     = $iMaxCloseValue;
    					$incrementValue = $aMatches[1];
                    break;
    				default :
    				    $sMessage = "unable to determine child segemnt type at $i for segment $sSplitValue";
    				    $this->oAppLogger->debug($sMessage);
                        throw ParseCronException::parseCronFailed($sMessage, $sCronExpr);
                }
                  
                
			
    			// validate opening occurse before closing. 
    			
    			if($closeValue < $openValue){
    			    $sMessage = "Close occurs before Opening in child segemnt type at $i for segment $sSplitValue";
    				$this->oAppLogger->debug($sMessage);
                    throw ParseCronException::parseCronFailed($sMessage, $sCronExpr);
    				
    			}


    			// insert the parsed range values into the tmp table
    			// range table using a closed:open so need to add +1 to last value in range
    			
    		    $aRanges[] = new ParsedRange($i, $iOpenValue, $iCloseValue, $iIncrementValue, $sCronType);
               
    		   // increment the loop
	           $i = $i + 1;
	           
            }
           
        }
        
        return $aRanges;
            
    }

    
    
    // -------------------------------------------------------------------------
    # Parse Helpers
    
    /**
     * Parse pattern A
     * 
     * @var string  $sCronType    The cron option to parse
     * @var string  $sCronString  The full cron string to parse
     */ 
    protected function getPatternA($sCronType,$sCronString)
    {
        
        switch($sCronType) {
            case 'minute':
                $sCronRegex = '/^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})/([0-9]+)$/';
            break;
            case 'hour':       
                $sCronRegex = '/^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})-([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})/([0-9]+)$';
            break;
            case 'dayofmonth': 
                $sCronRegex = '/^([0-6]{1})-([0-6]{1})/([0-9]+)$/';
            break;
            case 'dayofweek': 
                $sCronRegex = '/^([0-6]{1})-([0-6]{1})/([0-9]+)$/';
            break;
            case 'month':      
                $sCronRegex  = '/^([1-9]{1}|[1-2][1-2]{1})-([1-9]{1}|[1-2][1-2]{1})\\\([0-9]+)$/';
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
    protected function getPatternB($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '/^([0-5][0-9]{1}|[0-9]{1})/([0-9]+)$/';
            break;
            case 'hour':       
                $sCronRegex = '/^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})/([0-9]+)$/';
            break;
            case 'dayofmonth': 
                $sCronRegex = '/^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})/([0-9]+)$/';
            break;
            case 'dayofweek': 
                $sCronRegex = '/^([0-6]{1})/([0-9]+)$/';
            break;
            case 'month':      
                $sCronRegex = '/^([1-9]{1}|[1-2][1-2]{1})\\\([0-9]+)$/';
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
    protected function getPatternC($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '/^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})$/';
            break;
            case 'hour':       
                $sCronRegex = '/^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})-([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$/';
            break;
            case 'dayofmonth': 
                $sCronRegex = '/^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})-([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})$/';
            break;
            case 'dayofweek': 
                $sCronRegex = '/^([0-6]{1})-([0-6]{1})$/';
            break;
            case 'month':      
                $sCronRegex = '/^([1-9]{1}|[1-2][1-2]{1})-([1-9]{1}|[1-2][1-2]{1})/';
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
    protected function getPatternD($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '/^([0-5][0-9]?|[0-9]{1})$/';
            break;
            case 'hour':       
                $sCronRegex = '/^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$/';
            break;
            case 'dayofmonth': 
                $sCronRegex = '/^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})$/';
            break;
            case 'dayofweek': 
                $sCronRegex = '/^([0-6]{1})$/';
            break;
            case 'month':      
                $sCronRegex = '/^([1-9]{1}|[1-2][1-2]{1})$/';
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
    protected function getPatternE($sCronType,$sCronString) 
    {
         switch($sCronType) {
            case 'minute':
                $sCronRegex = '/^([*]{1})/([0-9]+)$/';
            break;
            case 'hour':       
                $sCronRegex = '/^([*]{1})/([0-9]+)$/';
            break;
            case 'dayofmonth': 
                $sCronRegex = '/^([*]{1})/([0-9]+)$/';
            break;
            case 'dayofweek': 
                $sCronRegex = '/^([*]{1})/([0-9]+)$/';
            break;
            case 'month':      
                $sCronRegex = '/^([*]{1})\\\([0-9]+)$/';
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
    protected function getSegmentMin($sCronType,$sCronString)
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
            default: throw ParseCronException::parseCronFailed("Unable to find min for $sCronType ",$sCronString);
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
    protected function getSegmentMax($sCronType,$sCronString)
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
            default: throw ParseCronException::parseCronFailed("Unable to find max for cron type $sCronType ",$sCronString);
        }
        
        return $iMaxValue;
        
    }
    
    
}
/* End of File */
