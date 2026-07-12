# ADR-003: Organization Context and Membership Boundary

## Status
Accepted

## Decision
The current organization is represented by a request-scoped `CurrentOrganization` object and coordinated by `OrganizationContextService`.

Only active memberships may resolve or switch organization context. A valid session selection is preferred; otherwise the active default membership is used, then the first active membership. Invalid, foreign, suspended, invited, or left memberships cannot become the current organization.

`SetCurrentOrganization` clears context before resolution and in a `finally` block after the response to prevent context leakage in long-lived workers.

A successful explicit switch dispatches `OrganizationSwitched`.

## Scope
STORY-003 does not implement RBAC, audit logging, organization CRUD UI, property, inventory, or reservation behavior.

## Verification
Tests cover default resolution, valid session preference, invalid-session fallback, suspended membership rejection, cross-organization rejection, switch event dispatch, session/context updates, and explicit context clearing.
