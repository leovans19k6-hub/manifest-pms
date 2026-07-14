# ADR-111: Inventory Unit Administration Application Layer

## Status

Accepted

## Context

ADR-110 introduced the Inventory Unit domain core, including the Unit aggregate model, unit type and status enums, tenant-safe relational constraints, occupancy invariants, soft deletion, factories, and domain-level persistence tests.

The next architectural step is to provide an application layer for administering inventory units without introducing HTTP controllers, API resources, Form Requests, or web user interfaces.

Unit administration must preserve the architectural contracts established by the Foundation and Property modules:

- all mutations require explicit permissions;
- all operations are scoped to the active organization context;
- cross-tenant properties and units must be rejected;
- application input must be represented by explicit DTOs and commands;
- validation must occur before mutation;
- database mutations and audit logging must be atomic;
- duplicate unit codes and slugs must be reported through validation contracts;
- queries must never expose units belonging to another organization;
- the system-level SUPER_ADMIN role must remain synchronized with newly introduced permissions.

## Decision

Implement the Inventory Unit Administration Application Layer.

### Application DTO

Introduce `UnitData` as an immutable application DTO.

The DTO contains the writable public contract for a Unit:

- code;
- name;
- slug;
- type;
- status;
- adult and child capacities;
- bedroom and bathroom counts;
- base and maximum occupancy;
- sort order;
- description;
- metadata.

The DTO converts enum values to their persisted scalar representations before passing data to the domain service.

### Commands

Introduce explicit command objects:

- `CreateUnitCommand`;
- `UpdateUnitCommand`;
- `ArchiveUnitCommand`.

Commands carry the authenticated organization membership, target aggregate or property, and application input required by each use case.

### Actions

Introduce application actions:

- `CreateUnitAction`;
- `UpdateUnitAction`;
- `ArchiveUnitAction`.

Create and update actions validate input before delegating mutations to `UnitService`.

Archive delegates directly to the service because it does not accept writable unit input.

### Validation

Introduce `UnitValidator`.

Validation enforces:

- required public fields;
- string length contracts;
- tenant-and-property-scoped unique unit codes;
- tenant-and-property-scoped unique unit slugs;
- valid `UnitType` values;
- valid `UnitStatus` values;
- non-negative numeric values;
- base occupancy not exceeding maximum occupancy;
- nullable description;
- nullable array metadata.

Update validation ignores the current unit when evaluating scoped uniqueness.

### Mutation Service

Introduce `UnitService`.

The service is responsible for:

- permission enforcement;
- active organization context enforcement;
- membership tenant validation;
- property tenant validation;
- unit tenant validation;
- create mutations;
- update mutations;
- soft-delete archive mutations;
- transactional audit logging.

The following permissions are required:

- `inventory.units.create`;
- `inventory.units.update`;
- `inventory.units.archive`.

Mutation and audit logging execute inside the same database transaction.

An audit failure therefore rolls back the associated database mutation.

The following audit events are recorded:

- `inventory.unit.created`;
- `inventory.unit.updated`;
- `inventory.unit.archived`.

### Query Service

Introduce `UnitQueryService`.

The query service requires:

- `inventory.units.view`.

Unit lists are restricted by:

- active organization;
- target property.

List ordering is deterministic using unit name followed by unit ID.

Individual unit lookup rejects missing or foreign-tenant units through the application validation contract.

### Inventory Permissions

Introduce an idempotent inventory unit permission migration defining:

- `inventory.units.view`;
- `inventory.units.create`;
- `inventory.units.update`;
- `inventory.units.archive`.

Permission creation preserves existing permission primary keys when the migration is executed against pre-existing permission records.

Permission metadata is updated deterministically.

### SUPER_ADMIN Synchronization

Update `RbacSeeder` so the system-level `SUPER_ADMIN` role synchronizes with all currently persisted permissions.

This ensures permissions introduced after the original Foundation RBAC seed remain available to the system administrator when the RBAC seeder runs again.

The existing RBAC seeder regression test verifies that:

- the seeder remains idempotent;
- the system SUPER_ADMIN role exists;
- the SUPER_ADMIN permission count matches the persisted permission count.

## Tenant Safety

Every mutation requires the membership organization to match the active organization context.

Create operations additionally require the target Property to belong to the active organization.

Update and archive operations require the target Unit to belong to the active organization.

Queries explicitly scope Unit retrieval to the active organization and property where applicable.

Foreign tenant aggregates are rejected rather than returned or mutated.

## Transaction and Audit Contract

Create, update, and archive mutations execute inside database transactions.

Audit logging is part of the transaction boundary.

If audit persistence fails, the associated Unit mutation is rolled back.

This preserves consistency between business state and the audit trail.

## Testing

`UnitAdministrationApplicationTest` verifies:

- authorized unit creation and audit logging;
- create permission enforcement;
- foreign property rejection;
- duplicate code validation;
- duplicate slug validation;
- numeric and occupancy validation;
- authorized unit update and audit logging;
- foreign unit update rejection;
- authorized unit archive and audit logging;
- tenant-safe unit listing;
- query permission enforcement;
- foreign unit lookup rejection.

`UnitDomainCoreTest` continues to verify Unit persistence and database invariants.

`RbacSeederTest` verifies SUPER_ADMIN synchronization and seeder idempotency.

The complete application test suite passes with 163 tests and 598 assertions.

Laravel Pint passes all 235 checked files.

`git diff --check` reports no whitespace errors.

## Consequences

### Positive

- Inventory Unit administration now has an explicit application boundary.
- Controllers and future delivery mechanisms can depend on stable commands and actions.
- Writable input is isolated from persistence models.
- Authorization and tenant safety are centralized.
- Unit mutations and audit logs remain transactionally consistent.
- Query operations cannot expose cross-tenant Unit records.
- Inventory permissions are explicitly persisted.
- SUPER_ADMIN remains synchronized as the permission catalog grows.
- Future HTTP API and Web UI stories can build on tested application contracts.

### Negative

- Validation currently depends on Laravel validation infrastructure.
- UnitData currently represents the complete writable Unit contract for both create and update use cases.
- Archive behavior uses soft deletion and does not yet model restoration workflows.
- Query filtering, pagination, and advanced sorting are intentionally deferred.
- HTTP request validation and transport-specific error contracts are not included.

## Deferred Work

The following concerns are intentionally deferred:

- Inventory Unit HTTP API;
- Inventory Unit API resources;
- Inventory Unit Form Requests;
- Inventory Unit administration Web UI;
- pagination and advanced query filtering;
- unit media and documents;
- unit amenities;
- unit rate plans;
- unit availability and inventory calendars;
- reservation allocation;
- archive restoration workflows.

## Verification

The implementation is accepted when:

- inventory unit application tests pass;
- inventory unit domain core tests pass;
- RBAC seeder regression tests pass;
- the complete Laravel test suite passes;
- Laravel Pint passes;
- `git diff --check` reports no whitespace errors.