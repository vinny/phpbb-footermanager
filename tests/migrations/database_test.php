<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\tests\migrations;

class database_test extends \phpbb_database_test_case
{
	protected $db_tools;
	protected $table_prefix;

	static protected function setup_extensions()
	{
		return array('vinny/footermanager');
	}

	public function getDataSet()
	{
		return new \PHPUnit\DbUnit\DataSet\DefaultDataSet();
	}

	public function setUp(): void
	{
		parent::setUp();

		global $table_prefix;
		$this->table_prefix = $table_prefix;

		$db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();
		$this->db_tools = $factory->get($db);
	}

	public function test_columns_table_exists()
	{
		$this->assertTrue(
			$this->db_tools->sql_table_exists($this->table_prefix . 'vinny_footermanager_columns'),
			'Asserting that vinny_footermanager_columns table exists'
		);
	}

	public function test_blocks_table_exists()
	{
		$this->assertTrue(
			$this->db_tools->sql_table_exists($this->table_prefix . 'vinny_footermanager_blocks'),
			'Asserting that vinny_footermanager_blocks table exists'
		);
	}

	public function test_columns_table_schema()
	{
		$table = $this->table_prefix . 'vinny_footermanager_columns';
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'column_id'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'column_title'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'column_width'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'column_order'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'column_enabled'));
	}

	public function test_blocks_table_schema()
	{
		$table = $this->table_prefix . 'vinny_footermanager_blocks';
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'block_id'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'column_id'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'block_title'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'block_content'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'block_parse_bbcode'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'bbcode_uid'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'bbcode_bitfield'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'bbcode_options'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'block_order'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'block_enabled'));
		$this->assertTrue($this->db_tools->sql_column_exists($table, 'block_hide_groups'));
	}
}
