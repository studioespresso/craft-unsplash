<?php

namespace studioespresso\splashingimages\migrations;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use studioespresso\splashingimages\records\UserRecord;

/**
 * m181108_123909_addUsersTable migration.
 */
class m181108_123909_addUsersTable extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /*
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /*
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */


    protected function createTables()
    {
        $tablesCreated = false;

        // splashing_images_users table
        $tableSchema = Craft::$app->db->schema->getTableSchema(UserRecord::tableName());
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                UserRecord::tableName(),
                [
                    'id' => $this->primaryKey(),
                    'user' => $this->integer(),
                    'token' => $this->tinyText(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),

                ]
            );
        }

        return $tablesCreated;
    }


    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {

        // $name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%splashing_images_users}}', 'user'),
            '{{%splashing_images_users}}',
            'user',
            '{{%users}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists(UserRecord::tableName());
    }
}
