# Changelog

All notable changes to this project will be documented in this file.

## [0.7.0]

### Changed
- The registration method of services that implement "DataFetcherInterface::class".
- The signature of the DataFetcherInterface::fetch method. From `fetch(array $identifiers): array` to `fetch(iterable $identifiers): array` 
- Simplified creation of custom attributes. Extend `InjectDataAbstract` for a minimal custom attribute:

```php
use ZJKiza\ResponseProcessor\Attribute\InjectDataAbstract;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class InjectPresenter extends InjectDataAbstract {}
```

The `$fetcher` property and `ProcessorAttributeInterface` implementation are inherited — you only need to add the `#[\Attribute]` declaration and extend the abstract class.


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
