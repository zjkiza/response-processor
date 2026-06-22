# Agent Knowledge: ResponseProcessor Bundle

## Project Structure

```
├── composer.json                          # zjkiza/response-processor
├── .gitignore
├── .php-cs-fixer.dist.php                 # PSR12 + strict_types
├── phpstan.neon                            # level max
├── psalm.xml                               # level 1
├── rector.php                              # PHP 8.0+
├── phpunit.xml.dist                        # PHPUnit 12.4
├── README.md
├── AGENTS.md
├── src/
│   ├── ZJKizaResponseProcessorBundle.php  # extends AbstractBundle (Symfony 5.4+)
│   ├── DependencyInjection/
│   │   ├── Configuration.php              # listener.priority (default 45)
│   │   └── ZJKizaResponseProcessorExtension.php  # loads YAML + sets params
│   ├── Attribute/
│   │   ├── InjectData.php                 # implements ProcessorAttributeInterface
│   │   ├── Processor.php                  # IS_REPEATABLE, $handler string
│   │   ├── ProcessorIdentifier.php        # marker on DTO id property
│   │   └── (InjectPresenter moved to tests/Fixtures as user example)
│   ├── Contract/
│   │   ├── ProcessorAttributeInterface.php # marker for processor attributes
│   │   ├── DataFetcherInterface.php        # fetch(array $ids): array
│   │   ├── IdentifierExtractorInterface.php # extract(object): ?string
│   │   ├── ProcessorHandlerInterface.php   # process(mixed, ViewEvent): void
│   │   └── PropertyResolverInterface.php   # resolve(ReflectionProperty): ?DataFetcherInterface
│   ├── Extractor/
│   │   └── ProcessorIdentifierExtractor.php # single class, caches per class
│   ├── Handler/
│   │   ├── ProcessorEngine.php            # batches fetches, $callSequence cache
│   │   ├── FetcherRegistry.php            # resolves fetcher by ::class
│   │   ├── AttributePropertyResolver.php  # checks property for specific attribute
│   │   ├── AttributeHandler.php           # single handler for any attribute type
│   │   └── ResolverFactory.php            # creates AttributePropertyResolver
│   ├── Listener/
│   │   └── ProcessorListener.php          # kernel.view, indexes by tag key
│   └── Resources/config/
│       └── services.yaml                  # bundle service definitions
└── tests/
```

## Key Conventions

- **Namespaces**: `ZJKiza\ResponseProcessor` (src), `ZJKiza\ResponseProcessor\Tests` (tests)
- **Test namespace**: `ZJKiza\ResponseProcessor\Tests\Unit`
- **Service tags**: `zjkiza_response_processor.handler` (`key` attr), `zjkiza_response_processor.fetcher`
- **Config root**: `zjkiza_response_processor` → `listener.priority` (int, default 45)
- **Autowiring**: enabled for all bundle services
- **YAML only**: bundle uses YAML for service definitions (no PHP config)
- **Marker interface**: `ProcessorAttributeInterface` flags custom attributes for this bundle
- **AbstractBundle**: bundle extends Symfony `AbstractBundle` (works on Symfony 5.4+)
- **CRITICAL — Extension loading**: `AbstractBundle::getContainerExtension()` in Symfony 8+ returns a `BundleExtension` that calls the bundle's own `loadExtension()` — NOT the separate `*Extension` class. To use a custom extension class (with `services.yaml`), the bundle MUST override `getContainerExtension()` to return a `new CustomExtension()`. Without this override, `services.yaml` is never loaded.

## Critical Edge Cases

- `ProcessorListener` indexes handlers by string `key`, not class name — allows multiple services with same PHP class
- `ProcessorEngine` uses `$callSequence` (not `spl_object_id`) to avoid GC reuse collisions
- `FetcherRegistry` indexes by `::class` (last-wins on duplicates)
- `Processor` is `IS_REPEATABLE` — multiple handlers per controller method
- `IdentifierExtractor::extract()` returns `null` for null values or missing `#[ProcessorIdentifier]`
- `InjectData` implements `ProcessorAttributeInterface` for marker pattern recognition

## Testing

```bash
composer install
vendor/bin/phpunit
```

Tests use anonymous classes and fixtures. No database required.

### PHPUnit "Risky" — Symfony ErrorHandler

When using `KernelTestCase::bootKernel()`, `FrameworkBundle::boot()` calls `ErrorHandler::register()` which pushes entries onto the error/exception handler stacks via `set_error_handler()`/`set_exception_handler()`. Each kernel boot adds one entry per stack, and `tearDown()`'s `ensureKernelShutdown()` re-boots the kernel.

PHPUnit 12 detects the extra handlers and marks the test as risky ("Test code or tested code did not remove its own error/exception handlers").

**Fix** — save handlers in `setUp()`, restore in `tearDown()` by popping all entries above the saved one:

```php
private array $savedHandlers = [];

protected function setUp(): void
{
    parent::setUp();

    $this->savedHandlers['error'] = \set_error_handler(static fn () => null);
    \restore_error_handler();

    $this->savedHandlers['exception'] = \set_exception_handler(static fn () => null);
    \restore_exception_handler();
}

protected function tearDown(): void
{
    parent::tearDown();

    while (\set_error_handler(static fn () => null) !== $this->savedHandlers['error']) {
        \restore_error_handler();
        \restore_error_handler();
    }

    \restore_error_handler();

    while (\set_exception_handler(static fn () => null) !== $this->savedHandlers['exception']) {
        \restore_exception_handler();
        \restore_exception_handler();
    }

    \restore_exception_handler();
}
```
