<?php
namespace IComeFromTheNet\BookMe\Test\Base;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;

class DoctrineLogger implements SQLLogger
{
    
    protected $oLogger;
    
    
    public function __construct(LoggerInterface $oLogger)
    {
        $this->oLogger = $oLogger;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->oLogger->debug($sql . PHP_EOL);

        if ($params) {
            $this->oLogger->debug(var_export($params,true));
        }

        if ($types) {
            //$this->oLogger->debug(var_export($types,true));
        }
        
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
/* End of File */