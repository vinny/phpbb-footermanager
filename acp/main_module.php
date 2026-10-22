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
	 */
	public function main($id, $mode)
	{
		global $language, $template, $request, $config, $db, $table_prefix, $phpbb_log, $user, $auth;
		global $phpbb_container, $phpbb_root_path, $phpEx;

		if (!$auth->acl_get('a_board'))
		{
			trigger_error('NO_AUTH_OPERATION');
		}

		$language->add_lang(['info_acp_footermanager', 'footermanager'], 'vinny/footermanager');

		$this->tpl_name = 'acp_footermanager_settings';
		$this->page_title = $language->lang('ACP_VINNY_FOOTERMANAGER_SETTINGS');

		add_form_key('vinny_footermanager');

		$columns_table = $table_prefix . 'vinny_footermanager_columns';
		$blocks_table  = $table_prefix . 'vinny_footermanager_blocks';

		$action = $request->variable('action', '');
		$block_id = $request->variable('block_id', 0);
		$column_id = $request->variable('column_id', 0);

		// Toggle block status
		if ($action === 'toggle_block' && $block_id)
		{
			if (!check_link_hash($request->variable('hash', ''), 'toggle_block'))
			{
				trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT block_enabled FROM ' . $blocks_table . ' WHERE block_id = ' . (int) $block_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row)
			{
				$new_status = $row['block_enabled'] ? 0 : 1;
				$db->sql_query('UPDATE ' . $blocks_table . ' SET block_enabled = ' . (int) $new_status . ' WHERE block_id = ' . (int) $block_id);
				$this->purge_footer_cache();

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'success'		=> true,
						'icon_class'	=> $new_status ? 'fa-check-circle' : 'fa-times-circle',
						'icon_color'	=> $new_status ? '#228822' : '#bcbcbc',
						'title'			=> $new_status ? $language->lang('ENABLED') : $language->lang('DISABLED'),
					]);
				}
			}
			redirect($this->u_action);
		}

		// Toggle column status
		if ($action === 'toggle_col' && $column_id)
		{
			if (!check_link_hash($request->variable('hash', ''), 'toggle_col'))
			{
				trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT column_enabled FROM ' . $columns_table . ' WHERE column_id = ' . (int) $column_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row)
			{
				$new_status = $row['column_enabled'] ? 0 : 1;
				$db->sql_query('UPDATE ' . $columns_table . ' SET column_enabled = ' . (int) $new_status . ' WHERE column_id = ' . (int) $column_id);
				$this->purge_footer_cache();

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'success'		=> true,
						'icon_class'	=> $new_status ? 'fa-check-circle' : 'fa-times-circle',
						'icon_color'	=> $new_status ? '#228822' : '#bcbcbc',
						'title'			=> $new_status ? $language->lang('ENABLED') : $language->lang('DISABLED'),
					]);
				}
			}
			redirect($this->u_action);
		}

		// Update blocks order
		if ($action === 'update_order' && $request->is_ajax())
		{
			if (!check_link_hash($request->variable('hash', ''), 'update_order'))
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(['success' => false, 'error' => $language->lang('FORM_INVALID')]);
			}

			$order_json = htmlspecialchars_decode($request->variable('order', '', true));
			$order_data = json_decode($order_json, true);

			if (is_array($order_data))
			{
				$db->sql_transaction('begin');

				foreach ($order_data as $col_id => $block_ids)
				{
					$col_id = (int) $col_id;
					if (!is_array($block_ids))
					{
						continue;
					}

					$order = 10;
					foreach ($block_ids as $b_id)
					{
						$b_id = (int) $b_id;
						if ($b_id > 0)
						{
							$sql = 'UPDATE ' . $blocks_table . '
								SET ' . $db->sql_build_array('UPDATE', [
									'column_id'   => (int) $col_id,
									'block_order' => (int) $order,
								]) . '
								WHERE block_id = ' . (int) $b_id;
							$db->sql_query($sql);
							$order += 10;
						}
					}
				}

				$db->sql_transaction('commit');
				$this->purge_footer_cache();

				$json_response = new \phpbb\json_response;
				$json_response->send(['success' => true]);
			}

			$json_response = new \phpbb\json_response;
			$json_response->send(['success' => false]);
		}

		// Update columns order
		if ($action === 'update_columns_order' && $request->is_ajax())
		{
			if (!check_link_hash($request->variable('hash', ''), 'update_order'))
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(['success' => false, 'error' => $language->lang('FORM_INVALID')]);
			}

			$columns_order_raw = htmlspecialchars_decode($request->variable('columns_order', '', true));
			$columns_order = json_decode($columns_order_raw, true);

			if (is_array($columns_order))
			{
				$db->sql_transaction('begin');

				$order = 10;
				foreach ($columns_order as $c_id)
				{
					$c_id = (int) $c_id;
					if ($c_id > 0)
					{
						$sql = 'UPDATE ' . $columns_table . '
							SET ' . $db->sql_build_array('UPDATE', [
								'column_order' => (int) $order,
							]) . '
							WHERE column_id = ' . (int) $c_id;
						$db->sql_query($sql);
						$order += 10;
					}
				}

				$db->sql_transaction('commit');
				$this->purge_footer_cache();

				$json_response = new \phpbb\json_response;
				$json_response->send(['success' => true]);
			}

			$json_response = new \phpbb\json_response;
			$json_response->send(['success' => false]);
		}

		// Add or edit column
		if ($action === 'add_col' || ($action === 'edit_col' && $column_id))
		{
			$this->page_title = $action === 'add_col' ? $language->lang('FOOTER_ADD_COLUMN') : $language->lang('FOOTER_EDIT_COLUMN');

			$col_data = [
				'column_title'			=> '',
				'column_width'			=> 'auto',
				'column_enabled'		=> 1,
			];

			if ($action === 'edit_col')
			{
				$sql = 'SELECT * FROM ' . $columns_table . ' WHERE column_id = ' . (int) $column_id;
				$result = $db->sql_query($sql);
				$col_data = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$col_data)
				{
					trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			if ($request->is_set_post('submit'))
			{
				if (!check_form_key('vinny_footermanager'))
				{
					trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$allowed_widths = ['auto', '1/4', '1/3', '1/2', '2/3', '3/4'];
				$req_width = $request->variable('column_width', 'auto');
				$col_width = in_array($req_width, $allowed_widths, true) ? $req_width : 'auto';

				$column_title = $request->variable('column_title', '', true);
				if (function_exists('utf8_encode_ucr'))
				{
					$column_title = utf8_encode_ucr($column_title);
				}

				$save_data = [
					'column_title'			=> $column_title,
					'column_width'			=> $col_width,
					'column_enabled'		=> $request->variable('column_enabled', 1),
				];

				if ($action === 'add_col')
				{
					$sql = 'SELECT COUNT(column_id) as total FROM ' . $columns_table;
					$result = $db->sql_query($sql);
					$count = (int) $db->sql_fetchfield('total');
					$db->sql_freeresult($result);

					if ($count >= 8)
					{
						trigger_error($language->lang('FOOTER_MAX_COLUMNS_REACHED') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql = 'SELECT MAX(column_order) as max_ord FROM ' . $columns_table;
					$result = $db->sql_query($sql);
					$max_ord = (int) $db->sql_fetchfield('max_ord');
					$db->sql_freeresult($result);

					$save_data['column_order'] = $max_ord + 10;

					$sql = 'INSERT INTO ' . $columns_table . ' ' . $db->sql_build_array('INSERT', $save_data);
					$db->sql_query($sql);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_VINNY_FOOTERMANAGER_COL_ADDED', false, [$save_data['column_title']]);
				}
				else
				{
					$sql = 'UPDATE ' . $columns_table . ' SET ' . $db->sql_build_array('UPDATE', $save_data) . ' WHERE column_id = ' . (int) $column_id;
					$db->sql_query($sql);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_VINNY_FOOTERMANAGER_COL_EDITED', false, [$save_data['column_title']]);
				}

				$this->purge_footer_cache();
				trigger_error($language->lang('FOOTER_COLUMN_SAVED') . adm_back_link($this->u_action));
			}

			$template->assign_vars([
				'S_EDIT_COLUMN'			=> true,
				'COLUMN_FORM_TITLE'		=> $action === 'add_col' ? $language->lang('FOOTER_ADD_COLUMN') : $language->lang('FOOTER_EDIT_COLUMN'),
				'COLUMN_TITLE'			=> $col_data['column_title'],
				'COLUMN_WIDTH'			=> $col_data['column_width'],
				'COLUMN_ENABLED'		=> (bool) $col_data['column_enabled'],
				'U_ACTION'				=> $this->u_action . '&amp;action=' . $action . ($column_id ? '&amp;column_id=' . $column_id : ''),
				'U_BACK'				=> $this->u_action,
			]);
			return;
		}

		// Delete column
		if ($action === 'delete_col' && $column_id)
		{
			if (confirm_box(true))
			{
				$sql = 'SELECT column_title FROM ' . $columns_table . ' WHERE column_id = ' . (int) $column_id;
				$result = $db->sql_query($sql);
				$col_title = (string) $db->sql_fetchfield('column_title');
				$db->sql_freeresult($result);

				$db->sql_query('DELETE FROM ' . $blocks_table . ' WHERE column_id = ' . (int) $column_id);
				$db->sql_query('DELETE FROM ' . $columns_table . ' WHERE column_id = ' . (int) $column_id);

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_VINNY_FOOTERMANAGER_COL_DELETED', false, [$col_title]);
				$this->purge_footer_cache();

				trigger_error($language->lang('FOOTER_COLUMN_DELETED') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $language->lang('FOOTER_CONFIRM_DELETE_COLUMN'), build_hidden_fields([
					'i'			=> $id,
					'mode'		=> 'settings',
					'action'	=> 'delete_col',
					'column_id'	=> $column_id,
				]));
			}
			redirect($this->u_action);
		}

		// Add or edit block
		if ($action === 'add_block' || ($action === 'edit_block' && $block_id))
		{
			$this->page_title = $action === 'add_block' ? $language->lang('FOOTER_ADD_BLOCK') : $language->lang('FOOTER_EDIT_BLOCK');

			$language->add_lang('posting');

			if (!function_exists('display_custom_bbcodes'))
			{
				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
			}
			if (!class_exists('parse_message'))
			{
				include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
			}

			$block_data = [
				'column_id'				=> $request->variable('target_col', 0),
				'block_title'			=> '',
				'block_content'			=> '',
				'block_parse_bbcode'	=> 1,
				'bbcode_uid'			=> '',
				'bbcode_bitfield'		=> '',
				'bbcode_options'		=> 7,
				'block_enabled'			=> 1,
				'block_hide_groups'		=> '',
			];
			$block_content_text = '';

			if ($action === 'edit_block')
			{
				$sql = 'SELECT * FROM ' . $blocks_table . ' WHERE block_id = ' . (int) $block_id;
				$result = $db->sql_query($sql);
				$block_data = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$block_data)
				{
					trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$parse_bbcode = isset($block_data['block_parse_bbcode']) ? (int) $block_data['block_parse_bbcode'] : 1;
				if ($parse_bbcode)
				{
					$edit_data = generate_text_for_edit($block_data['block_content'], $block_data['bbcode_uid'], $block_data['bbcode_options']);
					$block_content_text = $edit_data['text'];
				}
				else
				{
					$block_content_text = $block_data['block_content'];
				}
			}

			$submit = $request->is_set_post('submit');
			$preview = $request->is_set_post('preview');
			$analyse = $request->is_set_post('analyse');
			$analysis_results = [];
			$block_preview = '';

			if ($submit || $preview || $analyse)
			{
				if (!check_form_key('vinny_footermanager'))
				{
					trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$block_title = $request->variable('block_title', '', true);
				if (function_exists('utf8_encode_ucr'))
				{
					$block_title = utf8_encode_ucr($block_title);
				}

				$target_col = $request->variable('column_id', 0);
				if ($target_col <= 0 && !empty($block_data['column_id']))
				{
					$target_col = (int) $block_data['column_id'];
				}
				$enabled = $request->variable('block_enabled', 1);
				$parse_bbcode = $request->variable('block_parse_bbcode', 0);
				$raw_content = $request->variable('block_content', '', true);
				if (function_exists('utf8_encode_ucr'))
				{
					$raw_content = utf8_encode_ucr($raw_content);
				}

				$hide_groups_arr = $request->variable('block_hide_groups', [0]);
				$hide_groups_arr = array_filter(
					$hide_groups_arr,
					function ($g)
					{
						return (int) $g > 0;
					}
				);
				$hide_groups_str = !empty($hide_groups_arr) ? implode(',', $hide_groups_arr) : '';

				if ($block_title === '')
				{
					trigger_error($language->lang('BLOCK_NAME_EMPTY') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (utf8_strlen($block_title) > 255)
				{
					trigger_error($language->lang('BLOCK_NAME_TOO_LONG') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if ($raw_content === '')
				{
					trigger_error($language->lang('BLOCK_CONTENT_EMPTY') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$uid = $bitfield = '';
				$flags = 0;
				$storage_content = $raw_content;

				if ($parse_bbcode)
				{
					$flags = OPTION_FLAG_BBCODE | OPTION_FLAG_SMILIES | OPTION_FLAG_LINKS;
					generate_text_for_storage($storage_content, $uid, $bitfield, $flags, true, true, true);
				}

				$block_data = [
					'column_id'				=> (int) $target_col,
					'block_title'			=> $block_title,
					'block_content'			=> $storage_content,
					'block_parse_bbcode'	=> (int) $parse_bbcode,
					'bbcode_uid'			=> $uid,
					'bbcode_bitfield'		=> $bitfield,
					'bbcode_options'		=> (int) $flags,
					'block_enabled'			=> (int) $enabled,
					'block_hide_groups'		=> $hide_groups_str,
				];
				$block_content_text = $raw_content;

				if ($preview)
				{
					if ($block_data['block_parse_bbcode'])
					{
						$block_preview = generate_text_for_display($storage_content, $uid, $bitfield, $flags);
					}
					else
					{
						$block_preview = htmlspecialchars_decode($storage_content, ENT_COMPAT);
					}
				}

				if ($analyse)
				{
					$analysis_results = $this->analyse_block_content($storage_content, $config, $language);
				}

				if ($submit && $action === 'add_block')
				{
					$sql = 'SELECT MAX(block_order) as max_ord FROM ' . $blocks_table . ' WHERE column_id = ' . (int) $target_col;
					$result = $db->sql_query($sql);
					$max_ord = (int) $db->sql_fetchfield('max_ord');
					$db->sql_freeresult($result);

					$block_data['block_order'] = $max_ord + 10;

					$sql = 'INSERT INTO ' . $blocks_table . ' ' . $db->sql_build_array('INSERT', $block_data);
					$db->sql_query($sql);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_VINNY_FOOTERMANAGER_BLOCK_ADDED', false, [$block_title]);
					$this->purge_footer_cache();
					trigger_error($language->lang('FOOTER_BLOCK_SAVED') . adm_back_link($this->u_action));
				}
				else if ($submit)
				{
					$sql = 'UPDATE ' . $blocks_table . ' SET ' . $db->sql_build_array('UPDATE', $block_data) . ' WHERE block_id = ' . (int) $block_id;
					$db->sql_query($sql);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_VINNY_FOOTERMANAGER_BLOCK_EDITED', false, [$block_title]);
					$this->purge_footer_cache();
					trigger_error($language->lang('FOOTER_BLOCK_SAVED') . adm_back_link($this->u_action));
				}
			}

			// Fetch columns for select and preview
			$sql = 'SELECT column_id, column_title, column_width, column_order, column_enabled FROM ' . $columns_table . ' ORDER BY column_order ASC';
			$result = $db->sql_query($sql);
			$all_columns_list = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);

			$column_options = '';
			$col_index = 1;
			foreach ($all_columns_list as $row)
			{
				$selected = ($row['column_id'] == $block_data['column_id']) ? ' selected="selected"' : '';
				$title_display = !empty($row['column_title']) ? $row['column_title'] : $language->lang('FOOTER_COLUMNS') . ' #' . $col_index;
				$column_options .= '<option value="' . (int) $row['column_id'] . '"' . $selected . '>' . utf8_htmlspecialchars(htmlspecialchars_decode($title_display, ENT_COMPAT)) . '</option>';
				$col_index++;
			}

			$target_column_id = (int) $block_data['column_id'];
			$target_column_enabled = false;
			$preview_columns_list = [];

			foreach ($all_columns_list as $c)
			{
				if (!empty($c['column_enabled']))
				{
					$preview_columns_list[] = $c;
					if ((int) $c['column_id'] === $target_column_id)
					{
						$target_column_enabled = true;
					}
				}
			}

			if ($block_preview !== '' && $target_column_enabled)
			{
				$width_spans = [
					'1/4'	=> 3,
					'1/3'	=> 4,
					'1/2'	=> 6,
					'2/3'	=> 8,
					'3/4'	=> 9,
				];

				$has_custom_width = false;
				$total_explicit = 0;
				$auto_count = 0;

				foreach ($preview_columns_list as $c)
				{
					$w = $c['column_width'] ?? 'auto';
					if (isset($width_spans[$w]))
					{
						$has_custom_width = true;
						$total_explicit += $width_spans[$w];
					}
					else
					{
						$auto_count++;
					}
				}

				$auto_span = 12;
				if ($has_custom_width && $auto_count > 0)
				{
					$remaining = max(0, 12 - $total_explicit);
					$auto_span = max(1, (int) floor($remaining / $auto_count));
				}

				$col_idx = 1;

				foreach ($preview_columns_list as $c)
				{
					$c_id = (int) $c['column_id'];
					$c_width = $c['column_width'] ?? 'auto';
					$c_title = !empty($c['column_title']) ? $c['column_title'] : $language->lang('FOOTER_COLUMNS') . ' #' . $col_idx;
					$span = $has_custom_width ? ($width_spans[$c_width] ?? $auto_span) : 0;

					$template->assign_block_vars('preview_columns', [
						'ID'		=> $c_id,
						'TITLE'		=> $c_title,
						'WIDTH'		=> $c_width,
						'SPAN'		=> $span,
						'IS_TARGET'	=> ($c_id === $target_column_id),
					]);

					$col_idx++;
				}

				$template->assign_vars([
					'S_PREVIEW_CUSTOM_WIDTHS'		=> $has_custom_width,
					'PREVIEW_TOTAL_COLUMNS'			=> count($preview_columns_list),
				]);
			}

			$controller_helper = $phpbb_container->get('controller.helper');

			$template->assign_vars([
				'S_EDIT_BLOCK'				=> true,
				'BLOCK_FORM_TITLE'			=> $action === 'add_block' ? $language->lang('FOOTER_ADD_BLOCK') : $language->lang('FOOTER_EDIT_BLOCK'),
				'BLOCK_TITLE'				=> $block_data['block_title'],
				'COLUMN_OPTIONS'			=> $column_options,
				'BLOCK_ENABLED'				=> (bool) $block_data['block_enabled'],
				'S_PARSE_BBCODE'			=> (bool) $block_data['block_parse_bbcode'],
				'BLOCK_CONTENT'				=> $block_content_text,
				'S_BLOCK_PREVIEW'			=> ($block_preview !== '' && $target_column_enabled),
				'BLOCK_PREVIEW'				=> $block_preview,
				'S_BLOCK_ANALYSIS'			=> !empty($analysis_results),

				'BBCODE_STATUS'				=> $language->lang('BBCODE_IS_ON', '<a href="' . $controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
				'SMILIES_STATUS'			=> $language->lang('SMILIES_ARE_ON'),
				'IMG_STATUS'				=> $language->lang('IMAGES_ARE_ON'),
				'FLASH_STATUS'				=> $language->lang('FLASH_IS_ON'),
				'URL_STATUS'				=> $language->lang('URL_IS_ON'),
				'S_BBCODE_ALLOWED'			=> true,
				'S_SMILIES_ALLOWED'			=> true,
				'S_BBCODE_IMG'				=> true,
				'S_BBCODE_FLASH'			=> true,
				'S_LINKS_ALLOWED'			=> true,

				'U_ACTION'					=> $this->u_action . '&amp;action=' . $action . ($block_id ? '&amp;block_id=' . $block_id : ''),
				'U_BACK'					=> $this->u_action,
			]);

			display_custom_bbcodes();

			$all_groups = $this->get_all_groups($block_data['block_hide_groups'] ?? '');
			foreach ($all_groups as $group)
			{
				$template->assign_block_vars('groups', [
					'ID'			=> $group['group_id'],
					'NAME'			=> $group['group_name'],
					'S_SELECTED'	=> $group['selected'],
				]);
			}

			foreach ($analysis_results as $res)
			{
				$template->assign_block_vars('analysis_results', [
					'CLASS'		=> $res['class'],
					'MESSAGE'	=> $res['message'],
				]);
			}

			return;
		}

		// Delete block
		if ($action === 'delete_block' && $block_id)
		{
			if (confirm_box(true))
			{
				$sql = 'SELECT block_title FROM ' . $blocks_table . ' WHERE block_id = ' . (int) $block_id;
				$result = $db->sql_query($sql);
				$block_title = (string) $db->sql_fetchfield('block_title');
				$db->sql_freeresult($result);

				$db->sql_query('DELETE FROM ' . $blocks_table . ' WHERE block_id = ' . (int) $block_id);
				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_VINNY_FOOTERMANAGER_BLOCK_DELETED', false, [$block_title]);
				$this->purge_footer_cache();

				trigger_error($language->lang('FOOTER_BLOCK_DELETED') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $language->lang('FOOTER_CONFIRM_DELETE_BLOCK'), build_hidden_fields([
					'i'			=> $id,
					'mode'		=> 'settings',
					'action'	=> 'delete_block',
					'block_id'	=> $block_id,
				]));
			}
			redirect($this->u_action);
		}

		// Save Settings
		if ($request->is_set_post('submit_settings'))
		{
			if (!check_form_key('vinny_footermanager'))
			{
				trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$config->set('vinny_footermanager_enable', $request->variable('vinny_footermanager_enable', 1));

			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_VINNY_FOOTERMANAGER_SETTINGS');
			$this->purge_footer_cache();

			trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Columns and blocks overview
		$sql = 'SELECT * FROM ' . $columns_table . ' ORDER BY column_order ASC';
		$result = $db->sql_query($sql);
		$columns = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$sql = 'SELECT * FROM ' . $blocks_table . ' ORDER BY block_order ASC';
		$result = $db->sql_query($sql);
		$blocks = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$blocks_by_column = [];
		foreach ($blocks as $b)
		{
			$blocks_by_column[$b['column_id']][] = $b;
		}

		$col_counter = 1;
		foreach ($columns as $col)
		{
			$c_id = (int) $col['column_id'];
			$c_title = !empty($col['column_title']) ? $col['column_title'] : $language->lang('FOOTER_COLUMNS') . ' #' . $col_counter;

			$col_blocks = $blocks_by_column[$c_id] ?? [];

			$template->assign_block_vars('columns', [
				'ID'				=> $c_id,
				'TITLE'				=> $c_title,
				'WIDTH'				=> $col['column_width'],
				'ENABLED'			=> (bool) $col['column_enabled'],
				'TOTAL_BLOCKS'		=> count($col_blocks),
				'U_EDIT_COL'		=> $this->u_action . '&amp;action=edit_col&amp;column_id=' . $c_id,
				'U_DELETE_COL'		=> $this->u_action . '&amp;action=delete_col&amp;column_id=' . $c_id,
				'U_ADD_BLOCK'		=> $this->u_action . '&amp;action=add_block&amp;target_col=' . $c_id,
				'U_TOGGLE_COL'		=> $this->u_action . '&amp;action=toggle_col&amp;column_id=' . $c_id . '&amp;hash=' . generate_link_hash('toggle_col'),
			]);

			foreach ($col_blocks as $b)
			{
				$b_id = (int) $b['block_id'];
				$b_title = !empty($b['block_title']) ? $b['block_title'] : $language->lang('FOOTER_BLOCKS') . ' #' . $b_id;

				$template->assign_block_vars('columns.blocks', [
					'ID'			=> $b_id,
					'TITLE'			=> $b_title,
					'ENABLED'		=> (bool) $b['block_enabled'],
					'U_EDIT'		=> $this->u_action . '&amp;action=edit_block&amp;block_id=' . $b_id,
					'U_DELETE'		=> $this->u_action . '&amp;action=delete_block&amp;block_id=' . $b_id,
					'U_TOGGLE'		=> $this->u_action . '&amp;action=toggle_block&amp;block_id=' . $b_id . '&amp;hash=' . generate_link_hash('toggle_block'),
				]);
			}

			$col_counter++;
		}

		$template->assign_vars([
			'U_ACTION'						=> $this->u_action,
			'VINNY_FOOTERMANAGER_ENABLE'	=> (bool) $config['vinny_footermanager_enable'],
			'U_ADD_COLUMN'					=> $this->u_action . '&amp;action=add_col',
			'U_UPDATE_ORDER'				=> $this->u_action,
			'HASH_UPDATE_ORDER'				=> generate_link_hash('update_order'),
			'TOTAL_COLUMNS'					=> count($columns),
			'S_CAN_ADD_COLUMN'				=> count($columns) < 8,
		]);
	}

	/**
	 * Get list of all board groups formatted for ACP checkboxes.
	 *
	 * @param string|array $selected
	 * @return array List of group arrays with group_id, group_name, selected
	 */
	protected function get_all_groups($selected = '')
	{
		global $db, $user;

		if (is_string($selected))
		{
			$selected = !empty($selected) ? array_map('intval', explode(',', $selected)) : [];
		}
		else if (is_array($selected))
		{
			$selected = array_map('intval', $selected);
		}
		else
		{
			$selected = [];
		}

		global $phpbb_container;
		$group_helper = $phpbb_container->has('group_helper') ? $phpbb_container->get('group_helper') : null;
		$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . "
			WHERE group_name <> 'BOTS'
			ORDER BY group_name ASC";
		$result = $db->sql_query($sql);
		$groups = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$g_id = (int) $row['group_id'];
			$g_name = ($group_helper !== null) ? $group_helper->get_name($row['group_name']) : ((defined('GROUP_SPECIAL') && (int) $row['group_type'] === GROUP_SPECIAL && isset($user->lang['G_' . $row['group_name']])) ? $user->lang['G_' . $row['group_name']] : $row['group_name']);
			$groups[] = [
				'group_id'   => $g_id,
				'group_name' => $g_name,
				'selected'   => in_array($g_id, $selected, true),
			];
		}
		$db->sql_freeresult($result);

		return $groups;
	}

	/**
	 * Analyses raw HTML for common security and performance concerns.
	 *
	 * @param string $content
	 * @param \phpbb\config\config $config
	 * @param \phpbb\language\language $language
	 * @return array
	 */
	protected function analyse_block_content($content, $config, $language)
	{
		$content = htmlspecialchars_decode($content, ENT_COMPAT);
		$results = [];
		$has_untrusted_connection = false;

		if (preg_match('/alert\s*\(/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_ALERT_USAGE'),
			];
		}

		if (preg_match('/location\s*\.\s*href\s*=/i', $content))
		{
			$results[] = [
				'class'		=> 'errorbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_LOCATION_CHANGE'),
			];
		}

		if (preg_match('/<script\b(?=[^>]*\bsrc\s*=)(?![^>]*\basync\b)[^>]*>/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_SCRIPT_WITHOUT_ASYNC'),
			];
		}

		if (!empty($config['server_protocol']) && strpos($config['server_protocol'], 'https') === 0 && preg_match('/(?:src|href)\s*=\s*["\']http:\/\//i', $content))
		{
			$has_untrusted_connection = true;
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_UNTRUSTED_CONNECTION'),
			];
		}

		if (!$has_untrusted_connection && preg_match('/(?:src|href|action)\s*=\s*["\'](?:https?:)?\/\//i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_EXTERNAL_RESOURCE'),
			];
		}

		if (preg_match('/<iframe\b/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_IFRAME'),
			];
		}

		if (preg_match('/<form\b/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_FORM'),
			];
		}

		if (preg_match('/\s(on[a-z]+)\s*=/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_INLINE_EVENT'),
			];
		}

		if (preg_match('/(?:href|src)\s*=\s*["\']\s*javascript:/i', $content))
		{
			$results[] = [
				'class'		=> 'errorbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_JAVASCRIPT_URI'),
			];
		}

		if (empty($results))
		{
			$results[] = [
				'class'		=> 'successbox',
				'message'	=> $language->lang('BLOCK_ANALYSIS_NO_ISSUES'),
			];
		}

		return $results;
	}

	/**
	 * Purges footer blocks and columns cache
	 */
	protected function purge_footer_cache()
	{
		global $phpbb_container;
		if ($phpbb_container->has('cache.driver'))
		{
			$cache = $phpbb_container->get('cache.driver');
			$cache->destroy('_vinny_footermanager_cache');
			$cache->destroy('_vinny_footermanager_reg_groups');
		}
	}
}
