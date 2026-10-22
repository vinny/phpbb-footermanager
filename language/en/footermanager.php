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
	// General / Titles
	'FOOTER_BLOCKS_EXPLAIN'					=> 'Organise columns and blocks to build your footer. Drag and drop blocks between columns to rearrange them.',

	// Settings Mode
	'FOOTER_ENABLE'							=> 'Enable footer manager',
	'FOOTER_ENABLE_EXPLAIN'					=> 'Enable or disable the custom footer across the board.',

	// Columns Management
	'FOOTER_COLUMNS'						=> 'Footer columns',
	'FOOTER_ADD_COLUMN'						=> 'Add column',
	'FOOTER_EDIT_COLUMN'					=> 'Edit column',
	'FOOTER_DELETE_COLUMN'					=> 'Delete column',
	'FOOTER_CONFIRM_DELETE_COLUMN'			=> 'Are you sure you want to delete this column? All blocks inside it will also be deleted.',
	'FOOTER_COLUMN_TITLE'					=> 'Column title',
	'FOOTER_COLUMN_TITLE_EXPLAIN'			=> 'Title displayed above the column.',
	'FOOTER_COLUMN_WIDTH'					=> 'Column width',
	'FOOTER_COLUMN_WIDTH_EXPLAIN'			=> 'Set the column width, or select auto for an equal split.',
	'FOOTER_WIDTH_AUTO'						=> 'Auto (equal split)',
	'FOOTER_WIDTH_1_4'						=> '1/4 (25%)',
	'FOOTER_WIDTH_1_3'						=> '1/3 (33%)',
	'FOOTER_WIDTH_1_2'						=> '1/2 (50%)',
	'FOOTER_WIDTH_2_3'						=> '2/3 (66%)',
	'FOOTER_WIDTH_3_4'						=> '3/4 (75%)',
	'FOOTER_COLUMN_ENABLED'					=> 'Enabled',
	'FOOTER_COLUMN_EMPTY'					=> 'This column has no blocks yet. Drag blocks here or select “Add block”.',
	'FOOTER_COLUMN_SAVED'					=> 'Column saved.',
	'FOOTER_COLUMN_DELETED'					=> 'Column deleted.',
	'FOOTER_MAX_COLUMNS_REACHED'			=> 'You can create a maximum of 8 columns.',

	// Blocks Management
	'FOOTER_BLOCKS'							=> 'Blocks',
	'FOOTER_ADD_BLOCK'						=> 'Add block',
	'FOOTER_EDIT_BLOCK'						=> 'Edit block',
	'FOOTER_DELETE_BLOCK'					=> 'Delete block',
	'FOOTER_CONFIRM_DELETE_BLOCK'			=> 'Are you sure you want to delete this block?',
	'FOOTER_BLOCK_NAME'						=> 'Block title',
	'FOOTER_BLOCK_COLUMN'					=> 'Column',
	'FOOTER_BLOCK_ENABLED'					=> 'Enabled',
	'FOOTER_HIDE_FOR_GROUPS'				=> 'Hide for user groups',
	'FOOTER_HIDE_FOR_GROUPS_EXPLAIN'		=> 'Select groups that should not see this block.',
	'BLOCK_CONTENT'							=> 'Content',
	'BLOCK_CONTENT_EXPLAIN'					=> 'Enter the content for this block. Standard BBCode, smilies, and links are supported when BBCode parsing is enabled. If disabled, raw HTML is rendered directly, so only enter code from trusted sources.',
	'PARSE_BBCODE'							=> 'Parse BBCode',
	'FOOTER_BLOCK_SAVED'					=> 'Block saved.',
	'FOOTER_BLOCK_DELETED'					=> 'Block deleted.',
	'FOOTER_BLOCK_EMPTY'					=> 'No blocks found. Add a block to get started.',
	'FOOTER_LIVE_PREVIEW'					=> 'Footer preview',
	'FOOTER_PREVIEW_EXPLAIN'				=> 'Preview how this block appears within its column layout.',
	'FOOTER_DRAG_DROP_HINT'					=> 'Drag blocks to reorder them within or between columns.',
	'FOOTER_DRAG_DROP_COL_HINT'				=> 'Drag columns to reorder them.',

	// Block Validation
	'BLOCK_NAME_EMPTY'						=> 'You must specify a block title.',
	'BLOCK_NAME_TOO_LONG'					=> 'The block title cannot be longer than 255 characters.',
	'BLOCK_CONTENT_EMPTY'					=> 'You must enter block content.',

	// HTML Security and Analysis
	'BLOCK_TRUSTED_HTML_WARNING'			=> 'HTML entered here is rendered directly in the footer. Only enter code from trusted sources.',
	'BLOCK_ANALYSE_HTML'					=> 'Analyse HTML',
	'BLOCK_ANALYSIS'						=> 'HTML analysis',
	'BLOCK_PREVIEW'							=> 'Preview',
	'BLOCK_ANALYSIS_NO_ISSUES'				=> 'No common HTML issues were detected.',
	'BLOCK_ANALYSIS_ALERT_USAGE'			=> 'The HTML contains an alert() call. This is typically used for debugging and should be removed.',
	'BLOCK_ANALYSIS_LOCATION_CHANGE'		=> 'The HTML modifies location.href, which can redirect visitors. Ensure this code is trusted.',
	'BLOCK_ANALYSIS_SCRIPT_WITHOUT_ASYNC'	=> 'The HTML loads an external script without an async attribute. Consider adding async to prevent blocking page loads.',
	'BLOCK_ANALYSIS_UNTRUSTED_CONNECTION'	=> 'The board uses HTTPS, but this HTML loads resources over insecure HTTP. Use HTTPS URLs to prevent mixed-content warnings.',
	'BLOCK_ANALYSIS_EXTERNAL_RESOURCE'		=> 'The HTML loads external resources. Ensure the external source is trusted.',
	'BLOCK_ANALYSIS_IFRAME'					=> 'The HTML contains an iframe. Ensure the embedded content comes from a trusted source.',
	'BLOCK_ANALYSIS_FORM'					=> 'The HTML contains a form. Ensure it submits data only to a trusted destination.',
	'BLOCK_ANALYSIS_INLINE_EVENT'			=> 'The HTML contains inline JavaScript events (such as onclick or onload). Ensure this code is trusted.',
	'BLOCK_ANALYSIS_JAVASCRIPT_URI'			=> 'The HTML contains a javascript: link. Ensure this code is trusted before saving.',
]);
