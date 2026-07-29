# letkode/query-filter-bundle

Query, filter and pagination DTOs for Symfony applications. Framework-agnostic beyond `symfony/validator` — usable in table listings, dashboards or any other query/filter surface.

---

## Installation

```bash
composer require letkode/query-filter-bundle
```

Symfony Flex will register the bundle automatically. If not using Flex, add it manually:

```php
// config/bundles.php
return [
    Letkode\QueryFilterBundle\LetkodeQueryFilterBundle::class => ['all' => true],
];
```

---

## `Filter/`

### `FilterCriteria`

A single resolved filter condition.

```php
use Letkode\QueryFilterBundle\Filter\FilterCriteria;

$criteria = new FilterCriteria(field: 'firstName', operator: 'is', values: ['Ana']);
```

### `FilterInput`

Declares how a filterable field should be typed and cast.

```php
use Letkode\QueryFilterBundle\Filter\FilterInput;

$input = FilterInput::text();          // no casting
$input = FilterInput::bool();          // 'true'/'false' -> bool
$input = FilterInput::int();
$input = FilterInput::float();
$input = FilterInput::number();        // float, for numeric comparisons
$input = FilterInput::date();          // string -> DateTimeImmutable
$input = FilterInput::array();

$input->castValue('42.5');             // typed value
$input->castValues(['1', '2']);        // list<mixed>
```

`$input->type` is a `FilterCastType` enum (`Text`, `Bool`, `Int`, `Float`, `Number`, `Date`, `ArrayType`), which owns the casting rule via `$input->type->cast($value)`.

### `FilterQuery`

Parses raw filter arrays (as sent by a frontend) into `list<FilterCriteria>`.

```php
use Letkode\QueryFilterBundle\Filter\FilterQuery;

$filters = FilterQuery::fromArray([
    'firstName' => [['op' => 'is', 'value' => ['Ana']]],
]);
```

---

## `Request/`

### `FilterQueryStringRequest`

Validated DTO to bind directly from a query string.

```php
use Letkode\QueryFilterBundle\Request\FilterQueryStringRequest;

$request = new FilterQueryStringRequest(page: 1, perPage: 20, q: 'search', sort: 'name', dir: 'asc');
```

### `FilterQueryRequest`

Normalized query: page, perPage, search term, sort, direction and parsed filters.

```php
use Letkode\QueryFilterBundle\Request\FilterQueryRequest;

$query = FilterQueryRequest::fromArray($request->query->all());
// $query->page, $query->perPage, $query->q, $query->sort, $query->dir, $query->filters
```

---

## `Factory/`

### `FilterQueryRequestFactory`

Builds a `FilterQueryRequest` from a validated `FilterQueryStringRequest`.

```php
use Letkode\QueryFilterBundle\Factory\FilterQueryRequestFactory;

$query = FilterQueryRequestFactory::build($queryStringRequest);
```

---

## `Result/`

### `PaginatedResult`

```php
use Letkode\QueryFilterBundle\Result\PaginatedResult;

$result = new PaginatedResult(data: $items, total: 120, page: 1, perPage: 20);
$result->totalPages; // computed, e.g. 6
```

---

## Requirements

- PHP `^8.4`
- Symfony `^7.0 || ^8.0` (`symfony/validator`)

---

## License

MIT — see [LICENSE](LICENSE).
