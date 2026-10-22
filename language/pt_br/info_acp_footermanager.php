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
	'ACP_VINNY_FOOTERMANAGER_SETTINGS'		=> 'Configurações',
	'ACP_VINNY_FOOTERMANAGER_BLOCKS'		=> 'Gerenciar colunas e blocos',
	'ACP_VINNY_FOOTERMANAGER_EXPLAIN'		=> 'Configure o rodapé do fórum com colunas e blocos de conteúdo personalizados.',

	'FOOTERMANAGER_SUPPORT_STAR'			=> 'Se você gosta desta extensão, por favor dê uma estrela no <a href="https://github.com/vinny/phpbb-footermanager" target="_blank" rel="noopener"><i class="icon fa fa-github fa-fw" aria-hidden="true"></i>GitHub</a>.',
	'FOOTERMANAGER_SUPPORT_DONATE'			=> 'Você também pode apoiar o desenvolvimento com uma <a href="https://ko-fi.com/vinny1" target="_blank" rel="noopener"><i class="icon fa fa-heart fa-fw" aria-hidden="true"></i>doação</a> opcional.',

	// Logs
	'LOG_VINNY_FOOTERMANAGER_SETTINGS'		=> '<strong>Configurações do Footer Manager atualizadas</strong>',
	'LOG_VINNY_FOOTERMANAGER_BLOCK_ADDED'	=> '<strong>Bloco do rodapé adicionado</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_BLOCK_EDITED'	=> '<strong>Bloco do rodapé atualizado</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_BLOCK_DELETED'	=> '<strong>Bloco do rodapé excluído</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_COL_ADDED'		=> '<strong>Coluna do rodapé adicionada</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_COL_EDITED'	=> '<strong>Coluna do rodapé atualizada</strong><br />» %s',
	'LOG_VINNY_FOOTERMANAGER_COL_DELETED'	=> '<strong>Coluna do rodapé excluída</strong><br />» %s',
]);
