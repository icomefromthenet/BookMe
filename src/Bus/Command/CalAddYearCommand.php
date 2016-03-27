<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;

/**
 * This command is used to add a new year to the calendar table
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CalAddYearCommand implements ValidationInterface
{

  /**
   * @var integer This is the number of calendar years to add 
   */
  protected $iYear;

    
    
  public function __construct($iYear)
  {
        $this->iYear = (integer) $iYear;    
  }
  
  
  /**
   * Return the number of calender years to add
   * 
   * @return integer 
   */ 
  public function getYears()
  {
    return $this->iYear;
  }
  
  
  //---------------------------------------------------------
  # validation interface
  
  
  public function getRules()
  {
      return [
        'integer' => [
            ['year']
        ]
        ,'min' => [
           ['year',1]
        ]
        ,'max' => [
           ['year',10]
        ]
      ];
  }
  
  
  public function getData()
  {
      return [
        'year' => $this->iYear
      ];
  }
  
}
/* End of Clas */