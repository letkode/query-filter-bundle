# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

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
