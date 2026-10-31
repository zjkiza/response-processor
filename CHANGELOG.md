# Changelog

All notable changes to this project will be documented in this file.

## [0.8.0]

### Added
- GitHub Actions workflow (`.github/workflows/ci.yml`): PHPUnit on PHP 8.2–8.5 × Symfony 5.4/6.4/7.4/8.1 (plus `--prefer-lowest`), plus a quality job: `composer validate`, composer-unused, PHPStan, Psalm and PHP-CS-Fixer (dry-run).

### Fixed
- The bundle extends `Symfony\Component\HttpKernel\Bundle\Bundle` instead of `AbstractBundle` (which does not exist in Symfony 5.4), so the advertised Symfony 5.4 support now actually works.
- `phpstan.neon` no longer fails on a clean checkout when `tests/Resources/var` does not exist.
- Dev dependencies (`symfony/framework-bundle`, `yaml`, `http-client`, `phpunit-bridge`, `phpunit/phpunit`) are no longer pinned to Symfony 8 / PHPUnit 12, so older PHP/Symfony versions can be installed for testing.

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
