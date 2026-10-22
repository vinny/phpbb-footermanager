# Footer Manager for phpBB [![Tests](https://github.com/vinny/phpbb-footermanager/actions/workflows/tests.yml/badge.svg)](https://github.com/vinny/phpbb-footermanager/actions/workflows/tests.yml)

A phpBB extension that adds a customisable, responsive multi-column footer to your board, managed directly from the Administration Control Panel (ACP).

## Features

- **Multi-column layout**: Create up to 8 columns styled to match native prosilver panels.
- **Drag-and-drop builder**: Arrange columns and blocks visually with live reordering.
- **BBCode and HTML content**: Add content using standard BBCode (with editor buttons and smilies) or direct HTML with built-in safety analysis.
- **Live block preview**: Preview how a block looks in its exact column position and width before saving.
- **Column widths**: Choose between auto, 1/4, 1/3, 1/2, 2/3, and 3/4 widths with automatic grid distribution.
- **Group permissions**: Hide specific blocks from selected user groups.
- **Prosilver integration**: Uses phpBB’s native styling, keeping the original copyright line untouched below the footer.
- **Built-in caching**: Cache footer data to avoid extra database queries on page loads.

## Requirements

- phpBB 3.3.0 or higher
- PHP 7.2.0 or higher

## Installation

1. Download the extension archive.
2. Copy the files into `ext/vinny/footermanager/`.
3. In your board ACP, go to **Customise** &rarr; **Manage extensions**.
4. Find **Footer Manager** under *Disabled Extensions* and click **Enable**.
5. Configure your footer in **Extensions** &rarr; **Footer Manager**.

## Uninstallation

1. Go to **Customise** &rarr; **Manage extensions**.
2. Click **Disable** next to Footer Manager.
3. To permanently remove the database tables and settings, click **Delete data**.

## Support

[![Donate with Ko-fi](https://img.shields.io/badge/Donate-Ko--fi-29ABE2.svg)](https://ko-fi.com/vinny1)

## License

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](license.txt)
