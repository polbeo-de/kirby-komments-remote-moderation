# ADR 0001: Separate Panel area for remote comments

Status: accepted

## Context

Komments 3.9.5 registers its `komments` area, views, and dialogs directly and
does not provide a documented, stable provider, hook, or adapter point for
adding sources to the existing comments view. Vendor files must not be changed
or copied.

## Alternatives

1. Patch Komments vendor code or copy its components.
2. Use an undocumented internal extension point.
3. Provide a dedicated area with similarly clear interaction logic.

## Decision and rationale

We choose alternative 3. The area is called Remote Comments in English and
uses the active Panel language for its label. It displays the selected source
persistently and requires a second confirmation before write actions against a
`live` source. This avoids vendor copies and coupling to internal Komments
details.

## Consequences

The interface is initially separate from Komments. As an upstream proposal, we
recommend a documented `CommentSourceProvider` interface through which
Komments can register sources, detail data, and moderation commands. The core
area could query these providers through dependency injection; each provider
would remain responsible for its own transport and authentication.

## Required evidence

Compatibility tests with Komments >= 3.9 and an upstream API review for every
Komments major version.
