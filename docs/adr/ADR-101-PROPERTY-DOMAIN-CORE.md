# ADR-101: Property Domain Core

## Status
Accepted

## Context
Sprint 1 begins the business domain layer. Properties are tenant-owned aggregate roots that will later anchor inventory, reservations, operations, and reporting.

## Decision
Create a dedicated `Domain\Property` module. `properties` use ULID primary keys, belong to exactly one organization, use tenant-local unique codes and slugs, enum-backed type/status values, soft deletion, timezone/currency defaults, and extensible JSON metadata.

`PropertyService` is the application boundary for listing, finding, creating, updating, and archiving properties. Every query is constrained by `CurrentOrganization`. Mutations require the current active membership to hold the corresponding RBAC permission through the tenant-safe `AuthorizationService`.

Property mutations emit immutable audit records through `AuditLogger` inside the same database transaction. STORY-101 does not expose Property UI or HTTP CRUD endpoints.

## Permissions
- `property.properties.view`
- `property.properties.create`
- `property.properties.update`
- `property.properties.archive`

The system Super Admin role receives these permissions through the idempotent RBAC seeder. Organization roles may receive them explicitly.

## Consequences
Property domain code is isolated from Foundation except for organization ownership, authorization, current tenant context, and auditing. Cross-tenant reads and writes are rejected by service boundaries and covered by tests.

## Verification
Tests cover schema/model behavior, tenant-local uniqueness, relationships, authorized create/update/archive flows, audit hooks, tenant-isolated list/find/update behavior, permission denial, and idempotent minimal seeding.
