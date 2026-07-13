# ADR-107B: Property Media HTTP API

## Status

Accepted

## Context

The Property domain already provides the media and document foundation,
storage abstraction, tenant-safe persistence, application actions,
validation services, audit logging, private download generation, asset
ordering, document lifecycle administration, and RBAC permissions.

Story 007B exposes those capabilities through the versioned HTTP API.

The HTTP layer must preserve the existing architectural boundaries:

- organization context is resolved before authorization;
- current membership is request scoped;
- route middleware enforces coarse-grained permissions;
- application services and actions enforce tenant ownership and authorization;
- HTTP controllers translate validated requests into application commands;
- storage internals are never exposed through public API resources;
- private files are accessed only through temporary download URLs;
- validation failures use Laravel's JSON validation contract;
- authentication and authorization failures use the standard HTTP foundation
  contracts introduced by ADR-107A.

## Decision

### API Surface

Property asset endpoints:

- `GET /api/v1/properties/{property}/assets`
- `POST /api/v1/properties/{property}/assets`
- `POST /api/v1/properties/{property}/assets/reorder`
- `PATCH /api/v1/property-assets/{asset}`
- `DELETE /api/v1/property-assets/{asset}`
- `POST /api/v1/property-assets/{asset}/download`

Property document endpoints:

- `GET /api/v1/properties/{property}/documents`
- `POST /api/v1/properties/{property}/documents`
- `PATCH /api/v1/property-documents/{document}`
- `PATCH /api/v1/property-documents/{document}/lifecycle`
- `DELETE /api/v1/property-documents/{document}`
- `POST /api/v1/property-documents/{document}/download`

### Authentication and Organization Context

All Property Media API routes are placed inside the existing:

`auth` → `organization`

middleware boundary.

Unauthenticated API requests return the standard JSON `401` response.

Authenticated users without a valid active organization context are rejected
by the authorization HTTP foundation.

Request-scoped organization and membership state must not leak between
requests.

### Authorization

Routes use explicit RBAC permissions.

Asset permissions:

- `property.media.view`
- `property.media.create`
- `property.media.update`
- `property.media.delete`

Document permissions:

- `property.documents.view`
- `property.documents.create`
- `property.documents.update`
- `property.documents.delete`

Route middleware provides the HTTP authorization boundary.

Application actions and services remain responsible for authorization and
tenant ownership checks so that domain operations remain safe when invoked
outside HTTP controllers.

### Tenant Isolation

Property, asset, and document access is constrained to the current
organization.

Foreign properties, assets, and documents are never exposed through the API.

Cross-tenant resource access returns `404` after the request has passed the
required authentication and permission boundary.

Tenant ownership is derived from the current organization context and is not
accepted from client input.

### Request Contracts

Dedicated Form Requests validate:

- asset listing filters;
- document listing filters;
- asset uploads;
- document uploads;
- media metadata updates;
- asset ordering;
- document lifecycle transitions.

Upload requests accept multipart files and validated classifiers and metadata.

Controllers convert uploaded files into `UploadFileData` before invoking
application actions.

The server-derived MIME type is used by the application media validator.

### Response Contracts

Dedicated API resources expose stable public representations for:

- property assets;
- property documents;
- private downloads.

Asset and document resources do not expose:

- storage keys;
- storage disks;
- other storage implementation details.

Private download resources expose only:

- temporary URL;
- expiry timestamp.

Collection endpoints use Laravel paginator resources and expose:

- `data`;
- `links`;
- `meta`.

### Asset Operations

The Asset HTTP API supports:

- upload;
- filtered and paginated listing;
- metadata update;
- ordering;
- temporary private download creation;
- deletion.

Asset ordering is validated as a complete tenant-safe set of existing assets
for the selected property.

### Document Operations

The Document HTTP API supports:

- upload;
- filtered and paginated listing;
- metadata update;
- lifecycle transition;
- temporary private download creation;
- deletion.

Newly uploaded documents are persisted with the `active` lifecycle status.

This invariant is established by `PropertyMediaService` when the created model
is `PropertyDocument`.

Document lifecycle transitions continue to be handled by the existing
application action and administration service.

### Shared Application Corrections

Story 007B includes two small corrections required by the HTTP integration.

`PropertyMediaService` now explicitly initializes newly uploaded documents
with `PropertyDocumentLifecycle::Active`.

`UpdatePropertyDocumentMetadataAction` uses the document metadata DTO contract
expected by the existing administration service.

These changes preserve the existing application boundary and are covered by
the Property Media regression suite.

### Storage and Private Downloads

Uploads continue to use the `PropertyStorage` abstraction.

Controllers do not interact directly with Laravel storage disks.

Private downloads are generated through the existing application actions and
`PropertyPrivateDownloadService`.

Temporary URLs use the configured download TTL.

Deletion continues to preserve the existing compensation behavior when
database persistence fails after storage deletion.

### Audit Logging

The HTTP API reuses existing application-layer audit events.

Covered events include:

- `property.media.created`;
- `property.media.metadata.updated`;
- `property.assets.reordered`;
- `property.document.lifecycle.changed`;
- `property.media.deleted`.

Controllers do not write audit logs directly.

### Testing

Story 007B adds coverage for:

- Form Request contracts;
- API Resource contracts;
- asset HTTP integration;
- document HTTP integration;
- unauthenticated requests;
- missing permissions;
- tenant isolation;
- upload validation;
- filtering and pagination;
- metadata updates;
- asset ordering;
- document lifecycle transitions;
- private downloads;
- deletion;
- audit events;
- regression coverage for the existing Property Media foundation and
  administration application layer.

At final implementation audit, the full test suite passes with:

- 118 tests;
- 430 assertions.

## Consequences

### Positive

- Property media and documents are now available through a versioned API.
- HTTP controllers remain thin adapters over application actions and services.
- Tenant isolation is enforced at both HTTP and application boundaries.
- Storage implementation details remain private.
- Asset and document APIs use explicit request and response contracts.
- Existing audit and private download infrastructure is reused.
- HTTP behavior is covered by integration tests.

### Negative

- `PropertyMediaService::store()` remains a shared generic creation path for
  assets and documents and requires a model-specific document lifecycle
  invariant.
- Media and document HTTP controllers contain similar command-construction
  patterns.
- The API currently exposes private download creation through `POST` endpoints
  rather than direct streaming responses.

## Follow-up

Future stories may introduce:

- property media administration web UI;
- document lifecycle policy expansion;
- richer asset ordering rules;
- image transformation and thumbnail pipelines;
- external object storage adapters;
- asynchronous media processing;
- virus scanning and content inspection;
- direct-to-object-storage uploads;
- API contract documentation and OpenAPI generation.