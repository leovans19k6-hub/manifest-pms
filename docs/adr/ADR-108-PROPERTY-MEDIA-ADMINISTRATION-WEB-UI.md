# ADR-108: Property Media Administration Web UI

## Status

Accepted

## Context

The Property bounded context already provides:

- property media and document domain persistence;
- tenant-scoped storage contracts;
- application actions and commands for media administration;
- private download generation;
- authorization and current organization foundations;
- JSON HTTP APIs for property assets and documents.

The administration application requires server-rendered web flows for operators to manage property assets and documents without duplicating domain or application-layer business logic.

The web interface must preserve:

- organization isolation;
- permission enforcement;
- application-layer validation and authorization;
- private storage boundaries;
- audit logging;
- explicit media and document lifecycle contracts.

## Decision

Implement the Property Media Administration Web UI as a server-rendered Laravel administration surface.

### Entry point

Expose a single property-scoped media administration page:

`GET /admin/properties/{property}/media`

The page may be accessed by a membership that has at least one of:

- `property.media.view`;
- `property.documents.view`.

The controller determines asset and document visibility independently.

A user without either view permission receives HTTP 403.

A property outside the current organization is not exposed and results in HTTP 404.

### Asset web flows

Expose web routes for:

- asset upload;
- asset metadata update;
- asset reorder;
- private asset download;
- asset deletion.

Each mutation route has explicit route-level permission middleware.

The controller delegates mutations to existing application actions and commands.

The web layer does not duplicate storage, authorization, audit, or transaction logic.

### Document web flows

Expose web routes for:

- document upload;
- document metadata update;
- document lifecycle change;
- private document download;
- document deletion.

Each mutation route has explicit route-level permission middleware.

The controller delegates mutations to existing application actions and commands.

Supported document lifecycle transitions use the existing `PropertyDocumentLifecycle` enum contract.

### Request validation

Dedicated web Form Requests define browser-facing validation contracts for:

- media listing filters;
- asset upload;
- asset metadata update;
- asset reorder;
- document upload;
- document metadata update;
- document lifecycle change.

Form Requests validate transport input only.

Domain and application invariants remain enforced below the HTTP layer.

### Tenant isolation

Property, asset, and document lookups are scoped to the current organization.

Foreign tenant resources return HTTP 404.

Application services and actions retain their existing tenant guards as defense in depth.

### Authorization

Mutation routes use explicit permission middleware.

The media index route performs dynamic authorization because asset visibility and document visibility are independent capabilities.

UI controls are rendered according to calculated membership abilities.

UI visibility is not treated as an authorization boundary.

Application-layer authorization remains authoritative.

### Private downloads

The web layer does not expose storage keys or disks.

Download routes delegate to existing private download actions.

Successful requests redirect to temporary URLs produced by the configured `PropertyStorage` implementation.

### Audit logging

The web layer relies on existing application services for audit events.

Covered mutations include:

- media creation;
- metadata updates;
- asset reorder;
- document lifecycle changes;
- media deletion.

The controller does not write audit records directly.

### Testing strategy

Feature tests cover:

- guest authentication behavior;
- view capability separation;
- missing permission denial;
- upload flows;
- metadata updates;
- asset reorder;
- document lifecycle changes;
- private downloads;
- deletion;
- validation redirects;
- audit events;
- cross-tenant property isolation;
- cross-tenant asset isolation;
- cross-tenant document isolation.

The complete project test suite, Laravel Pint, and `git diff --check` are required to pass before merge.

## Consequences

### Positive

- Reuses existing application-layer behavior.
- Preserves tenant and authorization boundaries.
- Avoids business logic duplication in controllers.
- Keeps private storage details outside rendered resources.
- Provides independently permissioned asset and document administration.
- Establishes regression coverage for browser-based administration flows.

### Negative

- `PropertyMediaController` coordinates multiple use cases and is larger than single-purpose controllers.
- The server-rendered interface currently requires full-page form submissions.
- Asset and document listing filters share one administration page and therefore require explicit query parameter namespacing.

## Rejected alternatives

### Duplicate media business logic in web controllers

Rejected because it would bypass existing application actions, storage compensation behavior, authorization guards, and audit contracts.

### Authorize only through hidden UI controls

Rejected because UI visibility is not a security boundary.

### Expose storage paths directly

Rejected because property media and documents are private resources.

### Require both asset-view and document-view permissions for the index page

Rejected because asset and document capabilities are intentionally independent.

## Verification

The implementation is verified by:

- `PropertyMediaWebUiTest`;
- `PropertyAssetWebUiTest`;
- `PropertyDocumentWebUiTest`;
- the complete Laravel test suite;
- Laravel Pint;
- `git diff --check`;
- route and permission matrix inspection.