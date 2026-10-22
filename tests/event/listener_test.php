<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\tests\event;

use vinny\footermanager\event\listener;

class listener_test extends \phpbb_test_case
{
	/** @var \phpbb\template\template|\PHPUnit\Framework\MockObject\MockObject */
	protected $template;

	/** @var \phpbb\user|\PHPUnit\Framework\MockObject\MockObject */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface|\PHPUnit\Framework\MockObject\MockObject */
	protected $cache;

	/** @var string */
	protected $table_prefix = 'phpbb_';

	public function setUp(): void
	{
		parent::setUp();

		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->disableOriginalConstructor()->getMock();
		$this->user = $this->getMockBuilder('\phpbb\user')
			->disableOriginalConstructor()->getMock();
		$this->config = new \phpbb\config\config([
			'vinny_footermanager_enable' => 0,
		]);
		$this->db = $this->getMockBuilder('\phpbb\db\driver\driver_interface')
			->disableOriginalConstructor()->getMock();
		$this->cache = $this->getMockBuilder('\phpbb\cache\driver\driver_interface')
			->disableOriginalConstructor()->getMock();
	}

	protected function get_listener()
	{
		return new listener(
			$this->template,
			$this->user,
			$this->config,
			$this->db,
			$this->table_prefix,
			$this->cache
		);
	}

	public function test_get_subscribed_events()
	{
		$events = listener::getSubscribedEvents();
		$this->assertIsArray($events);
		$this->assertArrayHasKey('core.user_setup', $events);
		$this->assertArrayHasKey('core.page_header', $events);
		$this->assertArrayHasKey('core.page_footer', $events);
		$this->assertEquals('load_language_on_setup', $events['core.user_setup']);
		$this->assertEquals('on_page_header', $events['core.page_header']);
		$this->assertEquals('on_page_footer', $events['core.page_footer']);
	}

	public function test_load_language_on_setup()
	{
		$listener = $this->get_listener();
		$event = new \ArrayObject([
			'lang_set_ext' => [],
		]);

		$listener->load_language_on_setup($event);

		$this->assertCount(1, $event['lang_set_ext']);
		$this->assertEquals('vinny/footermanager', $event['lang_set_ext'][0]['ext_name']);
		$this->assertEquals('footermanager', $event['lang_set_ext'][0]['lang_set']);
	}

	public function test_on_page_header_when_disabled()
	{
		$this->config['vinny_footermanager_enable'] = 0;
		$this->template->expects($this->never())
			->method('assign_vars');

		$listener = $this->get_listener();
		$event = new \ArrayObject();
		$listener->on_page_header($event);
	}

	public function test_on_page_header_when_enabled()
	{
		$this->config['vinny_footermanager_enable'] = 1;
		$this->template->expects($this->once())
			->method('assign_vars')
			->with([
				'S_VINNY_FOOTERMANAGER_ENABLED'	=> true,
				'S_VINNY_FOOTERMANAGER_ACTIVE'	=> true,
			]);

		$listener = $this->get_listener();
		$event = new \ArrayObject();
		$listener->on_page_header($event);
	}

	public function test_on_page_footer_when_disabled()
	{
		$this->config['vinny_footermanager_enable'] = 0;

		$this->cache->expects($this->never())
			->method('get');
		$this->db->expects($this->never())
			->method('sql_query');
		$this->template->expects($this->never())
			->method('assign_block_vars');

		$listener = $this->get_listener();
		$event = new \ArrayObject();
		$listener->on_page_footer($event);
	}

	public function test_on_page_footer_with_cached_data()
	{
		$this->config['vinny_footermanager_enable'] = 1;

		$cached_columns = [
			[
				'column_id'			=> 1,
				'column_title'		=> 'About Us',
				'column_width'		=> '1/4',
				'column_enabled'	=> 1,
				'column_order'		=> 1,
			],
		];
		$cached_blocks = [
			[
				'block_id'				=> 1,
				'column_id'				=> 1,
				'block_title'			=> 'Info',
				'block_content'			=> '<p>Welcome</p>',
				'block_parse_bbcode'	=> 0,
				'bbcode_uid'			=> '',
				'bbcode_bitfield'		=> '',
				'bbcode_options'		=> 0,
				'block_order'			=> 1,
				'block_enabled'			=> 1,
				'block_hide_groups'		=> '',
			],
		];

		$this->cache->expects($this->once())
			->method('get')
			->with('_vinny_footermanager_cache')
			->willReturn([
				'columns' => $cached_columns,
				'blocks'  => $cached_blocks,
			]);

		$this->template->expects($this->atLeastOnce())
			->method('assign_vars');

		$this->template->expects($this->atLeastOnce())
			->method('assign_block_vars');

		$listener = $this->get_listener();
		$event = new \ArrayObject();
		$listener->on_page_footer($event);
	}
}
