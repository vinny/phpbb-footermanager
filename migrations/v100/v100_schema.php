<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\migrations\v100;

class v100_schema extends \phpbb\db\migration\migration
{
	/**
	 * Check if migration is effectively installed.
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'vinny_footermanager_columns') &&
			$this->db_tools->sql_table_exists($this->table_prefix . 'vinny_footermanager_blocks');
	}

	/**
	 * Define migration dependencies.
	 *
	 * @return array
	 */
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330',
		];
	}

	/**
	 * Updates the database schema
	 *
	 * @return array
	 */
	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'vinny_footermanager_columns' => [
					'COLUMNS' => [
						'column_id'				=> ['UINT', null, 'auto_increment'],
						'column_title'			=> ['VCHAR_UNI:255', ''],
						'column_order'			=> ['USINT', 0],
						'column_width'			=> ['VCHAR:50', 'auto'],
						'column_enabled'		=> ['BOOL', 1],
					],
					'PRIMARY_KEY' => 'column_id',
					'KEYS' => [
						'column_order'			=> ['INDEX', 'column_order'],
					],
				],
				$this->table_prefix . 'vinny_footermanager_blocks' => [
					'COLUMNS' => [
						'block_id'				=> ['UINT', null, 'auto_increment'],
						'column_id'				=> ['UINT', 0],
						'block_title'			=> ['VCHAR_UNI:255', ''],
						'block_content'			=> ['TEXT_UNI', ''],
						'block_parse_bbcode'	=> ['BOOL', 1],
						'bbcode_uid'			=> ['VCHAR:8', ''],
						'bbcode_bitfield'		=> ['VCHAR:255', ''],
						'bbcode_options'		=> ['UINT:11', 7],
						'block_order'			=> ['USINT', 0],
						'block_enabled'			=> ['BOOL', 1],
						'block_hide_groups'		=> ['VCHAR:255', ''],
					],
					'PRIMARY_KEY' => 'block_id',
					'KEYS' => [
						'column_id'				=> ['INDEX', 'column_id'],
						'block_order'			=> ['INDEX', 'block_order'],
					],
				],
			],
		];
	}

	/**
	 * Reverts the database schema
	 *
	 * @return array
	 */
	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'vinny_footermanager_blocks',
				$this->table_prefix . 'vinny_footermanager_columns',
			],
		];
	}
}
