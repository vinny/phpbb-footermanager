# Changelog

All notable changes to the Footer Manager extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Real-time animated feedback icons on drag handles (`fa-check` / `fa-times`) to visually confirm asynchronous order saving without page reloads.

### Fixed
- Replaced concatenated HTML `<option>` tags in PHP with native Twig block loops in the ACP block template.

### Security
- Prevented potential DOM-based XSS in the ACP by creating empty-column state labels safely via `document.createTextNode()`.

### Changed
- Made column and block drag-and-drop reordering fully asynchronous by removing full page reloads and managing empty column states dynamically in the DOM.
- Replaced inline CSS styles on ACP action icons with dedicated CSS classes.
- Moved inline BBCode panel toggle script and event handlers from the ACP block template into `acp_footermanager.js`.
- Cleaned up duplicate AJAX callback registrations in `acp_footermanager.js`.
- Removed redundant `revert_data()` from `v100_data` migration.
- Removed unused `$column` parameter from `render_block_content()` in `listener.php`.
- Refined column title alignment by removing redundant top margin from `.faq-title`.

## [1.0.0] - 2026-10-22

### Added
- Multi-column footer layout supporting between one and four responsive columns.
- Custom footer block system with support for rich text, BBCode parsing, and raw HTML.
- Per-block user group visibility settings to display or hide content from specific groups.
- Drag-and-drop column and block reordering in the ACP powered by Sortable.js.
- Interactive live footer preview in the ACP that updates in real time and only renders active columns.
- Dedicated caching layer to retrieve footer data with minimal database queries.
- Clean integration with phpBB prosilver via the `overall_footer_copyright_prepend` template event.
- Subtle divider line matching prosilver's style guidelines and full Right-to-Left (RTL) language support.
- Complete English (`en`) and Brazilian Portuguese (`pt_br`) language packs.
- Automated test suites including PHPUnit unit and functional tests, database schema checks, language parity tests, ESLint, and Stylelint.

[Unreleased]: https://github.com/vinny/phpbb-footermanager/compare/1.0.0...HEAD
[1.0.0]: https://github.com/vinny/phpbb-footermanager/releases/tag/1.0.0
