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

class v100_data extends \phpbb\db\migration\migration
{
	/**
	 * Check if migration is effectively installed.
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		return isset($this->config['vinny_footermanager_enable']);
	}

	/**
	 * Define migration dependencies.
	 *
	 * @return array
	 */
	public static function depends_on()
	{
		return [
			'\vinny\footermanager\migrations\v100\v100_schema',
		];
	}

	/**
	 * Updates the database data
	 *
	 * @return array
	 */
	public function update_data()
	{
		return [
			['config.add', ['vinny_footermanager_enable', 1]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_VINNY_FOOTERMANAGER',
			]],
			['module.add', [
				'acp',
				'ACP_VINNY_FOOTERMANAGER',
				[
					'module_basename'	=> '\vinny\footermanager\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
