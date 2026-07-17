# ADR-0003: Introduce Calendar Engine

- Status: Accepted
- Date: 2026-07-18

## Context

Manifest PMS requires a reusable calendar that can be shared by multiple
business domains.

Upcoming modules include:

- Availability
- Housekeeping
- Maintenance
- Rate Calendar
- Booking Engine

If each module generates its own calendar structure, duplicated logic,
inconsistent behavior, and difficult maintenance will occur.

A reusable calendar abstraction is required.

---

## Decision

Introduce a dedicated Calendar domain.

```
Domain
└── Calendar
    ├── CalendarBuilder
    ├── DTO
    │   ├── CalendarMonth
    │   ├── CalendarWeek
    │   └── CalendarDay
    └── Contracts
```

The Calendar domain is responsible only for generating calendar structures.

It has no knowledge of reservations, housekeeping, maintenance,
availability, pricing, or booking.

Business modules enrich CalendarDay with their own information.

---

## Consequences

### Advantages

- Single calendar implementation.
- Reusable across multiple modules.
- Business logic separated from rendering.
- Blade components remain presentation-only.
- Easier testing.

### Trade-offs

- Additional DTO layer.
- Slight increase in abstraction.

The long-term maintenance benefits outweigh the initial complexity.

---

## Related ADRs

- ADR-0001 Record Architecture Decisions
- ADR-0002 Adopt Domain-Driven Design