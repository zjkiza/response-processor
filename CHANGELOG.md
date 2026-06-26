# Changelog

All notable changes to this project will be documented in this file.

## [0.6.2]

### Changed
- Changed the registration method of services that implement "DataFetcherInterface::class".

## [0.6.1]

### Bugfix
- Skips classes that cannot be autoloaded

## [0.6.0]

### Fixed
- Manual `tags: [zjkiza_response_processor.fetcher]` from test services (now handled by compiler pass)

### Added
- `DataFetcherCompilerPass` — auto-tags all `DataFetcherInterface` implementations;

### Changed
- Renamed `InjectData::$repository` → `$fetcher` (consistent with naming across bundle)

## [0.5.0]
Initial version
