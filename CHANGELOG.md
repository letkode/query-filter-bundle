# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.4.0] - 2026-09-03

### Added
- `Exception\RejectionReason`: backed enum (`not_sortable`, `not_filterable`, `unknown_operator`, `malformed_filter`) identifying why a query parameter was rejected; the value doubles as the `query_filter.<value>` translation key
- `Exception\QueryParameterRejection`: readonly DTO `{parameter, reason, value}` describing a single rejected parameter, keyed by dot-notation path (`sort`, `filters.etapa`)
- `Exception\UndeclaredQueryParameterException`: HTTP-agnostic exception carrying a `non-empty-list<QueryParameterRejection>` so a caller can report every rejection at once and translate it to a response itself
- `Filter\ParsedFilterQuery`: readonly result of `FilterQuery::fromArray()`, exposing `criteria` (well-formed `FilterCriteria`) and `rejected` (malformed entries)
- `translations/query_filter.{en,es}.yaml`: default message catalog for the four rejection reasons, auto-registered by the bundle and overridable per app

### Changed
- `Filter\FilterQuery::fromArray()` now returns `Filter\ParsedFilterQuery` instead of `list<FilterCriteria>`. Malformed filter input (value not an array, entry not an array, missing/non-string `op`) is now collected as a `QueryParameterRejection` instead of being dropped silently
- `Request\FilterQueryRequest` gains a `rejected` constructor argument / property (`list<QueryParameterRejection>`, last, defaults to `[]`), populated by `fromArray()` from the parsed malformed entries
- `Factory\FilterQueryRequestFactory::build()` forwards the parsed rejections onto the `FilterQueryRequest`

### BC breaks
- `FilterQuery::fromArray()` return type changed from `list<FilterCriteria>` to `ParsedFilterQuery`; callers must read `->criteria`. Kept at a minor version because every downstream consumer is still in development.
- `FilterQueryRequest` construction is unaffected: the new `rejected` argument is last and optional.

---

## [1.3.0] - 2026-08-18

### Added
- `Response\PaginationValueResponse`: pagination metadata DTO (`total`, `perPage`, `totalPages`, `page`), for API responses that expose pagination without leaking the paginated data itself

### Changed
- `Result\PaginatedResult` renamed to `Result\PaginatedResultRepository` — the name now makes clear this is the repository-layer result (data + pagination metadata), distinct from `Response\PaginationValueResponse`

### BC breaks
- Code referencing `Result\PaginatedResult` must be updated to `Result\PaginatedResultRepository`

---

## [1.2.1] - 2026-08-10

### Fixed
- `composer.json` `type` corrected from `library` to `symfony-bundle`
- `composer.json` now declares `symfony/dependency-injection` and `symfony/http-kernel` in `require` — `LetkodeQueryFilterBundle` extends `AbstractBundle` from `symfony/http-kernel` and was never actually installable standalone without them
- `phpstan.neon` added (was required as a dev dependency but never configured); package is now phpstan level 9 clean
- `FilterInput::castValues()` return type corrected from `mixed` to `list<mixed>`, matching what it actually returns
- `Result\PaginatedResult` is now generic (`@template T`, `list<T> $data`)
- `Filter\FilterQuery::fromArray()` no longer passes `'strval'` (a plain string) as an `array_map()` callback without narrowing scalar values first
- `Request\FilterQueryRequest::fromArray()` no longer casts unvalidated `mixed` query params directly to `string`/`int`

No behavior changes for well-formed input; all fixes are dependency and type-safety only. 53 tests unchanged and passing.

---

## [1.2.0] - 2026-07-29

### Added
- `Filter\FilterCastType`: backed enum (`Text`, `Bool`, `Int`, `Float`, `Number`, `Date`, `ArrayType`) that owns the value-casting rule for each filter type via `cast(string $value): mixed`

### Changed
- `FilterInput::$type` is now a `FilterCastType` enum instead of a raw string; `castValue()`/`castValues()` now delegate to `$type->cast()`. This fixes a single-responsibility violation where `FilterInput` mixed field-descriptor data with casting logic duplicated as string tags
- `Filter\QueryFilter` renamed to `Filter\FilterQuery`
- `Request\QueryFilterRequest` renamed to `Request\FilterQueryRequest`
- `Request\QueryFilterStringRequest` renamed to `Request\FilterQueryStringRequest`
- `Factory\QueryFilterRequestFactory` renamed to `Factory\FilterQueryRequestFactory`

### BC breaks
- Code comparing `FilterInput::$type` against a string (e.g. `'text' === $field->type`) must compare against `FilterCastType::Text` instead
- All `Query*` class names above must be updated to their `Filter*`-prefixed equivalents

---

## [1.1.0] - 2026-07-29

### Changed
- `Filter\QueryFilterRequest` renamed to `Filter\QueryFilter`
- `Request\QueryRequest` renamed to `Request\QueryFilterRequest`
- `Request\QueryStringRequest` renamed to `Request\QueryFilterStringRequest`
- `Request\QueryRequestFactory` renamed to `Factory\QueryFilterRequestFactory` and moved to the new `Factory/` namespace

---

## [1.0.0] - 2026-07-28

### Added
- Initial release, extracted from `letkode/entity-traits-bundle`
- `Filter/`: `FilterCriteria`, `FilterInput`, `QueryFilterRequest` (renamed from `TableQueryFilterRequest`)
- `Request/`: `QueryStringRequest` (renamed from `TableQueryStringRequest`), `QueryRequest` (renamed from `TableQueryRequest`), `QueryRequestFactory` (renamed from `TableQueryRequestFactory`)
- `Result/`: `PaginatedResult`
- Symfony bundle integration via `LetkodeQueryFilterBundle` extending `AbstractBundle`
- Auto-discovery support via `extra.symfony.bundles` in Composer
