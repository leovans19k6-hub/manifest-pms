# ADR-112: Inventory Unit HTTP API

## Status

Accepted

## Context

STORY-008A introduced the Inventory Unit domain core.

STORY-008B introduced the Unit administration application layer, including
commands, actions, validation, authorization, tenant boundaries, audit logging,
query services, and archive behavior.

STORY-008C exposes those capabilities through the versioned JSON HTTP API.

The HTTP layer must remain thin and must not duplicate business mutation logic
already owned by the Inventory application layer.

The API must preserve the existing authentication, organization context,
permission middleware, tenant isolation, validation, audit, and archive
contracts.

## Decision

### HTTP endpoints

The Unit API is exposed under `/api/v1`.

Property-scoped collection endpoints:

- `GET /api/v1/properties/{property}/units`
- `POST /api/v1/properties/{property}/units`

Unit-scoped member endpoints:

- `GET /api/v1/units/{unit}`
- `PUT /api/v1/units/{unit}`
- `PATCH /api/v1/units/{unit}`
- `DELETE /api/v1/units/{unit}`

### Authorization

Routes use the existing HTTP authorization middleware.

The following permissions are required:

- `inventory.units.view`
- `inventory.units.create`
- `inventory.units.update`
- `inventory.units.archive`

Authentication and active organization context are required before permission
evaluation.

The controller resolves the active organization membership from the
authenticated user and `CurrentOrganization`.

### Controller boundary

`UnitController` is an HTTP adapter.

Create, update, and archive mutations are delegated to the existing Inventory
application actions and commands.

The controller does not directly persist Unit models.

Property-scoped operations resolve properties through `PropertyService`.

Unit-scoped operations resolve units through `UnitQueryService`.

### Request validation

Dedicated Form Requests define the accepted HTTP input contract:

- `IndexUnitRequest`
- `StoreUnitRequest`
- `UpdateUnitRequest`

Create requests require the public Unit identity fields and validate enum,
numeric, description, and metadata input.

Update requests reuse the create validation contract while making all fields
optional.

System-owned fields such as identifiers, tenant ownership, timestamps, and
soft-delete state are not accepted by the public request contract.

`IndexUnitRequest` validates the intended query parameter vocabulary for
status, type, search, sort, direction, page size, and page number.

STORY-008C does not extend `UnitQueryService::list()` to implement filtering,
sorting, or pagination. Therefore validated index query parameters are not
claimed as effective query behavior in this story.

### Update semantics

PUT and PATCH use the same update endpoint.

Partial update payloads are merged with the current persisted Unit attributes
before constructing the complete `UnitData` DTO required by the application
layer.

Application-layer validation remains authoritative for Unit invariants.

### Resource contract

`UnitResource` exposes the public Unit representation.

The resource includes:

- identifiers and ownership references;
- code, name, and slug;
- type and status;
- capacity and occupancy values;
- bedroom and bathroom counts;
- sort order;
- description and metadata;
- creation and update timestamps.

Soft-delete internals are not exposed.

### Archive semantics

DELETE delegates to `ArchiveUnitAction`.

Archive behavior follows the existing application contract and soft deletes
the Unit.

The endpoint returns HTTP `204 No Content`.

The HTTP layer does not rewrite archive behavior into a status transition.

### Tenant isolation and error semantics

Property-scoped collection and create operations resolve the property through
the tenant-scoped `PropertyService`.

Foreign properties are therefore not exposed and return HTTP `404`.

Unit-scoped show, update, and archive operations use the existing
`UnitQueryService::find()` application contract.

A foreign Unit is rejected by that service with a validation error and is
currently represented as HTTP `422 Unprocessable Entity`.

STORY-008C preserves this existing application-layer contract rather than
changing error semantics as part of the HTTP delivery story.

### Validation and persistence errors

Standard Laravel JSON validation responses are used for malformed request
input.

Duplicate Unit code and slug persistence violations are mapped by the existing
application service to validation errors and exposed as HTTP `422` JSON
responses.

### Testing

`UnitHttpApiTest` verifies:

- unauthenticated requests return JSON unauthorized responses;
- authorized create, list, show, update, and archive flows;
- audit recording through the existing application layer;
- soft-delete archive behavior;
- public resource representation;
- HTTP request validation;
- system-owned input fields are ignored;
- permission denial;
- cross-tenant property isolation;
- cross-tenant Unit rejection according to the existing application contract;
- index query validation;
- duplicate code and slug validation responses.

The complete regression suite remains green.

## Consequences

### Positive

- Inventory Unit capabilities are available through the versioned JSON API.
- The HTTP layer reuses the existing application layer instead of duplicating
  business mutation logic.
- Existing authorization, tenant isolation, validation, audit, and archive
  contracts remain intact.
- API resources provide an explicit public representation.
- Feature tests protect HTTP behavior and cross-tenant boundaries.

### Trade-offs

- Unit list filtering, sorting, and pagination are not implemented by
  `UnitQueryService::list()` in this story even though the HTTP query
  vocabulary is validated.
- Foreign property and foreign Unit operations currently have different HTTP
  error semantics because the underlying application services expose
  different contracts.
- PUT and PATCH share partial-update behavior rather than enforcing strict
  full-replacement PUT semantics.

## Deferred Work

Future stories may:

- implement Unit query filtering, sorting, and pagination in the application
  query service;
- align tenant-boundary error semantics across Property and Inventory APIs;
- add Inventory Unit administration web UI;
- add richer Unit availability, pricing, reservation, and operational
  contracts.

These concerns are intentionally outside STORY-008C.
