<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\tests\language;

class language_test extends \phpbb_test_case
{
	protected $ext_dir;

	public function setUp(): void
	{
		parent::setUp();
		$this->ext_dir = dirname(dirname(__DIR__));
	}

	protected function load_language_file($lang_iso, $filename)
	{
		$path = $this->ext_dir . '/language/' . $lang_iso . '/' . $filename;
		$this->assertFileExists($path);

		$lang = [];
		include $path;
		return $lang;
	}

	public function test_english_files_loaded()
	{
		$en_fm = $this->load_language_file('en', 'footermanager.php');
		$this->assertNotEmpty($en_fm);

		$en_info = $this->load_language_file('en', 'info_acp_footermanager.php');
		$this->assertNotEmpty($en_info);
	}

	public function test_translations_coverage()
	{
		$en_fm = $this->load_language_file('en', 'footermanager.php');
		$pt_fm = $this->load_language_file('pt_br', 'footermanager.php');

		foreach (array_keys($en_fm) as $key)
		{
			$this->assertArrayHasKey(
				$key,
				$pt_fm,
				"Asserting that Portuguese translation contains key: {$key}"
			);
		}

		$en_info = $this->load_language_file('en', 'info_acp_footermanager.php');
		$pt_info = $this->load_language_file('pt_br', 'info_acp_footermanager.php');

		foreach (array_keys($en_info) as $key)
		{
			$this->assertArrayHasKey(
				$key,
				$pt_info,
				"Asserting that Portuguese ACP info contains key: {$key}"
			);
		}
	}

	public function test_all_declared_keys_are_used_in_codebase()
	{
		$en_fm = $this->load_language_file('en', 'footermanager.php');
		$en_info = $this->load_language_file('en', 'info_acp_footermanager.php');
		$declared_keys = array_merge(array_keys($en_fm), array_keys($en_info));

		// Scan codebase files (excluding language, vendor, and git)
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->ext_dir));
		$codebase_text = '';

		/** @var \SplFileInfo $file */
		foreach ($files as $file)
		{
			if ($file->isFile())
			{
				$path = $file->getPathname();
				if (strpos($path, 'language') === false &&
					strpos($path, 'vendor') === false &&
					strpos($path, 'tests') === false &&
					strpos($path, '.git') === false)
				{
					$codebase_text .= file_get_contents($path) . "\n";
				}
			}
		}

		$unused_keys = [];
		foreach ($declared_keys as $key)
		{
			if (strpos($codebase_text, $key) === false)
			{
				$unused_keys[] = $key;
			}
		}

		$this->assertEmpty(
			$unused_keys,
			'All declared language keys should be in use. Unused keys: ' . implode(', ', $unused_keys)
		);
	}
}
