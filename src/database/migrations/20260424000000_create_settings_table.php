<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateSettingsTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // create the table
        $table = $this->table('settings', ['comment' => '系统设置表']);
        $table->addColumn('key', 'string', ['limit' => 100, 'comment' => '设置键名'])
              ->addColumn('value', 'text', ['comment' => '设置值'])
              ->addColumn('created_at', 'datetime', ['comment' => '创建时间'])
              ->addColumn('updated_at', 'datetime', ['comment' => '更新时间'])
              ->addIndex(['key'], ['unique' => true, 'name' => 'idx_key'])
              ->create();
    }
}
