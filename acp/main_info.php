<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\vinny\footermanager\acp\main_module',
			'title'		=> 'ACP_VINNY_FOOTERMANAGER',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_VINNY_FOOTERMANAGER_SETTINGS',
					'auth'	=> 'ext_vinny/footermanager && acl_a_board',
					'cat'	=> ['ACP_VINNY_FOOTERMANAGER'],
				],
			],
		];
	}
}
