# ADR-001: Modular Monolith with Domain-Oriented Boundaries

## Status
Accepted for Sprint 0B.

## Context
Manifest Stay PMS needs explicit module boundaries while remaining deployable as one Laravel application.

## Decision
Use a modular monolith. Domain code lives under `app/Domain`, infrastructure adapters under `app/Infrastructure`, and Laravel framework composition remains under `app/Providers`, `routes`, and `bootstrap`.

The Shared kernel must stay minimal and may contain only cross-domain abstractions with demonstrated reuse. Business modules must not depend on future modules that have not been implemented.

## Consequences
The application remains simple to deploy on XAMPP while gaining enforceable namespace and dependency boundaries. Future modules will be introduced incrementally with architecture tests.
