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

Parses raw filter arrays (as sent by a frontend) into a `ParsedFilterQuery`:
`->criteria` holds the well-formed `list<FilterCriteria>`, `->rejected` holds a
`list<QueryParameterRejection>` for entries that were structurally malformed
(value not an array, entry not an array, missing/non-string `op`).

```php
use Letkode\QueryFilterBundle\Filter\FilterQuery;

$parsed = FilterQuery::fromArray([
    'firstName' => [['op' => 'is', 'value' => ['Ana']]],
]);
// $parsed->criteria, $parsed->rejected
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
// $query->rejected — list<QueryParameterRejection> for malformed filter entries
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

### `PaginatedResultRepository`

Returned by the repository layer: the paginated data plus its pagination metadata.

```php
use Letkode\QueryFilterBundle\Result\PaginatedResultRepository;

$result = new PaginatedResultRepository(data: $items, total: 120, page: 1, perPage: 20);
$result->totalPages; // computed, e.g. 6
```

---

## `Response/`

### `PaginationValueResponse`

Pagination metadata only, without the data — for API responses that expose pagination separately from the result set.

```php
use Letkode\QueryFilterBundle\Response\PaginationValueResponse;

$pagination = new PaginationValueResponse(
    total: $result->total,
    perPage: $result->perPage,
    totalPages: $result->totalPages,
    page: $result->page,
);
```

---

## `Exception/`

### `UndeclaredQueryParameterException`

Thrown when a query carries parameters the consumer did not declare (unknown sort/filter field, unsupported operator) or that are malformed. It carries every rejection at once (`->rejections`: a list of `QueryParameterRejection` with `parameter`, `reason` and `value`).

It is an HTTP status exception of [`letkode/http-exception-bundle`](https://github.com/letkode/http-exception-bundle) (it extends `AbstractHttpStatusException`), so that bundle's `ExceptionListener` answers it with:

```json
{
  "success": false,
  "message": "The query contains undeclared or invalid parameters.",
  "status": 422,
  "errorCode": "INVALID_QUERY_PARAMETERS",
  "errors": {
    "sort": ["Sorting by \"secret\" is not allowed."],
    "filters.etapa": ["Filtering by \"etapa\" is not allowed."]
  }
}
```

- `errors` is keyed by the rejected `parameter` (dot notation: `sort`, `filters.etapa`); a parameter rejected more than once has several messages.
- Messages and the top-level `message` come from the `query_filter` translation domain (`query_filter.<reason>`; English and Spanish are included, override any key in your app's `translations/query_filter.<locale>.yaml`). Each message is a `RejectionMessage`, a Symfony `TranslatableInterface`.
- The class is not `final`: extend it and override `defaultErrorCode()` to change the code.
- If your application renders exceptions with its own listener instead, read `->rejections` and build the response yourself, or handle `HttpStatusExceptionInterface` with its `ErrorsOption`.

---

## Requirements

- PHP `^8.4`
- `letkode/http-exception-bundle` `^1.2`
- Symfony `^7.0 || ^8.0` (`config`, `dependency-injection`, `http-kernel`, `validator`, `yaml`, `translation-contracts`)

---

## License

MIT — see [LICENSE](LICENSE).
