<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;


/**
 * Used to add a number of years to the calender tables
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class CalAddYearHandler 
{
    
    /**
     * @var array   a map internal table names to external names
     */ 
    protected $aTableNames;
    
    /**
     * @var Doctrine\DBAL\Connection    the database adpater
     */ 
    protected $oDatabaseAdapter;
    
    /**
     * This will find the last calender year that exists in this table,
     * we would want to start adding new year after this one.
     * 
     * If the table has no last then default to now()
     */ 
    protected function getLastCalendarYear()
    {
        $oDatabase     = $this->oDatabaseAdapter;
        $sCalTableName = $this->aTableNames['bm_calendar'];
        $sSql          = '';
        $oDateType     = Type::getType(Type::DATE);
        
        
        $sSql   .= "SELECT DATE_FORMAT(IFNULL((SELECT MAX(calendar_date) from $sCalTableName),NOW()),'%Y-01-01')";
        
        return $oDateType->convertToPHPValue($oDatabase->fetchColumn($sSql,[],0,[]),$oDatabase->getDatabasePlatform());
    }
    
    /**
     * Build the calender table which contain all days in a given year
     * 
     */ 
    protected function buildCalendar($iYears, \DateTime $oLastCalYear)
    {
        $oDatabase     = $this->oDatabaseAdapter;
        $sCalTableName = $this->aTableNames['bm_calendar'];
        $aSql          = [];
        
        
        
        $aSql[] = " INSERT INTO $sCalTableName (calendar_date) ";
		$aSql[] = " SELECT CAST('".$oLastCalYear->format('Y-m-d')."' AS DATETIME) + INTERVAL a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i DAY ";
		$aSql[] = " FROM ints a JOIN ints b JOIN ints c JOIN ints d JOIN ints e ";
		$aSql[] = " WHERE (a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i) <= DATEDIFF(DATE_FORMAT(CAST('".$oLastCalYear->format('Y-m-d')."' AS DATETIME) + INTERVAL (? -1) YEAR,'%Y-12-31'),DATE_FORMAT(CAST('".$oLastCalYear->format('Y-m-d')."' AS DATETIME) ,'%Y-01-01')) ";
		$aSql[] = " ORDER BY 1 ";
	
	    $sSql = implode(PHP_EOL,$aSql);
	    $oDatabase->executeUpdate($sSql, [$iYears], [Type::getType(Type::INTEGER)]);
        
    
        
    	$aSql = [];
            
    	
    	$aSql[] =" UPDATE $sCalTableName ";
    	$aSql[] =" SET is_week_day = CASE WHEN dayofweek(calendar_date) IN (1,7) THEN 0 ELSE 1 END, ";
    	$aSql[] ="	y = YEAR(calendar_date), ";
    	$aSql[] ="	q = quarter(calendar_date), ";
    	$aSql[] ="	m = MONTH(calendar_date), ";
    	$aSql[] ="	d = dayofmonth(calendar_date), ";
    	$aSql[] ="	dw = dayofweek(calendar_date), ";
    	$aSql[] ="	month_name = monthname(calendar_date), ";
    	$aSql[] ="	day_name = dayname(calendar_date), ";
    	$aSql[] ="	w = week(calendar_date) ";
    	
        $sSql = implode(PHP_EOL,$aSql);
	    $oDatabase->executeUpdate($sSql, [], []);
        
    }
    
    
    protected function buildWeeks($iYears)
    {
        
        
        
        
        
    }
    
    
    protected function buildMonths($iYears)
    {
        
        
    }
    
    protected function buildQuarters($iYears)
    {
        
        
    }
    
    
    protected function buildYears($iYears)
    {
        
        
    }
    
    
    public function __construct(array $aTableNames, Connection $oDatabaseAdapter)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        
        
    }
    
    
    public function handle(CalAddYearCommand $command)
    {
        $iYears       = $command->getYears();
        $oLastCalYear = $this->getLastCalendarYear();
        
        $this->buildCalendar($iYears, $oLastCalYear); 
       
        
        return true;
    }
     
    
}
/* End of File */