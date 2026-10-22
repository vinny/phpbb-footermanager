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
	'FOOTER_BLOCKS_EXPLAIN'					=> 'Organize colunas e blocos para montar o rodapé. Arraste e solte os blocos entre as colunas para reordená-los.',

	// Settings Mode
	'FOOTER_ENABLE'							=> 'Ativar footer manager',
	'FOOTER_ENABLE_EXPLAIN'					=> 'Ativa ou desativa o rodapé personalizado no fórum.',

	// Columns Management
	'FOOTER_COLUMNS'						=> 'Colunas do rodapé',
	'FOOTER_ADD_COLUMN'						=> 'Adicionar coluna',
	'FOOTER_EDIT_COLUMN'					=> 'Editar coluna',
	'FOOTER_DELETE_COLUMN'					=> 'Excluir coluna',
	'FOOTER_CONFIRM_DELETE_COLUMN'			=> 'Tem certeza de que deseja excluir esta coluna? Todos os blocos dentro dela também serão excluídos.',
	'FOOTER_COLUMN_TITLE'					=> 'Título da coluna',
	'FOOTER_COLUMN_TITLE_EXPLAIN'			=> 'Título exibido no topo da coluna.',
	'FOOTER_COLUMN_WIDTH'					=> 'Largura da coluna',
	'FOOTER_COLUMN_WIDTH_EXPLAIN'			=> 'Defina a largura da coluna ou selecione automático para divisão igual.',
	'FOOTER_WIDTH_AUTO'						=> 'Automático (divisão igual)',
	'FOOTER_WIDTH_1_4'						=> '1/4 (25%)',
	'FOOTER_WIDTH_1_3'						=> '1/3 (33%)',
	'FOOTER_WIDTH_1_2'						=> '1/2 (50%)',
	'FOOTER_WIDTH_2_3'						=> '2/3 (66%)',
	'FOOTER_WIDTH_3_4'						=> '3/4 (75%)',
	'FOOTER_COLUMN_ENABLED'					=> 'Ativada',
	'FOOTER_COLUMN_EMPTY'					=> 'Esta coluna ainda não possui blocos. Arraste blocos para cá ou clique em “Adicionar bloco”.',
	'FOOTER_COLUMN_SAVED'					=> 'Coluna salva.',
	'FOOTER_COLUMN_DELETED'					=> 'Coluna excluída.',
	'FOOTER_MAX_COLUMNS_REACHED'			=> 'Você pode criar no máximo 8 colunas.',

	// Blocks Management
	'FOOTER_BLOCKS'							=> 'Blocos',
	'FOOTER_ADD_BLOCK'						=> 'Adicionar bloco',
	'FOOTER_EDIT_BLOCK'						=> 'Editar bloco',
	'FOOTER_DELETE_BLOCK'					=> 'Excluir bloco',
	'FOOTER_CONFIRM_DELETE_BLOCK'			=> 'Tem certeza de que deseja excluir este bloco?',
	'FOOTER_BLOCK_NAME'						=> 'Título do bloco',
	'FOOTER_BLOCK_COLUMN'					=> 'Coluna',
	'FOOTER_BLOCK_ENABLED'					=> 'Ativado',
	'FOOTER_HIDE_FOR_GROUPS'				=> 'Ocultar para grupos de usuários',
	'FOOTER_HIDE_FOR_GROUPS_EXPLAIN'		=> 'Selecione os grupos que não devem ver este bloco.',
	'BLOCK_CONTENT'							=> 'Conteúdo',
	'BLOCK_CONTENT_EXPLAIN'					=> 'Informe o conteúdo deste bloco. BBCodes padrão, smilies e links são suportados quando a interpretação de BBCode estiver ativada. Se desativada, o HTML será exibido diretamente, portanto insira apenas código de fontes confiáveis.',
	'PARSE_BBCODE'							=> 'Interpretar BBCode',
	'FOOTER_BLOCK_SAVED'					=> 'Bloco salvo.',
	'FOOTER_BLOCK_DELETED'					=> 'Bloco excluído.',
	'FOOTER_BLOCK_EMPTY'					=> 'Nenhum bloco cadastrado. Adicione um bloco para começar.',
	'FOOTER_LIVE_PREVIEW'					=> 'Prévia do rodapé',
	'FOOTER_PREVIEW_EXPLAIN'				=> 'Pré-visualização de como este bloco será exibido na coluna em relação ao restante do rodapé.',
	'FOOTER_DRAG_DROP_HINT'					=> 'Arraste os blocos para reordená-los dentro da coluna ou entre outras colunas.',
	'FOOTER_DRAG_DROP_COL_HINT'				=> 'Arraste as colunas para reordená-las.',

	// Block Validation
	'BLOCK_NAME_EMPTY'						=> 'Você deve informar um título para o bloco.',
	'BLOCK_NAME_TOO_LONG'					=> 'O título do bloco não pode ter mais de 255 caracteres.',
	'BLOCK_CONTENT_EMPTY'					=> 'Você deve informar o conteúdo do bloco.',

	// HTML Security and Analysis
	'BLOCK_TRUSTED_HTML_WARNING'			=> 'Adicione apenas HTML de fontes confiáveis. O código informado será executado diretamente no navegador dos visitantes.',
	'BLOCK_ANALYSE_HTML'					=> 'Analisar HTML',
	'BLOCK_ANALYSIS'						=> 'Análise de HTML',
	'BLOCK_PREVIEW'							=> 'Prévia',
	'BLOCK_ANALYSIS_NO_ISSUES'				=> 'Nenhum problema comum de HTML foi detectado.',
	'BLOCK_ANALYSIS_ALERT_USAGE'			=> 'O HTML contém alert(). Isso geralmente é código de depuração e deve ser removido.',
	'BLOCK_ANALYSIS_LOCATION_CHANGE'		=> 'O HTML altera location.href, o que pode redirecionar os visitantes. Certifique-se de que o código seja confiável.',
	'BLOCK_ANALYSIS_SCRIPT_WITHOUT_ASYNC'	=> 'O HTML carrega um script externo sem o atributo async. Considere adicionar async para não bloquear o carregamento da página.',
	'BLOCK_ANALYSIS_UNTRUSTED_CONNECTION'	=> 'O fórum usa HTTPS, mas este HTML carrega recursos via HTTP inseguro. Use URLs em HTTPS para evitar avisos de conteúdo misto.',
	'BLOCK_ANALYSIS_EXTERNAL_RESOURCE'		=> 'O HTML carrega recursos externos. Certifique-se de que a origem seja confiável.',
	'BLOCK_ANALYSIS_IFRAME'					=> 'O HTML contém um iframe. Certifique-se de que o conteúdo incorporado seja de origem confiável.',
	'BLOCK_ANALYSIS_FORM'					=> 'O HTML contém um formulário. Certifique-se de que o envio seja destinado apenas a locais confiáveis.',
	'BLOCK_ANALYSIS_INLINE_EVENT'			=> 'O HTML contém eventos JavaScript embutidos (como onclick ou onload). Certifique-se de que o código seja confiável.',
	'BLOCK_ANALYSIS_JAVASCRIPT_URI'			=> 'O HTML contém um link javascript:. Certifique-se de que o código seja confiável antes de salvar.',
]);
