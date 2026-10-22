<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ACP_VINNY_FOOTERMANAGER'				=> 'Footer Manager',
	'ACP_VINNY_FOOTERMANAGER_SETTINGS'		=> 'Settings',
	'ACP_VINNY_FOOTERMANAGER_BLOCKS'		=> 'Manage columns and blocks',
	'ACP_VINNY_FOOTERMANAGER_EXPLAIN'		=> 'Configure your board footer with column layouts and custom content blocks.',

	'FOOTERMANAGER_SUPPORT_STAR'			=> 'If you like this extension, please give it a star on <a href="https://github.com/vinny/phpbb-footermanager" target="_blank" rel="noopener"><i class="icon fa fa-github fa-fw" aria-hidden="true"></i>GitHub</a>.',
	'FOOTERMANAGER_SUPPORT_DONATE'			=> 'You can also support its development with an optional <a href="https://ko-fi.com/vinny1" target="_blank" rel="noopener"><i class="icon fa fa-heart fa-fw" aria-hidden="true"></i>donation</a>.',

	// Logs
	'LOG_VINNY_FOOTERMANAGER_SETTINGS'		=> '<strong>Footer Manager settings updated</strong>',
	'LOG_VINNY_FOOTERMANAGER_BLOCK_ADDED'	=> '<strong>Footer block added</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_BLOCK_EDITED'	=> '<strong>Footer block updated</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_BLOCK_DELETED'	=> '<strong>Footer block deleted</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_COL_ADDED'		=> '<strong>Footer column added</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_COL_EDITED'	=> '<strong>Footer column updated</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_COL_DELETED'	=> '<strong>Footer column deleted</strong><br />» %s',
]);
