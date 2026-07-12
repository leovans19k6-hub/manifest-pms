# ADR-006: Authentication & Session Foundation

## Status
Accepted

## Decision
Manifest Stay PMS uses Laravel's stateful session guard behind a domain authentication service. Successful login regenerates the session, resolves an active organization membership into the current organization context, and records operational activity. Failed login attempts are recorded without authenticating a user. Logout records activity, clears organization context, logs out the guard, invalidates the session, and regenerates the CSRF token.

Protected tenant routes require both authentication and an active organization context. Permission middleware resolves the membership for the current user and current organization and delegates to the tenant-safe AuthorizationService from ADR-004.

Login attempts are rate limited by normalized email plus IP address. Session cookie security remains environment configurable so local HTTP development works while production can require secure cookies.

## Security Boundaries
- Session ID regeneration after successful authentication.
- Session invalidation and CSRF token regeneration on logout.
- No organization context without an active membership.
- Permission checks are bound to current user membership and current organization.
- Failed authentication is rate limited and activity logged.
- Super Admin behavior remains constrained by ADR-004.

## Scope
No password reset, MFA, SSO, user administration UI, invitation flow, or Property domain is implemented in STORY-006.

## Verification
Feature tests cover successful login, failed login, logout, session regeneration, organization resolution, activity hooks, authentication and organization middleware, rate limiting, and tenant-safe permission middleware.
