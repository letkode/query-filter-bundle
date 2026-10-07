# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2026-10-07

### Changed
- **Breaking:** `FilterInput` no longer takes a `path`. Every factory now takes `alias`, `property`, `propertyCase` and `expression` (in that order): `alias` names the query alias that owns the field (the consumer's root alias when omitted) and `property` the field's name (the key converted to the property case when omitted). A former `path: 'rp.uuid'` becomes `alias: 'rp', property: 'uuid'`; `path: 'c.legalName'` on a key `legal_name` becomes `alias: 'c'`. A filter on something other than a column (e.g. `CONCAT(u.firstName, ' ', u.lastName)`) is declared with `expression`: raw DQL used as-is, never built from request input, and it cannot be combined with `alias`, `property` or `propertyCase` (`InvalidArgumentException`).
- **Breaking:** `FilterInput::$path` and `FilterInput::resolvePath()` are replaced by `FilterInput::$alias`, `FilterInput::$property` and `FilterInput::resolveProperty()`, which returns the property only; qualifying it with an alias is the consumer's job.

---

## [1.7.0] - 2026-10-07

### Added
- `Filter\PropertyCase` (`None`, `Camel`, `Snake`): converts a filter key to the spelling of the field it targets. Moved here from `letkode/orm-toolkit-bundle`, where it shipped in 2.3.0–2.4.1.
- `property_case` bundle option (`letkode_query_filter.property_case`, default `none`): the global property case, published to `Filter\PropertyCaseRegistry` on boot.
- `FilterInput::resolvePath(string $key)`: the explicit `path` when present (never converted), otherwise the key converted to the input's `propertyCase` or, failing that, the global one.
- Every `FilterInput` factory takes an optional `propertyCase` argument that overrides the global setting for that input only.
- `resources/config/letkode_query_filter.yaml.dist`, published with `bin/console letkode:config:publish query-filter`.
- `symfony/string` and `letkode/config-publisher-bundle` are now required.

---

## [1.6.1] - 2026-09-30

### Fixed
- `LetkodeQueryFilterBundle::loadExtension()` still imported `config/services.yaml`, which 1.6.0 had removed, so loading the bundle failed with `The file ".../config/services.yaml" does not exist`. 1.6.0 must not be used; upgrade to 1.6.1.
- Added tests that load the extension into a container and compile it, so a missing import cannot ship again.

---

## [1.6.0] - 2026-09-29

### Changed
- `Exception\UndeclaredQueryParameterException` is now an HTTP status exception: it extends `AbstractHttpStatusException` from `letkode/http-exception-bundle`, answers with status `422` and the error code `INVALID_QUERY_PARAMETERS`, exposes the rejections as an `ErrorsOption` keyed by parameter and translates its message in the `query_filter` domain. The constructor (`non-empty-list<QueryParameterRejection>`) and `->rejections` are unchanged, so existing `catch` blocks keep working. The class is no longer `final`.
- `letkode/http-exception-bundle` `^1.2` is now required.

### Added
- `Exception\RejectionMessage`: a Symfony `TranslatableInterface` that translates `query_filter.<reason>` with the rejected value as `%value%`.
- `query_filter.{en,es}.yaml` translate the exception message.

### Removed
- `EventListener\UndeclaredQueryParameterListener` and `config/services.yaml`, introduced in 1.5.0: the exception is now rendered directly by the `ExceptionListener` of `letkode/http-exception-bundle`, so no conversion is needed.

---

## [1.5.0] - 2026-09-29

### Added
- `EventListener\UndeclaredQueryParameterListener`: a `kernel.exception` listener (priority `10`, always active) that converts `UndeclaredQueryParameterException` into an `UnprocessableEntityHttpException` (422) wrapping a `ValidationFailedException`, with one violation per `QueryParameterRejection` (property path = `parameter`, message = translated `query_filter.<reason>`). Any exception listener that already renders that shape, such as the one in `letkode/http-exception-bundle`, now answers 422 instead of 500 for an undeclared or malformed query parameter. The exception itself stays HTTP-agnostic.
- `config/services.yaml`, imported by the bundle to register the listener.

### Changed
- `symfony/config`, `symfony/yaml` and `symfony/translation-contracts` are now required, since the bundle loads its service definitions and translations and uses the translator.

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
