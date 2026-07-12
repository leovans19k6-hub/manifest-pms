# ADR-105: Property Media & Document Foundation
## Status
Accepted
## Decision
Property assets and documents are tenant-owned metadata records linked to Property. Binary persistence is behind `PropertyStorage`; domain/application code does not depend on a local filesystem. Laravel storage is the initial adapter and the disk is configurable.
Uploads enter through immutable DTO/commands and application actions. Central validation enforces MIME allowlists, non-empty content and size limits. Tenant and permission checks occur before persistence. Storage keys are generated server-side and include organization/property namespaces; client tenant/system metadata is never accepted.
Database metadata creation and audit logging share one transaction. Because object storage cannot join the SQL transaction, failures after storage write trigger compensating deletion. Audit failure therefore rolls back metadata and removes the stored object.
RBAC adds explicit media/document view/create permissions. Property relationships expose assets/documents. No HTTP endpoints or UI are introduced.
## Verification
Tests cover schema/relationships, validation, authorization, tenant isolation, storage persistence, audit integration, SQL rollback and storage compensation.
