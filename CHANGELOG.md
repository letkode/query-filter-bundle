# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

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
