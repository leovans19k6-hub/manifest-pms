# ADR-104: Property Administration Web UI
## Status
Accepted
## Context
Property administration needs a production-oriented server-rendered interface without duplicating business rules from STORY-102/103.
## Decision
Add authenticated, organization-scoped admin web routes under `/admin/properties`. A thin Admin PropertyController delegates queries to PropertyQueryService and mutations to Create/Update/Archive application actions. Existing Form Requests remain the validation contract. Property IDs are resolved through tenant-scoped PropertyService rather than implicit route-model binding.
Blade + Tailwind views provide list/filter/pagination, create/edit/archive flows, validation feedback, flash status messages, and permission-aware action visibility. Route middleware remains the enforcement boundary; hidden buttons are usability only, never authorization.
## Security
All routes require authentication, active organization context, and operation-specific permission middleware. Cross-tenant identifiers return 404. Tenant/system fields are not exposed in forms and remain blocked by the application validation boundary. Audit hooks remain inside PropertyService transaction boundaries.
## Scope
No SPA, Livewire, API changes, bulk operations, restore/permanent delete, or dashboard redesign.
## Verification
Feature tests cover guest redirect, tenant-scoped filtered listing, pagination contract, permission-aware actions, validation feedback, create/update/archive flows, audit integration, per-route permission denial, and cross-tenant 404 behavior.
