# ADR-110: Inventory Unit Domain Core

## Status

Accepted

## Context

Manifest Stay PMS requires an Inventory bounded context for managing
bookable accommodation units belonging to tenant-owned properties.

A Unit is the foundational aggregate entity for later inventory,
availability, rate, reservation, housekeeping, and operations behavior.

The domain requires database-enforced tenant isolation and public
contracts that remain consistent across MariaDB production environments
and SQLite test environments.

## Decision

Introduce the Inventory bounded context with a Unit domain core.

The implementation includes:

- `Domain\Inventory\Models\Unit`
- `UnitType` enum
- `UnitStatus` enum
- `UnitFactory`
- `units` database table
- `Property::units()` relationship
- Inventory architecture allowlist registration
- Unit domain contract tests

## Tenant Ownership

Every Unit belongs to:

- one Organization
- one Property

The database enforces tenant consistency through the composite foreign key:

`units (organization_id, property_id)`

referencing:

`properties (organization_id, id)`

The `properties` table exposes a composite unique key on:

`(organization_id, id)`

This prevents a Unit from referencing a Property owned by another
Organization.

Tenant consistency is therefore enforced at the database boundary and
does not rely only on application-layer validation.

## Identity and Uniqueness

Units use ULIDs as primary keys.

Unit codes are unique within the tenant and property scope:

`(organization_id, property_id, code)`

Unit slugs are unique within the tenant and property scope:

`(organization_id, property_id, slug)`

The same code or slug may exist in different properties.

## Unit Type Contract

Supported Unit types are:

- `room`
- `villa`
- `house`
- `apartment`
- `bed`
- `other`

The database rejects unsupported Unit type values.

## Unit Status Contract

Supported Unit statuses are:

- `draft`
- `active`
- `inactive`
- `maintenance`
- `archived`

The database rejects unsupported Unit status values.

## Occupancy Contract

A Unit stores:

- adult capacity
- child capacity
- bedrooms
- bathrooms
- base occupancy
- maximum occupancy

The database enforces:

`base_occupancy <= max_occupancy`

The invariant applies to both INSERT and UPDATE operations.

## Numeric Invariants

The database rejects negative values for:

- `capacity_adults`
- `capacity_children`
- `bedrooms`
- `bathrooms`
- `base_occupancy`
- `max_occupancy`
- `sort_order`

## Database Portability

Production uses MariaDB while automated tests use SQLite.

MariaDB enforces Unit invariants through native CHECK constraints.

SQLite enforces equivalent contracts through INSERT and UPDATE triggers.

The behavior is covered by the same domain contract test suite.

## Lifecycle

Units support soft deletion.

Force deleting a Property physically cascades deletion to its Units.

Force deleting an Organization physically cascades deletion through the
foreign-key ownership graph.

Normal soft deletion does not trigger physical database cascades.

## Factory Contract

`UnitFactory` creates Units whose `organization_id` always matches the
Organization owning the referenced Property.

Factory-generated domain objects therefore preserve tenant consistency.

## Testing

`UnitDomainCoreTest` covers:

- public model contract
- enum casts
- metadata casts
- Property relationship
- tenant-scoped code uniqueness
- tenant-scoped slug uniqueness
- cross-tenant Property rejection
- occupancy invariant on INSERT
- occupancy invariant on UPDATE
- negative numeric rejection
- unsupported type rejection
- unsupported status rejection
- soft deletion
- Property force-delete cascade
- Organization force-delete cascade
- factory tenant consistency

The full repository quality gate passes with:

- 151 tests
- 581 assertions
- Laravel Pint passing on 223 files
- `git diff --check` clean

## Consequences

The Inventory bounded context now has a database-enforced Unit foundation.

Later stories may build:

- Unit administration application services
- Unit HTTP APIs
- Unit administration web UI
- availability calendars
- inventory allocation
- pricing and rate plans
- reservation inventory assignment
- housekeeping state
- operational workflows

These later capabilities must preserve the tenant ownership and database
invariants established by this ADR.