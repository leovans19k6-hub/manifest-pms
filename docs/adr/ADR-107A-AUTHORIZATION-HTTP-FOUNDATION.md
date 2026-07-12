\# ADR-107A: Authorization HTTP Foundation



\## Status



Accepted.



\## Context



Manifest Stay PMS requires a tenant-safe HTTP authorization boundary shared by web and API requests.



The existing foundation already provided authentication, organization context resolution, RBAC permission resolution, authorization services, and request-scoped organization state. STORY-007A extends that foundation with explicit current-membership state and a stable HTTP authorization contract.



The HTTP pipeline must prevent cross-tenant access, stale request state, permission checks against memberships from another organization, and authenticated users becoming unable to terminate their session when no active organization membership is available.



\## Decision



Authorization HTTP requests use an ordered pipeline:



1\. Laravel authentication resolves the authenticated user.

2\. `RequireOrganization` clears stale organization and membership state.

3\. `OrganizationContextService` resolves an active organization available to the authenticated user.

4\. `MembershipResolver` resolves the active membership for the current user and current organization.

5\. `RequirePermission` consumes the already-resolved `CurrentOrganization` and `CurrentMembership`.

6\. `AuthorizationService` evaluates the requested permission against the current active membership.

7\. `RequireOrganization` clears organization and membership state in a `finally` block after downstream handling completes.



`RequirePermission` does not resolve or repair missing membership state.



\## CurrentOrganization Lifecycle



`CurrentOrganization` is request-scoped.



The organization middleware clears stale state before resolution. Organization activation clears `CurrentMembership` before changing current organization state.



After the downstream response completes or throws, organization and membership state are cleared.



This lifecycle prevents state reuse in long-lived application workers and sequential requests handled by the same application container.



\## CurrentMembership Lifecycle



`CurrentMembership` is request-scoped and represents only the active membership belonging to the authenticated user and `CurrentOrganization`.



It is cleared when:



\- organization context is cleared;

\- organization context changes;

\- membership resolution fails;

\- request authorization processing completes.



Permission middleware never resolves a missing membership implicitly.



\## MembershipResolver Responsibility



`MembershipResolver` is the single active-membership lookup path.



It requires an existing current organization and constrains lookup by:



\- authenticated user identifier;

\- current organization identifier;

\- active membership status.



Missing organization context, inactive membership, suspended membership, and cross-tenant membership cannot produce current membership state.



\## Middleware Ordering



Tenant-protected routes use:



`auth -> organization -> permission`



The `organization` middleware establishes both current organization and current membership state.



The `permission` middleware only consumes that established state.



Routes requiring authentication but not tenant context may use `auth` without `organization`.



\## HTTP Error Contract



Authorization failures use stable machine-readable codes and human-readable messages.



JSON authorization responses use:



```json

{

&#x20; "error": {

&#x20;   "code": "permission\_denied",

&#x20;   "message": "Missing required permission \[example.permission]."

&#x20; }

}

