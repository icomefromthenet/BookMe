<?php
namespace Migration\Components\Migration\Entities;

use Doctrine\DBAL\Connection,
    Doctrine\DBAL\Schema\AbstractSchemaManager as Schema,
    Migration\Components\Migration\EntityInterface;

class test_data implements EntityInterface
{
    
    public function up(Connection $db, Schema $sc)
    {

        $db->executeUpdate('INSERT INTO ints values (0)');
        $db->executeUpdate('INSERT INTO ints values (1)');
        $db->executeUpdate('INSERT INTO ints values (2)');
        $db->executeUpdate('INSERT INTO ints values (3)');
        $db->executeUpdate('INSERT INTO ints values (4)');
        $db->executeUpdate('INSERT INTO ints values (5)');
        $db->executeUpdate('INSERT INTO ints values (6)');
        $db->executeUpdate('INSERT INTO ints values (7)');
        $db->executeUpdate('INSERT INTO ints values (8)');
        $db->executeUpdate('INSERT INTO ints values (9)');
        
        $db->executeUpdate("INSERT INTO bm_rule_type (`rule_type_id`,`rule_code`) values (null,'adhoc')");
        $db->executeUpdate("INSERT INTO bm_rule_type (`rule_type_id`,`rule_code`) values (null,'repeat')");


    }

    public function down(Connection $db, Schema $sc)
    {


    }


}
/* End of File */
