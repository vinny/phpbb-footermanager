<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\tests\functional;

/**
 * Functional ACP Test for Footer Manager Extension
 */
class acp_footermanager_functional_test extends \PHPUnit\Framework\TestCase
{
	/** @var \vinny\footermanager\acp\main_module */
	protected $module;

	protected function setUp(): void
	{
		$this->module = new \vinny\footermanager\acp\main_module();
	}

	public function test_module_instantiation()
	{
		$this->assertInstanceOf(\vinny\footermanager\acp\main_module::class, $this->module);
	}
}
