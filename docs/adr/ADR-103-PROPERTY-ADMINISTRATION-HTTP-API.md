# ADR-103: Property Administration HTTP API
## Status
Accepted
## Decision
Expose Property administration through versioned JSON endpoints under `/api/v1/properties`. HTTP controllers remain thin adapters over STORY-102 application actions and query services. Form Requests define create/update/filter contracts, API Resources define serialization, and route middleware enforces authentication, active organization context, and per-operation permissions.
All property lookup and mutation remains tenant-scoped through PropertyService/PropertyQueryService. Client-provided tenant/system fields are excluded by request validation and application validation boundaries. API requests receive JSON authentication, authorization, validation, not-found, and domain exception responses through Laravel's exception rendering.
## Endpoints
GET collection, GET item, POST create, PUT/PATCH update, DELETE archive. Delete is soft archive and returns 204.
## Security
No route-model binding is used for tenant-owned Property records; IDs are resolved through tenant-scoped services. Permissions are explicit per route. Cross-tenant identifiers return 404.
## Scope
No UI, token authentication, public API, bulk operations, restore endpoint, or permanent deletion is introduced.
## Verification
Feature tests cover unauthenticated JSON errors, permission denial, validation/filter contracts, pagination, CRUD happy path, tenant isolation, audit integration, ignored system fields, and archive semantics.
