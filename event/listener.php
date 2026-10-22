<?php
/**
 *
 * Footer Manager extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\footermanager\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string */
	protected $table_prefix;

	/** @var \phpbb\cache\driver\driver_interface|null */
	protected $cache;

	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		$table_prefix,
		\phpbb\cache\driver\driver_interface $cache = null
	)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->table_prefix = $table_prefix;
		$this->cache = $cache;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'	=> 'load_language_on_setup',
			'core.page_header'	=> 'on_page_header',
			'core.page_footer'	=> 'on_page_footer',
		];
	}

	/**
	 * Load language file
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'vinny/footermanager',
			'lang_set' => 'footermanager',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Inject header CSS variables and meta if enabled
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function on_page_header($event)
	{
		if (!$this->is_footer_enabled())
		{
			return;
		}

		$this->template->assign_vars([
			'S_VINNY_FOOTERMANAGER_ENABLED'	=> true,
			'S_VINNY_FOOTERMANAGER_ACTIVE'	=> true,
		]);
	}

	/**
	 * Render footer columns and blocks on page footer
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function on_page_footer($event)
	{
		if (!$this->is_footer_enabled())
		{
			return;
		}

		$cache_key = '_vinny_footermanager_cache';
		$cached_data = ($this->cache !== null) ? $this->cache->get($cache_key) : false;

		if ($cached_data === false)
		{
			$columns_table = $this->table_prefix . 'vinny_footermanager_columns';
			$blocks_table  = $this->table_prefix . 'vinny_footermanager_blocks';

			$sql = 'SELECT * FROM ' . $columns_table . '
				WHERE column_enabled = 1
				ORDER BY column_order ASC';
			$result = $this->db->sql_query($sql);
			$columns = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			$sql = 'SELECT * FROM ' . $blocks_table . '
				WHERE block_enabled = 1
				ORDER BY block_order ASC';
			$result = $this->db->sql_query($sql);
			$blocks = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			$cached_data = [
				'columns'	=> $columns,
				'blocks'	=> $blocks,
			];

			if ($this->cache !== null)
			{
				$this->cache->put($cache_key, $cached_data);
			}
		}

		$columns = $cached_data['columns'] ?? [];
		$blocks  = $cached_data['blocks'] ?? [];

		if (empty($blocks))
		{
			return;
		}

		// Check if any active block has group restrictions configured
		$has_group_restrictions = false;
		foreach ($blocks as $block)
		{
			if (!empty($block['block_hide_groups']))
			{
				$has_group_restrictions = true;
				break;
			}
		}

		$user_id = isset($this->user->data['user_id']) ? (int) $this->user->data['user_id'] : 0;
		$primary_group = isset($this->user->data['group_id']) ? (int) $this->user->data['group_id'] : 0;
		$is_bot = !empty($this->user->data['is_bot']);
		$is_anonymous = ($user_id <= 0 || (defined('ANONYMOUS') && $user_id === (int) ANONYMOUS) || $user_id === 1);

		$user_groups = [];
		$base_groups = [2, 3];

		if ($has_group_restrictions)
		{
			$base_groups = $this->get_base_groups();

			if ($is_anonymous || $is_bot)
			{
				$user_groups = ($primary_group > 0) ? [$primary_group] : [];
			}
			else
			{
				$user_groups = ($user_id > 0) ? $this->get_user_groups($user_id) : [];
				if (empty($user_groups) && $primary_group > 0)
				{
					$user_groups = [$primary_group];
				}
			}
		}

		// Group blocks by column respecting visibility
		$blocks_by_col = [];
		foreach ($blocks as $block)
		{
			if ($has_group_restrictions && !empty($block['block_hide_groups']))
			{
				$hidden_groups = array_map('intval', explode(',', $block['block_hide_groups']));
				if ($this->is_item_hidden($user_groups, $primary_group, $hidden_groups, $base_groups))
				{
					continue;
				}
			}

			$blocks_by_col[$block['column_id']][] = $block;
		}

		$visible_columns = [];
		foreach ($columns as $column)
		{
			$col_id = (int) $column['column_id'];
			$col_blocks = $blocks_by_col[$col_id] ?? [];

			// If column has no visible blocks, skip it
			if (!empty($col_blocks))
			{
				$visible_columns[] = [
					'data'   => $column,
					'blocks' => $col_blocks,
				];
			}
		}

		$visible_columns_count = count($visible_columns);

		$width_spans = [
			'1/4'	=> 3,
			'1/3'	=> 4,
			'1/2'	=> 6,
			'2/3'	=> 8,
			'3/4'	=> 9,
		];

		$has_custom_width = false;
		$total_explicit_span = 0;
		$auto_count = 0;

		foreach ($visible_columns as $vc)
		{
			$w = $vc['data']['column_width'] ?? 'auto';
			if (isset($width_spans[$w]))
			{
				$has_custom_width = true;
				$total_explicit_span += $width_spans[$w];
			}
			else
			{
				$auto_count++;
			}
		}

		$auto_span = 12;
		if ($has_custom_width && $auto_count > 0)
		{
			$remaining = max(0, 12 - $total_explicit_span);
			$auto_span = max(1, (int) floor($remaining / $auto_count));
		}

		foreach ($visible_columns as $vc)
		{
			$column = $vc['data'];
			$col_blocks = $vc['blocks'];
			$col_id = (int) $column['column_id'];
			$col_title = $column['column_title'];
			$w = $column['column_width'] ?? 'auto';

			$col_span = 0;
			if ($has_custom_width)
			{
				$col_span = isset($width_spans[$w]) ? $width_spans[$w] : $auto_span;
			}

			$this->template->assign_block_vars('vfm_columns', [
				'ID'			=> $col_id,
				'TITLE'			=> $col_title,
				'WIDTH'			=> $w,
				'SPAN'			=> $col_span,
				'BLOCKS_COUNT'	=> count($col_blocks),
			]);

			foreach ($col_blocks as $b)
			{
				$rendered_content = $this->render_block_content($b);

				$this->template->assign_block_vars('vfm_columns.vfm_blocks', [
					'ID'		=> (int) $b['block_id'],
					'CONTENT'	=> $rendered_content,
				]);
			}
		}

		$this->template->assign_vars([
			'S_VINNY_FOOTERMANAGER_ENABLED'	=> true,
			'S_VINNY_FOOTERMANAGER_VISIBLE'	=> ($visible_columns_count > 0),
			'VFM_TOTAL_VISIBLE_COLUMNS'		=> $visible_columns_count,
			'S_VFM_CUSTOM_WIDTHS'			=> $has_custom_width,
		]);
	}

	/**
	 * Process and render block content (BBCode or HTML)
	 *
	 * @param array $block
	 * @return string
	 */
	protected function render_block_content(array $block)
	{
		$content = $block['block_content'];

		if (!empty($block['block_parse_bbcode']))
		{
			return generate_text_for_display(
				$content,
				$block['bbcode_uid'],
				$block['bbcode_bitfield'],
				$block['bbcode_options']
			);
		}

		return htmlspecialchars_decode($content, ENT_COMPAT);
	}

	/**
	 * Check if footer manager is globally enabled
	 *
	 * @return bool
	 */
	protected function is_footer_enabled()
	{
		return !empty($this->config['vinny_footermanager_enable']);
	}

	/**
	 * Get group IDs for a user.
	 *
	 * @param int $user_id
	 * @return array
	 */
	protected function get_user_groups($user_id)
	{
		$user_id = (int) $user_id;
		if ($user_id <= 0)
		{
			return [];
		}

		// Guests / Anonymous users do not have secondary groups in USER_GROUP_TABLE
		if ((defined('ANONYMOUS') && $user_id === (int) ANONYMOUS) || $user_id === 1)
		{
			$primary = isset($this->user->data['group_id']) ? (int) $this->user->data['group_id'] : 1;
			return [$primary];
		}

		$groups = [];
		$sql = 'SELECT group_id FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $user_id . '
				AND user_pending = 0';
		$result = $this->db->sql_query($sql, 3600);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$groups[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $groups;
	}

	/**
	 * Check if an item is hidden for a user based on groups.
	 *
	 * @param array $user_groups User's group IDs
	 * @param int $primary_group User's primary group ID
	 * @param array $hidden_groups Group IDs that should NOT see the item
	 * @param array $base_groups Base group IDs (REGISTERED, REGISTERED_COPPA)
	 * @return bool True if item should be hidden, false if visible
	 */
	public function is_item_hidden(array $user_groups, $primary_group, array $hidden_groups, array $base_groups = [2, 3])
	{
		if (empty($hidden_groups) || empty($user_groups))
		{
			return false;
		}

		$primary_group = (int) $primary_group;

		// User's primary group is in hidden_groups
		if ($primary_group > 0 && in_array($primary_group, $hidden_groups, true))
		{
			// If primary group is a base group (e.g. REGISTERED), check if user has an unhidden secondary group
			if (in_array($primary_group, $base_groups, true))
			{
				$secondary_groups = array_diff($user_groups, $base_groups);
				$unhidden_secondary = array_diff($secondary_groups, $hidden_groups);

				if (empty($unhidden_secondary))
				{
					return true;
				}

				return false;
			}

			// Primary group is a specific group (e.g. Administrators or Global Mods) and is marked hidden -> HIDE!
			return true;
		}

		// User's primary group is a non-base specific group and is NOT in hidden_groups (e.g. Primary = Administrators) -> VISIBLE!
		if ($primary_group > 0 && !in_array($primary_group, $base_groups, true))
		{
			return false;
		}

		// Fallback for primary group = base group or 0: check if all non-base user groups are hidden
		$non_base_user_groups = array_diff($user_groups, $base_groups);
		if (!empty($non_base_user_groups))
		{
			$unhidden_groups = array_diff($non_base_user_groups, $hidden_groups);
			if (empty($unhidden_groups))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get base registered group IDs (e.g. REGISTERED and REGISTERED_COPPA).
	 *
	 * @return array
	 */
	protected function get_base_groups()
	{
		$ids = ($this->cache !== null) ? $this->cache->get('_vinny_footermanager_reg_groups') : false;
		if (is_array($ids))
		{
			return $ids;
		}

		$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . "
			WHERE group_name IN ('REGISTERED', 'REGISTERED_COPPA')";
		$result = $this->db->sql_query($sql);
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		$ids = !empty($ids) ? $ids : [2, 3];
		if ($this->cache !== null)
		{
			$this->cache->put('_vinny_footermanager_reg_groups', $ids, 86400);
		}

		return $ids;
	}
}
