<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\tests\controller;

use vinny\footermanager\controller\acp_controller;

class acp_controller_test extends \phpbb_test_case
{
	/** @var \phpbb\auth\auth|\PHPUnit\Framework\MockObject\MockObject */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper|\PHPUnit\Framework\MockObject\MockObject */
	protected $controller_helper;

	/** @var \phpbb\db\driver\driver_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	/** @var \phpbb\group\helper|\PHPUnit\Framework\MockObject\MockObject */
	protected $group_helper;

	/** @var \phpbb\language\language|\PHPUnit\Framework\MockObject\MockObject */
	protected $language;

	/** @var \phpbb\log\log_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $log;

	/** @var \phpbb\request\request_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $request;

	/** @var \phpbb\template\template|\PHPUnit\Framework\MockObject\MockObject */
	protected $template;

	/** @var \phpbb\user|\PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	/** @var string */
	protected $root_path = './';

	/** @var string */
	protected $php_ext = 'php';

	/** @var string */
	protected $table_prefix = 'phpbb_';

	public function setUp(): void
	{
		parent::setUp();

		$this->auth = $this->getMockBuilder('\phpbb\auth\auth')
			->disableOriginalConstructor()->getMock();
		$this->cache = $this->getMockBuilder('\phpbb\cache\driver\driver_interface')
			->disableOriginalConstructor()->getMock();
		$this->config = new \phpbb\config\config([
			'vinny_footermanager_enable' => 1,
		]);
		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()->getMock();
		$this->db = $this->getMockBuilder('\phpbb\db\driver\driver_interface')
			->disableOriginalConstructor()->getMock();
		$this->group_helper = $this->getMockBuilder('\phpbb\group\helper')
			->disableOriginalConstructor()->getMock();
		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()->getMock();
		$this->log = $this->getMockBuilder('\phpbb\log\log_interface')
			->disableOriginalConstructor()->getMock();
		$this->request = $this->getMockBuilder('\phpbb\request\request_interface')
			->disableOriginalConstructor()->getMock();
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()->getMock();
		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()->getMock();
	}

	protected function get_controller()
	{
		return new acp_controller(
			$this->auth,
			$this->cache,
			$this->config,
			$this->controller_helper,
			$this->db,
			$this->group_helper,
			$this->language,
			$this->log,
			$this->request,
			$this->template,
			$this->user,
			$this->root_path,
			$this->php_ext,
			$this->table_prefix
		);
	}

	public function test_controller_instantiation()
	{
		$controller = $this->get_controller();
		$this->assertInstanceOf(acp_controller::class, $controller);
	}

	public function test_page_url_and_title_accessors()
	{
		$controller = $this->get_controller();
		$controller->set_page_url('adm/index.php?i=-vinny-footermanager-acp-main_module&mode=settings');

		$this->assertSame('', $controller->get_page_title());
	}
}
