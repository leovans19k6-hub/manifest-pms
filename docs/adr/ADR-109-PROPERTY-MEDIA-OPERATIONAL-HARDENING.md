# ADR-109: Property Media Operational Hardening

## Status

Accepted

## Context

The Property Media subsystem persists metadata in the relational database while storing binary content through the `PropertyStorage` abstraction.

Database transactions cannot atomically include external object storage operations. The existing implementation therefore uses compensating operations when persistence, audit logging, deletion, or storage recovery fails.

The subsystem required explicit operational contracts for failure handling and input normalization.

The identified gaps were:

1. Upload persistence or audit failure triggered storage cleanup, but cleanup failure could hide the original exception.
2. Delete persistence or audit failure triggered storage restoration, but restoration failure could hide the original exception and did not expose the consistency recovery failure explicitly.
3. Private download TTL configuration accepted non-positive values.
4. Uploaded filenames were used without a portable normalization contract before persistence and storage key generation.

## Decision

### Upload compensation

Binary content is written to storage before the database transaction.

If the database transaction or audit logging fails:

- storage cleanup is attempted;
- cleanup is best-effort;
- cleanup failure must not replace or hide the original persistence or audit exception;
- the original exception is rethrown.

This preserves the primary failure cause for callers and operational diagnostics.

A cleanup failure may leave an orphaned storage object. Reconciliation of orphaned objects is outside the scope of this story.

### Delete compensation

Binary content is backed up and deleted from storage before the database transaction.

If the database transaction or audit logging fails:

- restoration of the binary content is attempted;
- if restoration succeeds, the original exception is rethrown;
- if restoration fails, the service throws `RuntimeException` with message `Property media consistency recovery failed.`;
- the original persistence or audit exception is preserved as the previous exception;
- the database transaction remains rolled back, preserving the media database record.

This explicitly represents a consistency incident where the database record exists but the storage object could not be restored.

### Private download TTL

`property_media.download_ttl_seconds` must be a positive integer at runtime.

A non-positive TTL is rejected before generating a temporary URL.

The current story does not introduce a maximum TTL policy.

### Filename normalization

Uploaded filenames are normalized before:

- persistence to `original_name`;
- construction of the storage key.

Normalization:

- trims surrounding whitespace;
- treats both `/` and `\` as path separators;
- removes path components;
- replaces characters outside `A-Z`, `a-z`, `0-9`, `.`, `_`, and `-` with `_`;
- falls back to `file` for empty, `.` or `..` results.

The binary contents, MIME type, checksum, metadata, and authorization contracts are unchanged.

## Consequences

### Positive

- Original upload failures remain observable even when cleanup fails.
- Delete recovery failures are explicit and preserve causal exception chaining.
- Invalid private download TTL configuration fails fast.
- Persisted filenames and storage keys use a predictable portable format.
- Existing HTTP API and administration web UI contracts remain unchanged.
- No database schema migration is required.

### Negative

- Failed upload cleanup can leave orphaned storage objects.
- Failed delete restoration can leave a database record referencing a missing storage object.
- Filename normalization may change the user-supplied filename before persistence.
- Recovery still relies on synchronous compensating operations.

## Verification

The operational contracts are covered by `PropertyMediaOperationalHardeningTest`.

Regression verification includes:

- `PropertyMediaAdministrationApplicationTest`;
- the complete PHPUnit test suite;
- Laravel Pint;
- `git diff --check`.

At acceptance time:

- 136 tests passed;
- 548 assertions passed;
- 217 files passed Laravel Pint validation;
- the working tree was clean after the implementation commit.

## Scope

This ADR covers Property Media operational hardening only.

It does not introduce:

- asynchronous reconciliation jobs;
- orphaned object garbage collection;
- storage retry policies;
- MIME content sniffing;
- maximum download TTL policy;
- database schema changes;
- HTTP API changes;
- administration web UI changes.