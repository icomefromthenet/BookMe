<?php
namespace IComeFromTheNet\BookMe\Bus\Middleware;

use Doctrine\DBAL\Connection;
use League\Tactician\Middleware;
use Exception;

/**
 * Wraps command execution inside a Database transaction
 */
class UnitOfWorkMiddleware implements Middleware
{
    /**
     * @var Doctrine\DBAL\Connection
     */
    private $oDatabase;
    
    
    /**
     * @param Doctrine\DBAL\Connection $oDatabase
     */
    public function __construct(Connection $oDatabase)
    {
        $this->oDatabase = $oDatabase;
    }
    
    
    /**
     * Executes the given command and optionally returns a value
     *
     * @param object $command
     * @param callable $next
     * @return mixed
     * @throws Exception
     */
    public function execute($command, callable $next)
    {
        $this->oDatabase->beginTransaction();
        
        try {
            
            $returnValue = $next($command);
            
            $this->oDatabase->commit();
            
        } catch (Exception $e) {
            $this->oDatabase->rollback();
            throw $e;
        } 
        
        return $returnValue;
    }
   
}