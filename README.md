# ResponseProcessor Bundle

A Symfony bundle for automatically fetches and injects related data into DTOs after a controller returns, using PHP 8 attributes.

## How it works

1. Controller returns DTOs with `#[ProcessorIdentifier]` on the ID property
2. DTO properties are annotated with `#[InjectData(SomeFetcher::class)]` or a custom attribute
3. `ProcessorListener` intercepts `kernel.view`, reads `#[Processor('handler_key')]` from the controller method
4. The registered handler resolves which properties need data, batches fetches by fetcher class (O(1) per class), and injects results

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

```php
use ZJKiza\ResponseProcessor\Attribute\ProcessorIdentifier;
use ZJKiza\ResponseProcessor\Attribute\InjectData;

class TagDto
{
    public ?object $tag = null;

    public function __construct(
        #[ProcessorIdentifier]
        public string $id,
        public string $name,
    ) {}

    #[InjectData(TagFetcher::class)]
    public function setTag(mixed $tag): void
    {
        $this->tag = $tag;
    }
}
```

### 2. Create a DataFetcher

```php
use ZJKiza\ResponseProcessor\Contract\DataFetcherInterface;

class TagFetcher implements DataFetcherInterface
{
    /** @param string[] $identifiers */
    public function fetch(array $identifiers): array
    {
        // return ['id1' => ..., 'id2' => ...]
    }
}
```

`DataFetcherInterface` implementations are auto-tagged with `zjkiza_response_processor.fetcher` via `_instanceof`.

### 3. Register handlers in your app config

```yaml
# config/services.yaml
services:
    app.tag_handler:
        class: ZJKiza\ResponseProcessor\Handler\AttributeHandler
        arguments:
            $attributeClass: 'ZJKiza\ResponseProcessor\Attribute\InjectData'
        tags:
            - { name: 'zjkiza_response_processor.handler', key: 'app.tag_handler' }

    app.presenter_handler:
        class: ZJKiza\ResponseProcessor\Handler\AttributeHandler
        arguments:
            $attributeClass: 'App\Attribute\InjectPresenter'
        tags:
            - { name: 'zjkiza_response_processor.handler', key: 'app.presenter_handler' }
```

### 4. Use on controller

```php
use ZJKiza\ResponseProcessor\Attribute\Processor;

class TagController
{
    #[Processor('app.tag_handler')]
    public function __invoke(): array
    {
        return [new TagDto('1', 'Test')];
    }
}
```

## Custom attributes

Create your own attribute and implement `ProcessorAttributeInterface` to mark it as a processor attribute:

```php
use Attribute;
use ZJKiza\ResponseProcessor\Contract\ProcessorAttributeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InjectPresenter implements ProcessorAttributeInterface
{
    public function __construct(
        public string $repository,
    ) {}
}
```

Register it with `AttributeHandler` as shown above.

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
  │              └─ FetcherRegistry → resolves DataFetcherInterface by class
  │
  └─ (multiple handlers per controller)
```

- `ProcessorEngine` batches fetches per `DataFetcher` class (one `fetch()` call per class per `process()` call)
- `$callSequence` cache prevents redundant reflection lookups within a single `process()` call
- Tag keys allow same PHP class registered under multiple keys

## Tags

| Tag | Purpose |
|---|---|
| `zjkiza_response_processor.handler` | Registers a handler with a `key` attribute |
| `zjkiza_response_processor.fetcher` | Auto-tagged on all `DataFetcherInterface` implementations |
