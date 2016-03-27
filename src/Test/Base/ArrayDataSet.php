<?php
namespace IComeFromTheNet\BookMe\Test\Base;

use PHPUnit_Extensions_Database_DataSet_AbstractDataSet;

/**
 * Dataset for php arrays
 * 
 * @package IComeFromTheNet\BookMe\Test\Base
 */
class ArrayDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
    /**
     * @var array
     */
    protected $tables = [];

    /**
     * Creates a new dataset
     *
     * @param mixed $files
     */
    public function __construct($files = null)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->addFile($file);
            }
        } else if ($files) {
            $this->addFile($files);
        }
    }

    /**
     * Adds a new file to the dataset.
     * @param string $file
     */
    public function addFile($file)
    {
        $data = require $file;

        foreach ($data as $tableName => $rows) {
            if (!isset($rows)) {
                $rows = array();
            }

            if (!is_array($rows)) {
                continue;
            }

            if (!array_key_exists($tableName, $this->tables)) {
                if(true === isset($rows[0])) {
                    $columns = array_keys($rows[0]);
    
                }
                else {
                    $columns = [];

                }                
                
                $tableMetaData = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
                    $tableName,
                    $columns
                );

                $this->tables[$tableName] = new \PHPUnit_Extensions_Database_DataSet_DefaultTable(
                    $tableMetaData
                );
            }

            foreach ($rows as $row) {
                $this->tables[$tableName]->addRow($row);
            }
        }
    }

    /**
     * Creates an iterator over the tables in the data set. If $reverse is
     * true a reverse iterator will be returned.
     *
     * @param bool $reverse
     * @return \PHPUnit_Extensions_Database_DataSet_ITableIterator
     */
    protected function createIterator($reverse = false)
    {
        return new \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator(
            $this->tables,
            $reverse
        );
    }
}
/* End of File */