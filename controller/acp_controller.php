<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\controller;

class acp_controller
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\group\helper */
	protected $group_helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $columns_table;

	/** @var string */
	protected $blocks_table;

	/** @var string */
	protected $u_action = '';

	/** @var string */
	protected $page_title = '';

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                     $auth              Auth object
	 * @param \phpbb\cache\driver\driver_interface $cache             Cache driver
	 * @param \phpbb\config\config                 $config            Config object
	 * @param \phpbb\controller\helper             $controller_helper Controller helper
	 * @param \phpbb\db\driver\driver_interface    $db                Database connection
	 * @param \phpbb\group\helper                  $group_helper      Group helper
	 * @param \phpbb\language\language             $language          Language object
	 * @param \phpbb\log\log_interface             $log               Log object
	 * @param \phpbb\request\request_interface     $request           Request object
	 * @param \phpbb\template\template             $template          Template object
	 * @param \phpbb\user                          $user              User object
	 * @param string                               $root_path         phpBB root path
	 * @param string                               $php_ext           PHP file extension
	 * @param string                               $table_prefix      Database table prefix
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\controller\helper $controller_helper,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\group\helper $group_helper,
		\phpbb\language\language $language,
		\phpbb\log\log_interface $log,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$table_prefix
	)
	{
		$this->auth              = $auth;
		$this->cache             = $cache;
		$this->config            = $config;
		$this->controller_helper = $controller_helper;
		$this->db                = $db;
		$this->group_helper      = $group_helper;
		$this->language          = $language;
		$this->log               = $log;
		$this->request           = $request;
		$this->template          = $template;
		$this->user              = $user;
		$this->root_path         = $root_path;
		$this->php_ext           = $php_ext;
		$this->columns_table     = $table_prefix . 'vinny_footermanager_columns';
		$this->blocks_table      = $table_prefix . 'vinny_footermanager_blocks';
	}

	/**
	 * Set custom form action URL
	 *
	 * @param string $u_action Custom form action
	 * @return void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	 * Get the dynamic ACP page title
	 *
	 * @return string
	 */
	public function get_page_title()
	{
		return $this->page_title;
	}

	/**
	 * Handle ACP module requests
	 *
	 * @param int|string $id   Module ID
	 * @param string     $mode Module mode
	 * @return void
	 */
	public function handle($id, $mode)
	{
		if (!$this->auth->acl_get('a_board'))
		{
			trigger_error('NO_AUTH_OPERATION');
		}

		$this->language->add_lang(['info_acp_footermanager', 'footermanager'], 'vinny/footermanager');

		$this->page_title = $this->language->lang('ACP_VINNY_FOOTERMANAGER_SETTINGS');

		add_form_key('vinny_footermanager');

		$action = $this->request->variable('action', '');
		$block_id = $this->request->variable('block_id', 0);
		$column_id = $this->request->variable('column_id', 0);

		// Toggle block status
		if ($action === 'toggle_block' && $block_id)
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'toggle_block'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT block_enabled FROM ' . $this->blocks_table . ' WHERE block_id = ' . (int) $block_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$new_status = $row['block_enabled'] ? 0 : 1;
				$this->db->sql_query('UPDATE ' . $this->blocks_table . ' SET block_enabled = ' . (int) $new_status . ' WHERE block_id = ' . (int) $block_id);
				$this->purge_footer_cache();

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'success'		=> true,
						'icon_class'	=> $new_status ? 'fa-check-circle' : 'fa-times-circle',
						'icon_color'	=> $new_status ? '#228822' : '#bcbcbc',
						'title'			=> $new_status ? $this->language->lang('ENABLED') : $this->language->lang('DISABLED'),
					]);
				}
			}
			redirect($this->u_action);
		}

		// Toggle column status
		if ($action === 'toggle_col' && $column_id)
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'toggle_col'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT column_enabled FROM ' . $this->columns_table . ' WHERE column_id = ' . (int) $column_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$new_status = $row['column_enabled'] ? 0 : 1;
				$this->db->sql_query('UPDATE ' . $this->columns_table . ' SET column_enabled = ' . (int) $new_status . ' WHERE column_id = ' . (int) $column_id);
				$this->purge_footer_cache();

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'success'		=> true,
						'icon_class'	=> $new_status ? 'fa-check-circle' : 'fa-times-circle',
						'icon_color'	=> $new_status ? '#228822' : '#bcbcbc',
						'title'			=> $new_status ? $this->language->lang('ENABLED') : $this->language->lang('DISABLED'),
					]);
				}
			}
			redirect($this->u_action);
		}

		// Update blocks order
		if ($action === 'update_order' && $this->request->is_ajax())
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'update_order'))
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(['success' => false, 'error' => $this->language->lang('FORM_INVALID')]);
			}

			$order_json = htmlspecialchars_decode($this->request->variable('order', '', true));
			$order_data = json_decode($order_json, true);

			if (is_array($order_data))
			{
				$this->db->sql_transaction('begin');

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
							$sql = 'UPDATE ' . $this->blocks_table . '
								SET ' . $this->db->sql_build_array('UPDATE', [
									'column_id'   => (int) $col_id,
									'block_order' => (int) $order,
								]) . '
								WHERE block_id = ' . (int) $b_id;
							$this->db->sql_query($sql);
							$order += 10;
						}
					}
				}

				$this->db->sql_transaction('commit');
				$this->purge_footer_cache();

				$json_response = new \phpbb\json_response;
				$json_response->send(['success' => true]);
			}

			$json_response = new \phpbb\json_response;
			$json_response->send(['success' => false]);
		}

		// Update columns order
		if ($action === 'update_columns_order' && $this->request->is_ajax())
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'update_order'))
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(['success' => false, 'error' => $this->language->lang('FORM_INVALID')]);
			}

			$columns_order_raw = htmlspecialchars_decode($this->request->variable('columns_order', '', true));
			$columns_order = json_decode($columns_order_raw, true);

			if (is_array($columns_order))
			{
				$this->db->sql_transaction('begin');

				$order = 10;
				foreach ($columns_order as $c_id)
				{
					$c_id = (int) $c_id;
					if ($c_id > 0)
					{
						$sql = 'UPDATE ' . $this->columns_table . '
							SET ' . $this->db->sql_build_array('UPDATE', [
								'column_order' => (int) $order,
							]) . '
							WHERE column_id = ' . (int) $c_id;
						$this->db->sql_query($sql);
						$order += 10;
					}
				}

				$this->db->sql_transaction('commit');
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
			$this->page_title = $action === 'add_col' ? $this->language->lang('FOOTER_ADD_COLUMN') : $this->language->lang('FOOTER_EDIT_COLUMN');

			$col_data = [
				'column_title'			=> '',
				'column_width'			=> 'auto',
				'column_enabled'		=> 1,
			];

			if ($action === 'edit_col')
			{
				$sql = 'SELECT * FROM ' . $this->columns_table . ' WHERE column_id = ' . (int) $column_id;
				$result = $this->db->sql_query($sql);
				$col_data = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$col_data)
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			if ($this->request->is_set_post('submit'))
			{
				if (!check_form_key('vinny_footermanager'))
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$allowed_widths = ['auto', '1/4', '1/3', '1/2', '2/3', '3/4'];
				$req_width = $this->request->variable('column_width', 'auto');
				$col_width = in_array($req_width, $allowed_widths, true) ? $req_width : 'auto';

				$column_title = $this->request->variable('column_title', '', true);
				if (function_exists('utf8_encode_ucr'))
				{
					$column_title = utf8_encode_ucr($column_title);
				}

				$save_data = [
					'column_title'			=> $column_title,
					'column_width'			=> $col_width,
					'column_enabled'		=> $this->request->variable('column_enabled', 1),
				];

				if ($action === 'add_col')
				{
					$sql = 'SELECT COUNT(column_id) as total FROM ' . $this->columns_table;
					$result = $this->db->sql_query($sql);
					$count = (int) $this->db->sql_fetchfield('total');
					$this->db->sql_freeresult($result);

					if ($count >= 8)
					{
						trigger_error($this->language->lang('FOOTER_MAX_COLUMNS_REACHED') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql = 'SELECT MAX(column_order) as max_ord FROM ' . $this->columns_table;
					$result = $this->db->sql_query($sql);
					$max_ord = (int) $this->db->sql_fetchfield('max_ord');
					$this->db->sql_freeresult($result);

					$save_data['column_order'] = $max_ord + 10;

					$sql = 'INSERT INTO ' . $this->columns_table . ' ' . $this->db->sql_build_array('INSERT', $save_data);
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_VINNY_FOOTERMANAGER_COL_ADDED', false, [$save_data['column_title']]);
				}
				else
				{
					$sql = 'UPDATE ' . $this->columns_table . ' SET ' . $this->db->sql_build_array('UPDATE', $save_data) . ' WHERE column_id = ' . (int) $column_id;
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_VINNY_FOOTERMANAGER_COL_EDITED', false, [$save_data['column_title']]);
				}

				$this->purge_footer_cache();
				trigger_error($this->language->lang('FOOTER_COLUMN_SAVED') . adm_back_link($this->u_action));
			}

			$this->template->assign_vars([
				'S_EDIT_COLUMN'			=> true,
				'COLUMN_FORM_TITLE'		=> $action === 'add_col' ? $this->language->lang('FOOTER_ADD_COLUMN') : $this->language->lang('FOOTER_EDIT_COLUMN'),
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
				$sql = 'SELECT column_title FROM ' . $this->columns_table . ' WHERE column_id = ' . (int) $column_id;
				$result = $this->db->sql_query($sql);
				$col_title = (string) $this->db->sql_fetchfield('column_title');
				$this->db->sql_freeresult($result);

				$this->db->sql_query('DELETE FROM ' . $this->blocks_table . ' WHERE column_id = ' . (int) $column_id);
				$this->db->sql_query('DELETE FROM ' . $this->columns_table . ' WHERE column_id = ' . (int) $column_id);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_VINNY_FOOTERMANAGER_COL_DELETED', false, [$col_title]);
				$this->purge_footer_cache();

				trigger_error($this->language->lang('FOOTER_COLUMN_DELETED') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $this->language->lang('FOOTER_CONFIRM_DELETE_COLUMN'), build_hidden_fields([
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> 'delete_col',
					'column_id'	=> $column_id,
				]));
			}
			redirect($this->u_action);
		}

		// Add or edit block
		if ($action === 'add_block' || ($action === 'edit_block' && $block_id))
		{
			$this->page_title = $action === 'add_block' ? $this->language->lang('FOOTER_ADD_BLOCK') : $this->language->lang('FOOTER_EDIT_BLOCK');

			$this->language->add_lang('posting');

			if (!function_exists('display_custom_bbcodes'))
			{
				include($this->root_path . 'includes/functions_display.' . $this->php_ext);
			}
			if (!class_exists('parse_message'))
			{
				include($this->root_path . 'includes/message_parser.' . $this->php_ext);
			}

			$block_data = [
				'column_id'				=> $this->request->variable('target_col', 0),
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
				$sql = 'SELECT * FROM ' . $this->blocks_table . ' WHERE block_id = ' . (int) $block_id;
				$result = $this->db->sql_query($sql);
				$block_data = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$block_data)
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
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

			$submit = $this->request->is_set_post('submit');
			$preview = $this->request->is_set_post('preview');
			$analyse = $this->request->is_set_post('analyse');
			$analysis_results = [];
			$block_preview = '';

			if ($submit || $preview || $analyse)
			{
				if (!check_form_key('vinny_footermanager'))
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$block_title = $this->request->variable('block_title', '', true);
				if (function_exists('utf8_encode_ucr'))
				{
					$block_title = utf8_encode_ucr($block_title);
				}

				$target_col = $this->request->variable('column_id', 0);
				if ($target_col <= 0 && !empty($block_data['column_id']))
				{
					$target_col = (int) $block_data['column_id'];
				}
				$enabled = $this->request->variable('block_enabled', 1);
				$parse_bbcode = $this->request->variable('block_parse_bbcode', 0);
				$raw_content = $this->request->variable('block_content', '', true);
				if (function_exists('utf8_encode_ucr'))
				{
					$raw_content = utf8_encode_ucr($raw_content);
				}

				$hide_groups_arr = $this->request->variable('block_hide_groups', [0]);
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
					trigger_error($this->language->lang('BLOCK_NAME_EMPTY') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (utf8_strlen($block_title) > 255)
				{
					trigger_error($this->language->lang('BLOCK_NAME_TOO_LONG') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if ($raw_content === '')
				{
					trigger_error($this->language->lang('BLOCK_CONTENT_EMPTY') . adm_back_link($this->u_action), E_USER_WARNING);
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
					$analysis_results = $this->analyse_block_content($storage_content);
				}

				if ($submit && $action === 'add_block')
				{
					$sql = 'SELECT MAX(block_order) as max_ord FROM ' . $this->blocks_table . ' WHERE column_id = ' . (int) $target_col;
					$result = $this->db->sql_query($sql);
					$max_ord = (int) $this->db->sql_fetchfield('max_ord');
					$this->db->sql_freeresult($result);

					$block_data['block_order'] = $max_ord + 10;

					$sql = 'INSERT INTO ' . $this->blocks_table . ' ' . $this->db->sql_build_array('INSERT', $block_data);
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_VINNY_FOOTERMANAGER_BLOCK_ADDED', false, [$block_title]);
					$this->purge_footer_cache();
					trigger_error($this->language->lang('FOOTER_BLOCK_SAVED') . adm_back_link($this->u_action));
				}
				else if ($submit)
				{
					$sql = 'UPDATE ' . $this->blocks_table . ' SET ' . $this->db->sql_build_array('UPDATE', $block_data) . ' WHERE block_id = ' . (int) $block_id;
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_VINNY_FOOTERMANAGER_BLOCK_EDITED', false, [$block_title]);
					$this->purge_footer_cache();
					trigger_error($this->language->lang('FOOTER_BLOCK_SAVED') . adm_back_link($this->u_action));
				}
			}

			// Fetch columns for select and preview
			$sql = 'SELECT column_id, column_title, column_width, column_order, column_enabled FROM ' . $this->columns_table . ' ORDER BY column_order ASC';
			$result = $this->db->sql_query($sql);
			$all_columns_list = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			$col_index = 1;
			foreach ($all_columns_list as $row)
			{
				$title_display = !empty($row['column_title']) ? html_entity_decode($row['column_title'], ENT_COMPAT, 'UTF-8') : $this->language->lang('FOOTER_COLUMNS') . ' #' . $col_index;
				$this->template->assign_block_vars('column_options', [
					'ID'       => (int) $row['column_id'],
					'TITLE'    => $title_display,
					'SELECTED' => (int) $row['column_id'] === (int) $block_data['column_id'],
				]);
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
					$c_title = !empty($c['column_title']) ? $c['column_title'] : $this->language->lang('FOOTER_COLUMNS') . ' #' . $col_idx;
					$span = $has_custom_width ? ($width_spans[$c_width] ?? $auto_span) : 0;

					$this->template->assign_block_vars('preview_columns', [
						'ID'		=> $c_id,
						'TITLE'		=> $c_title,
						'WIDTH'		=> $c_width,
						'SPAN'		=> $span,
						'IS_TARGET'	=> ($c_id === $target_column_id),
					]);

					$col_idx++;
				}

				$this->template->assign_vars([
					'S_PREVIEW_CUSTOM_WIDTHS'		=> $has_custom_width,
					'PREVIEW_TOTAL_COLUMNS'			=> count($preview_columns_list),
				]);
			}

			$this->template->assign_vars([
				'S_EDIT_BLOCK'				=> true,
				'BLOCK_FORM_TITLE'			=> $action === 'add_block' ? $this->language->lang('FOOTER_ADD_BLOCK') : $this->language->lang('FOOTER_EDIT_BLOCK'),
				'BLOCK_TITLE'				=> $block_data['block_title'],
				'BLOCK_ENABLED'				=> (bool) $block_data['block_enabled'],
				'S_PARSE_BBCODE'			=> (bool) $block_data['block_parse_bbcode'],
				'BLOCK_CONTENT'				=> $block_content_text,
				'S_BLOCK_PREVIEW'			=> ($block_preview !== '' && $target_column_enabled),
				'BLOCK_PREVIEW'				=> $block_preview,
				'S_BLOCK_ANALYSIS'			=> !empty($analysis_results),

				'BBCODE_STATUS'				=> $this->language->lang('BBCODE_IS_ON', '<a href="' . $this->controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
				'SMILIES_STATUS'			=> $this->language->lang('SMILIES_ARE_ON'),
				'IMG_STATUS'				=> $this->language->lang('IMAGES_ARE_ON'),
				'FLASH_STATUS'				=> $this->language->lang('FLASH_IS_ON'),
				'URL_STATUS'				=> $this->language->lang('URL_IS_ON'),
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
				$this->template->assign_block_vars('groups', [
					'ID'			=> $group['group_id'],
					'NAME'			=> $group['group_name'],
					'S_SELECTED'	=> $group['selected'],
				]);
			}

			foreach ($analysis_results as $res)
			{
				$this->template->assign_block_vars('analysis_results', [
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
				$sql = 'SELECT block_title FROM ' . $this->blocks_table . ' WHERE block_id = ' . (int) $block_id;
				$result = $this->db->sql_query($sql);
				$block_title = (string) $this->db->sql_fetchfield('block_title');
				$this->db->sql_freeresult($result);

				$this->db->sql_query('DELETE FROM ' . $this->blocks_table . ' WHERE block_id = ' . (int) $block_id);
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_VINNY_FOOTERMANAGER_BLOCK_DELETED', false, [$block_title]);
				$this->purge_footer_cache();

				trigger_error($this->language->lang('FOOTER_BLOCK_DELETED') . adm_back_link($this->u_action));
			}
			else
			{
				confirm_box(false, $this->language->lang('FOOTER_CONFIRM_DELETE_BLOCK'), build_hidden_fields([
					'i'			=> $id,
					'mode'		=> $mode,
					'action'	=> 'delete_block',
					'block_id'	=> $block_id,
				]));
			}
			redirect($this->u_action);
		}

		// Save Settings
		if ($this->request->is_set_post('submit_settings'))
		{
			if (!check_form_key('vinny_footermanager'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->config->set('vinny_footermanager_enable', $this->request->variable('vinny_footermanager_enable', 1));

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_VINNY_FOOTERMANAGER_SETTINGS');
			$this->purge_footer_cache();

			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Columns and blocks overview
		$sql = 'SELECT * FROM ' . $this->columns_table . ' ORDER BY column_order ASC';
		$result = $this->db->sql_query($sql);
		$columns = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT * FROM ' . $this->blocks_table . ' ORDER BY block_order ASC';
		$result = $this->db->sql_query($sql);
		$blocks = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$blocks_by_column = [];
		foreach ($blocks as $b)
		{
			$blocks_by_column[$b['column_id']][] = $b;
		}

		$col_counter = 1;
		foreach ($columns as $col)
		{
			$c_id = (int) $col['column_id'];
			$c_title = !empty($col['column_title']) ? $col['column_title'] : $this->language->lang('FOOTER_COLUMNS') . ' #' . $col_counter;

			$col_blocks = $blocks_by_column[$c_id] ?? [];

			$this->template->assign_block_vars('columns', [
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
				$b_title = !empty($b['block_title']) ? $b['block_title'] : $this->language->lang('FOOTER_BLOCKS') . ' #' . $b_id;

				$this->template->assign_block_vars('columns.blocks', [
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

		$this->template->assign_vars([
			'U_ACTION'						=> $this->u_action,
			'VINNY_FOOTERMANAGER_ENABLE'	=> (bool) $this->config['vinny_footermanager_enable'],
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

		$sql = 'SELECT group_id, group_name, group_type FROM ' . GROUPS_TABLE . "
			WHERE group_name <> 'BOTS'
			ORDER BY group_name ASC";
		$result = $this->db->sql_query($sql);
		$groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$g_id = (int) $row['group_id'];
			$groups[] = [
				'group_id'   => $g_id,
				'group_name' => $this->group_helper->get_name($row['group_name']),
				'selected'   => in_array($g_id, $selected, true),
			];
		}
		$this->db->sql_freeresult($result);

		return $groups;
	}

	/**
	 * Analyses raw HTML for common security and performance concerns.
	 *
	 * @param string $content
	 * @return array
	 */
	protected function analyse_block_content($content)
	{
		$content = htmlspecialchars_decode($content, ENT_COMPAT);
		$results = [];
		$has_untrusted_connection = false;

		if (preg_match('/alert\s*\(/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_ALERT_USAGE'),
			];
		}

		if (preg_match('/location\s*\.\s*href\s*=/i', $content))
		{
			$results[] = [
				'class'		=> 'errorbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_LOCATION_CHANGE'),
			];
		}

		if (preg_match('/<script\b(?=[^>]*\bsrc\s*=)(?![^>]*\basync\b)[^>]*>/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_SCRIPT_WITHOUT_ASYNC'),
			];
		}

		if (!empty($this->config['server_protocol']) && strpos($this->config['server_protocol'], 'https') === 0 && preg_match('/(?:src|href)\s*=\s*["\']http:\/\//i', $content))
		{
			$has_untrusted_connection = true;
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_UNTRUSTED_CONNECTION'),
			];
		}

		if (!$has_untrusted_connection && preg_match('/(?:src|href|action)\s*=\s*["\'](?:https?:)?\/\//i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_EXTERNAL_RESOURCE'),
			];
		}

		if (preg_match('/<iframe\b/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_IFRAME'),
			];
		}

		if (preg_match('/<form\b/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_FORM'),
			];
		}

		if (preg_match('/\s(on[a-z]+)\s*=/i', $content))
		{
			$results[] = [
				'class'		=> 'warningbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_INLINE_EVENT'),
			];
		}

		if (preg_match('/(?:href|src)\s*=\s*["\']\s*javascript:/i', $content))
		{
			$results[] = [
				'class'		=> 'errorbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_JAVASCRIPT_URI'),
			];
		}

		if (empty($results))
		{
			$results[] = [
				'class'		=> 'successbox',
				'message'	=> $this->language->lang('BLOCK_ANALYSIS_NO_ISSUES'),
			];
		}

		return $results;
	}

	/**
	 * Purges footer blocks and columns cache
	 *
	 * @return void
	 */
	protected function purge_footer_cache()
	{
		$this->cache->destroy('_vinny_footermanager_cache');
		$this->cache->destroy('_vinny_footermanager_reg_groups');
	}
}
