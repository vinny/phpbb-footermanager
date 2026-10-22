/**
 * Footer Manager ACP JavaScript
 * Copyright (c) 2026 Vinny
 */

(function($) {
	'use strict';

	function registerCallbacks() {
		if (typeof phpbb !== 'undefined' && phpbb.addAjaxCallback) {
			phpbb.addAjaxCallback('vfm_toggle_enabled', function(res) {
				if (res.success) {
					var $el = $(this);
					var $icon = $el.find('i.icon');
					$icon.removeClass('fa-check-circle fa-times-circle vfm-icon-enabled vfm-icon-disabled')
						.addClass(res.icon_class + ' ' + (res.icon_color === '#228822' ? 'vfm-icon-enabled' : 'vfm-icon-disabled'))
						.removeAttr('style')
						.attr('title', res.title)
						.attr('data-original-title', res.title);
				}
			});

			phpbb.addAjaxCallback('row_delete', function(_res) {
				var $target = $(this).closest('.vfm-block-card, .vfm-column-card, tr, li');
				if ($target.length) {
					var $list = $target.closest('.vfm-blocks-list');
					$target.fadeOut(300, function() {
						$target.remove();
						if ($list.length && !$list.find('.vfm-block-card').length && !$list.find('.vfm-empty-col-drop').length) {
							$list.append('<div class="vfm-empty-col-drop"><i class="icon fa-inbox fa-2x vfm-empty-col-icon"></i></div>');
						}
					});
				}
			});
		}
	}

	$(function() {
		registerCallbacks();

		// BBCode panel toggle on block edit page
		var $parseBbcode = $('#block_parse_bbcode');
		if ($parseBbcode.length) {
			var toggleBbcode = function(isBbcode) {
				$('#bbcode_controls_panel').toggle(isBbcode);
				$('#trusted_html_warning').toggle(!isBbcode);
				$('#color_palette_placeholder').toggle(isBbcode);
				var flow = document.documentElement.dir === 'rtl' ? 'margin-right' : 'margin-left';
				$('#block_content_dd').css(flow, isBbcode ? '90px' : '0px');
				$('#bbcode_status_dd').toggle(isBbcode);
				$('#btn_analyse_html').toggle(!isBbcode);
			};

			$parseBbcode.on('change', function() {
				toggleBbcode(this.checked);
			});
		}

		var container = $('#vfm-blocks-data');
		if (container.length) {
			var updateOrderURL = container.attr('data-url');
			var updateOrderHash = container.attr('data-hash');

			if (updateOrderURL && updateOrderURL.indexOf('&amp;') !== -1) {
				updateOrderURL = updateOrderURL.replace(/&amp;/g, '&');
			}

			// Sortable for columns
			var columnsGrid = document.querySelector('.vfm-columns-grid');
			if (columnsGrid && typeof Sortable !== 'undefined') {
				Sortable.create(columnsGrid, {
					animation: 150,
					handle: '.vfm-col-drag-handle',
					draggable: '.vfm-column-card',
					ghostClass: 'vfm-sortable-ghost',
					onEnd: function() {
						var columnIds = [];
						$('.vfm-column-card[data-col-id]').each(function() {
							columnIds.push($(this).data('col-id'));
						});

						$.ajax({
							url: updateOrderURL,
							type: 'POST',
							data: {
								action: 'update_columns_order',
								columns_order: JSON.stringify(columnIds),
								hash: updateOrderHash
							},
							success: function() {
								window.location.reload();
							}
						});
					}
				});
			}

			// Sortable for blocks
			var saveBlockOrder = function() {
				var orderData = {};

				$('.vfm-blocks-list').each(function() {
					var columnId = $(this).data('column-id');
					var blockIds = [];

					$(this).find('.vfm-block-card[data-id]').each(function() {
						blockIds.push($(this).data('id'));
					});

					orderData[columnId] = blockIds;
				});

				$.ajax({
					url: updateOrderURL,
					type: 'POST',
					data: {
						action: 'update_order',
						order: JSON.stringify(orderData),
						hash: updateOrderHash
					},
					success: function() {
						window.location.reload();
					}
				});
			};

			if (typeof Sortable !== 'undefined') {
				$('.vfm-blocks-list').each(function() {
					Sortable.create(this, {
						group: 'vfm_blocks_shared',
						animation: 150,
						handle: '.vfm-block-drag-handle',
						draggable: '.vfm-block-card',
						filter: '.vfm-empty-col-drop',
						ghostClass: 'vfm-sortable-ghost',
						onEnd: saveBlockOrder
					});
				});
			}
		}
	});
})(jQuery);
