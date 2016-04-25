<?php
namespace IComeFromTheNet\BookMe\Cron;

use Valitron\Validator;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationException;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;

/**
 * Results of a cron segment parser pass.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */
class ParsedRange implements ValidationInterface
{
    
    const TYPE_MINUTE           = 'minute';
    const TYPE_HOUR             = 'hour';
    const TYPE_DAYOFMONTH       = 'dayofmonth';
    const TYPE_DAYOFWEEK        = 'dayofweek';
    const TYPE_MONTH            = 'month';
    
    
    protected $iSegmentOrder;
    protected $iRangeOpen;
    protected $iRangeClose;
    protected $iModVaue;
    protected $sRangeType;
    
    
    public function __construct($iSegmentOrder,$iRangeOpen,$iRangeClose,$iModVaue,$sRangeType)
    {
        $this->iSegmentOrder = $iSegmentOrder;
        $this->iRangeOpen    = $iRangeOpen; 
        $this->iRangeClose   = $iRangeClose;
        $this->iModVaue      = $iModVaue;
        $this->sRangeType    = $sRangeType;
        
    }
    
    
    
    public function validate()
    {
        $aRules     = $this->getRules();
        $aData      = $this->getData();
        
        $oValidator = new Validator($aData);
            
        
        $oValidator->rules($aRules);
        
        $bValid = $oValidator->validate();
        
        if(false === $bValid) {
            throw ValidationException::hasFailedValidation($this,$oValidator->errors());
        }
        
        return $bValid;
    }
    
    
    public function getData()
    {
        return array(
           'segment_order'  => $this->iSegmentOrder,
           'range_open'     => $this->iRangeOpen,
           'range_close'    => $this->iRangeClose,
           'mod_value'      => $this->iModVaue,
           'range_type'     => $this->sRangeType,
        );
    }
    
    
    public function getRules()
    {
        
        return [
            'integer' => [
                ['segment_order']
            ]
            ,'min' => [
               ['segment_order',1]
            ]
            ,'in' => [
                ['range_type',[self::TYPE_DAYOFMONTH,self::TYPE_DAYOFWEEK,self::TYPE_HOUR,self::TYPE_MINUTE,self::TYPE_MONTH]]
            ]
            ,'numeric' =>[
               ['range_open'],['range_close']
            ]
        ];
        
        
    }
    
    
    public function getSegmentOrder()
    {
        return $this->iSegmentOrder;
    }
    
    
    
    public function getRangeOpen()
    {
        return $this->iRangeOpen;
    }
    
    
    
    public function getRangeClose()
    {
        return $this->iRangeClose;
    }
    
    
    
    public function getModValue()
    {
        return $this->iModVaue;
    }
    
    
    public function getRangeType()
    {
        return $this->sRangeType;
    }
    
    
    
}
/* End of Class */