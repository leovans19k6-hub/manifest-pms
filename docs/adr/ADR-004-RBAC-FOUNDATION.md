# ADR-004: RBAC Foundation

## Status
Accepted

## Decision
Manifest Stay PMS uses role-based access control attached to `organization_users`, not directly to users. This preserves the organization membership as the tenant authorization boundary.

Permissions are global immutable codes. Organization roles belong to one organization. System roles have no organization and must be explicitly marked `is_system`.

`PermissionResolver` only resolves permissions when the supplied membership is active and belongs to the current organization context. Organization roles from other tenants are ignored. System roles are considered only when explicitly marked as system roles.

`AuthorizationService` denies access when there is no matching active current membership. The Super Admin bypass requires an active current membership plus a system role with code `SUPER_ADMIN`, `is_system = true`, and no organization owner. An organization-scoped role named `SUPER_ADMIN` does not bypass authorization.

## Database Constraints
- Permissions have globally unique codes.
- Roles are unique by organization and code.
- Role-permission assignments are unique.
- Membership-role assignments are unique.
- Pivot rows cascade when parent records are deleted.

## Scope
STORY-004 does not implement audit logging, CRUD UI, policy wiring across application modules, property, inventory, reservation, or finance.

## Verification
Tests cover RBAC relationships, duplicate assignment constraints, permission allow/deny behavior, tenant isolation, inactive membership denial, secure Super Admin bypass, fake organization Super Admin denial, and idempotent seed behavior.
