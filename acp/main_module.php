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

class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	/**
	 * Main execution function
	 *
	 * @param int|string $id   The module ID
	 * @param string     $mode The module mode
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \vinny\footermanager\controller\acp_controller $acp_controller */
		$acp_controller = $phpbb_container->get('vinny.footermanager.controller.acp');

		$this->tpl_name = 'acp_footermanager_settings';

		$acp_controller->set_page_url($this->u_action);
		$acp_controller->handle($id, $mode);

		$this->page_title = $acp_controller->get_page_title();
	}
}
