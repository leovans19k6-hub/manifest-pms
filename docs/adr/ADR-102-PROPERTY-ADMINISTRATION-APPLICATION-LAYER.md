# ADR-102: Property Administration Application Layer

## Status
Accepted

## Context
STORY-101 established the Property aggregate, tenant ownership, RBAC permissions, audit hooks, and domain service boundaries. Administrative use cases now need explicit input contracts, centralized validation, query capabilities, and application actions without exposing HTTP CRUD or UI.

## Decision
Introduce immutable command DTOs for create, update, and archive operations; a readonly `PropertyData` DTO containing only administrator-editable fields; centralized `PropertyValidator`; application actions that validate and delegate mutations to the existing tenant-safe, permission-aware, transactional `PropertyService`; and `PropertyQueryService` for tenant-scoped filtering, search, allowlisted sorting, and bounded pagination.

System-controlled fields such as `id`, `organization_id`, timestamps, and deletion state are never copied from application input into `PropertyData`. Tenant ownership is derived exclusively from `CurrentOrganization`.

Mutation audit records remain inside the same database transaction as property writes. An audit failure therefore rolls back the property mutation. Existing RBAC permissions from ADR-101 remain the authorization source.

## Query Contract
Supported filters are `status`, `type`, and `search` over property name/code. Sort fields are allowlisted to `code`, `name`, `type`, `status`, `created_at`, and `updated_at`. Direction is limited to `asc`/`desc`; page size is bounded to 1–100. Every query is constrained to the current organization and requires `property.properties.view`.

## Consequences
Application use cases are explicit and independently testable. Validation and mass-assignment boundaries are centralized. HTTP controllers and UI remain out of scope.

## Verification
Tests cover immutable application commands, centralized validation, system-field rejection-by-omission, create/update/archive actions, permission denial, tenant isolation, filtering/search/sort/pagination, invalid query parameters, audit records, and transaction rollback when auditing fails.
