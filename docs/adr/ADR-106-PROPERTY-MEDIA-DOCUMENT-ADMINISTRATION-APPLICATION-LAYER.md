# ADR-106: Property Media & Document Administration Application Layer

## Status
Accepted

## Decision
Extend the Property media foundation with an application-only administration layer. Immutable commands and DTOs represent metadata changes, deletion, asset ordering, document lifecycle transitions, and private-download requests. No HTTP routes or UI are introduced.

Tenant-scoped query services enforce current organization and RBAC before filtering, allowlisted sorting, and pagination. Mutations are delegated to application actions and a domain service that keeps database changes and audit records in transactions.

Private downloads are exposed through `PropertyStorage::temporaryUrl`; callers never receive storage paths or public-disk assumptions. Download TTL is configuration-driven and tenant/permission checks occur before URL generation.

Deletion uses explicit compensation: the private object is read, storage deletion must succeed before database mutation, and audit/database failure restores the object. Storage failure leaves database state untouched. Metadata, ordering, and lifecycle mutations place audit writes in the same database transaction so audit failure rolls back mutation.

Asset ordering accepts only a complete tenant/property-owned identifier set supplied by the command and rejects foreign/missing identifiers. Document lifecycle is explicit (`active`/`archived`) and records `archived_at`.

## Permissions
`property.media.update`, `property.media.delete`, `property.documents.update`, and `property.documents.delete` are added to the RBAC catalog. Existing view permissions gate query and signed/private download generation.

## Scope
No HTTP endpoints, upload UI, administration UI, public URLs, bulk cross-property operations, or permanent public sharing.

## Verification
Tests cover immutable application actions, validation, authorization, tenant isolation, query filtering/sorting/pagination, ordering, private download security, storage deletion failure, audit rollback, storage compensation, metadata mutation, deletion, and document lifecycle.
