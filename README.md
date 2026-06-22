# ResponseProcessor Bundle

A Symfony bundle that fetches and injects related data into DTOs after a controller returns, using PHP 8 attributes.

## How it works

1. Controller returns DTOs with `#[ProcessorIdentifier]` on the ID property
2. DTO properties are annotated with `#[InjectData(SomeFetcher::class)]` or a custom attribute
3. `ProcessorListener` intercepts `kernel.view`, reads `#[Processor('handler_key')]` from the controller method
4. Each registered handler delegates to `ProcessorEngine`, which batches fetches per `DataFetcher` class (one `fetch()` call per class per request) and injects results directly into DTO properties

## When to use it

### The problem

After a controller returns DTOs (or any response objects), you often need to **enrich them with related data** — tags, authors, categories, metadata, or data from external sources. Common approaches all have drawbacks:

- Fetching manually in the controller → bloated logic, repeated across endpoints
- Doctrine lazy loading / serialization → N+1 queries or oversized JOINs
- Event subscribers / post-controller hooks → ad-hoc, no reuse between handlers

### Use cases

**1. API responses with relational data**
A controller returns `PostDto[]`, each with `author`, `tags`, `attachments`. Instead of manually populating fields, annotate DTO properties with `#[InjectData(TagFetcher::class)]`. The bundle populates them before serialization — zero controller code.

**2. CQRS read models**
A query handler returns a read model that combines data from PostgreSQL, Redis, and an HTTP API. Each source gets its own `DataFetcher`, each property its own `#[InjectData(...)]`. Batching is automatic — one `fetch()` call per source per request.

**3. Multi-source user profiles**
User details from the database, notifications from Redis, permissions from a remote API. One controller, one `#[Processor]`, multiple DTO properties with different fetchers — all resolved in the same `kernel.view` pass.

**4. Aggregate views with expensive fields**
Some DTO fields are costly to load (e.g. full-text search data, analytics). Instead of always joining or always lazy-loading, mark them with `#[InjectData]`. The bundle resolves them **only when needed** — no premature optimization, no waste.

### What you gain

- **Batch fetch** — each `DataFetcher::fetch()` is called **once** per request with all identifiers at once. No N+1, no redundant calls.
- **Handler key system** — the same PHP handler class can be registered under multiple keys (e.g. `api.tag` vs `web.tag`) with different behavior, tag resolution, or configuration.
- **Zero config for fetchers** — every `DataFetcherInterface` implementation is auto-tagged. Just create the class.
- **Clean controllers** — controllers only assemble and return DTOs. They know nothing about fetching or injection.
- **Predictable performance** — `O(1)` batch calls per `DataFetcher` class, property resolution cached per call sequence.
- **Extensible** — create a custom `#[Attribute]`, implement `ProcessorAttributeInterface`, register an `AttributeHandler` with your key, and it works.
- **Battle-tested** — 96.97% code coverage, 37 tests, 0 PHPStan errors at level max.

## Installation

```bash
composer require zjkiza/response-processor
```

```php
// config/bundles.php
ZJKiza\ResponseProcessor\ZJKizaResponseProcessorBundle::class => ['all' => true],
```

## Configuration

```yaml
# config/packages/zjkiza_response_processor.yaml
zjkiza_response_processor:
    listener:
        priority: 45            # kernel.view listener priority (default: 45)
```

## Usage

### 1. Define your DTO

Mark the ID property with `#[ProcessorIdentifier]` and properties to be injected with `#[InjectData]`:

```php
use ZJKiza\ResponseProcessor\Attribute\ProcessorIdentifier;
use ZJKiza\ResponseProcessor\Attribute\InjectData;

class MediaDto
{
    public function __construct(
        #[ProcessorIdentifier]
        public string $id,
        public string $name,
        #[InjectData(TagFetcher::class)]
        public ?TagDto $tag = null,
        #[InjectData(PresenterFetcher::class)]
        public ?array $presenters = null,
    ) {}
}
```

`#[InjectData]` points to a `DataFetcherInterface` implementation that provides the data.

### 2. Create a DataFetcher

```php
use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;

class TagFetcher implements DataFetcherInterface
{
    /** @param string[] $identifiers */
    public function fetch(array $identifiers): array
    {
        // return ['id1' => TagDto(...), 'id2' => TagDto(...)]
    }
}
```

All `DataFetcherInterface` implementations are auto-tagged with `zjkiza_response_processor.fetcher`.

### 3. Register handlers

```yaml
# config/services.yaml
services:
    app.tag_handler:
        class: ZJKiza\ResponseProcessor\Handler\AttributeHandler
        arguments:
            $attributeClass: 'ZJKiza\ResponseProcessor\Attribute\InjectData'
        tags:
            - { name: 'zjkiza_response_processor.handler', key: 'app.tag_handler' }
```

The `key` tag matches the value in `#[Processor('key')]`. Different keys can use the same `attributeClass` or different ones.

### 4. Use on controller

```php
use ZJKiza\ResponseProcessor\Attribute\Processor;

class MediaController
{
    #[Processor('app.tag_handler')]
    public function __invoke(): array
    {
        return [new MediaDto('1', 'Title')];
    }
}
```

Multiple handlers can be stacked on one method:

```php
#[Processor('app.tag_handler')]
#[Processor('app.presenter_handler')]
public function testAction(): array { ... }
```

## Custom attributes

Create your own property attribute and implement `ProcessorAttributeInterface`:

```php
use ZJKiza\ResponseProcessor\Contract\ProcessorAttributeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class InjectPresenter implements ProcessorAttributeInterface
{
    public function __construct(
        public string $repository,  // DataFetcherInterface class name
    ) {}
}
```

Register it with `AttributeHandler`:

```yaml
services:
    app.presenter_handler:
        class: ZJKiza\ResponseProcessor\Handler\AttributeHandler
        arguments:
            $attributeClass: 'App\Attribute\InjectPresenter'
        tags:
            - { name: 'zjkiza_response_processor.handler', key: 'app.presenter_handler' }
```

## Architecture

```
Controller
  │  #[Processor('handler_key')]
  ▼
ProcessorListener (kernel.view)
  │
  ├─ AttributeHandler (@handler_key)
  │    └─ ProcessorEngine
  │         ├─ IdentifierExtractor  → reads #[ProcessorIdentifier]
  │         └─ AttributePropertyResolver
  │              └─ FetcherRegistry → resolves DataFetcherInterface by ::class
  │
  └─ (multiple handlers per controller)
```

- `ProcessorEngine` batches fetches per `DataFetcher` class — one `fetch()` call per class per `process()` call
- `$callSequence` prevents redundant reflection lookups within a single `process()` call
- Handler tag keys allow the same PHP class registered under multiple keys

## Service Tags

| Tag | Purpose |
|-----|---------|
| `zjkiza_response_processor.handler` | Registers a handler with a `key` attribute |
| `zjkiza_response_processor.fetcher` | Auto-tagged on all `DataFetcherInterface` implementations |

## Contracts

| Interface | Purpose |
|-----------|---------|
| `ProcessorAttributeInterface` | Marker interface for custom processor attributes |
| `DataFetcherInterface` | Fetches data by array of identifiers |
| `ProcessorHandlerInterface` | Handles processed data in `kernel.view` |
| `IdentifierExtractorInterface` | Extracts identifier from an object |
| `PropertyResolverInterface` | Resolves a `DataFetcherInterface` for a property |
