# psr14-event-list Documentation

## Overview

The `psr14-event-list` extension provides a backend module for listing all PSR-14 events from installed packages in TYPO3. This extension helps developers easily identify and access PSR-14 events across the system, and it can be particularly useful for debugging and documentation purposes.

Additionally, a Symfony command is available to generate documentation links for the listed events. (for now only works for the TYPO3 Core events)

## Installation

You can install the extension via Composer by running the following command:

```bash
composer require --dev cru/psr14-event-list
```

Once installed and activated, the module will automatically appear in the TYPO3 Backend.

## Usage

### Backend Module

After installation, you can access the new backend module under the **System** section in the TYPO3 backend. This module will list all PSR-14 events provided by installed packages.

> **Warning**  
> The list of PSR-14 events is generated automatically. As a result, false positives may occur. Please verify the events and check the TYPO3 documentation for further clarification:  
> [TYPO3 Documentation](https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Events/Events/Index.html#eventlist)


### Symfony Command

You can also use the Symfony command to list the PSR-14 events and optionally fetch documentation links.

#### Command Syntax

```bash
cru:list-psr14-events [options]
```

#### Options

- `--no-cache`  
  Disable caching (lower performance, up-to-date results)

- `--no-docs`  
  Disable fetching Documentation link (faster performance)

- `--no-table-separator`  
  Prevent a table separator in the output

- `--no-table`  
  Do not use table output, show results in plaintext format

- `--show-missing`  
  List all PSR-14 events with missing documentation

- `--vertical-table`  
  Use a vertical table layout

- `-h`, `--help`  
  Display help for the given command. If no command is specified, help for the list command will be displayed.

- `--silent`  
  Suppress all messages, only show errors

- `-q`, `--quiet`  
  Only errors will be displayed. All other output is suppressed.

- `-V`, `--version`  
  Show the version of the application

- `--ansi|--no-ansi`  
  Force (or disable) ANSI output

- `-n`, `--no-interaction`  
  Do not ask any interactive questions

- `-v|vv|vvv`, `--verbose`  
  Increase verbosity of messages (1 for normal output, 2 for more verbose, 3 for debug)

#### Example Command

To list all PSR-14 events and fetch documentation links, you can run:

```bash
./vendor/bin/typo3 cru:list-psr14-events
```

To list events without documentation links:

```bash
./vendor/bin/typo3 cru:list-psr14-events --no-docs
```

To refresh event data with updated results (useful if documentation links show "none-found"):

```bash
./vendor/bin/typo3 cru:list-psr14-events --no-cache
```

## Features

- The extension uses the `TYPO3\CMS\Core\Package\PackageManager` to find installed packages.
- All classes ending with `Event.php` are considered PSR-14 events.
- Documentation links for events can be fetched using the Symfony command. If no documentation is found, the `--show-missing` option will list those events.

## Troubleshooting

- If you see `#none-found` under the documentation column, you can run the Symfony command with the `--no-cache` option to force a fresh fetch of the event documentation.
  
## License

This extension is licensed under the **GPL-2.0-or-later** license.

## Contribution

Contributions to this extension are welcome. You can open Pull Requests in the repository:  
[https://github.com/Rathch/cru-psr14-event-list](https://github.com/Rathch/cru-psr14-event-list)

## Credits

- [Garvin Hicking](https://github.com/garvinhicking) for improving and inspiring the development of this extension.
