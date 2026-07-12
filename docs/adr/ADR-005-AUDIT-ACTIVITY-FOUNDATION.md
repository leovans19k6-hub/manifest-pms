# ADR-005: Audit & Activity Foundation

## Status
Accepted

## Decision
Manifest Stay PMS separates immutable compliance-oriented `audit_logs` from operational `activity_logs`. Both capture organization, actor, event, request correlation ID, and metadata where applicable.

Audit logs additionally capture polymorphic subject references and old/new values. Application-level update/delete operations on `AuditLog` are rejected. Audit reads must use `AuditQueryService`, which requires and scopes to the current organization context.

`RequestContext` is request-scoped and provides one correlation UUID per request or accepts an upstream `X-Request-ID`. `SetRequestContext` propagates the ID to the response.

`AuditLogger` and `ActivityLogger` capture the current organization and optional actor explicitly. This avoids hidden authorization assumptions and supports CLI/background execution with explicit context.

Activity retention defaults to 90 days. Audit retention is unset by default because destructive audit retention requires an explicit compliance decision.

## Security Boundaries
- Tenant audit reads require current organization context.
- Audit records are immutable through the domain model.
- Actor and organization foreign keys become null if principals are deleted, preserving historical records.
- Request IDs support trace correlation without being authorization credentials.

## Scope
STORY-005 provides foundation services and request context. It does not add audit UI, asynchronous queues, broad automatic model observers, Property, Inventory, Reservation, or Finance modules.

## Verification
Tests cover context capture, old/new values, model immutability, tenant-safe queries, missing-context denial, activity logging, request ID lifecycle/propagation, and retention defaults.
