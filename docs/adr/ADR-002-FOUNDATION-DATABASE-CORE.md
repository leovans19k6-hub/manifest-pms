# ADR-002: Foundation Database Core

## Status
Accepted

## Decision
Manifest Stay PMS uses ULID primary keys for foundation aggregate records. The first verified database core contains users, organizations, and organization memberships.

Membership is modeled explicitly through `organization_users` so lifecycle state, default-organization selection, and join timestamps can evolve without overloading the user or organization aggregate.

## Constraints
- No RBAC, audit, authentication flow, CRUD UI, or property domain is implemented in STORY-002.
- Organization and membership statuses are PHP backed enums while database columns remain strings for portable migrations.
- Foreign keys cascade on membership deletion when a parent user or organization is deleted.
- Organization deletion uses soft deletes.

## Verification
Database tests must prove ULID generation, relationships, and the unique organization/user membership constraint.
