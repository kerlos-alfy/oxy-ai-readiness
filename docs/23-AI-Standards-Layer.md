# Oxy AI Readiness

# AI Standards Layer

Version 1.1

---

> **Canonical note (ADR-001):** A Standard is owned by exactly one Module and lives inside that Module's folder as `{Module}Standard.php` — there is **no** top-level `app/Standards/` directory. A Standard's `discover()/generate()/validate()/score()/monitor()/report()` methods delegate to the owning Module's already-registered Generator/Validator/ScoreProvider/Monitor/Reporter classes; a Standard's own unique responsibility is external-specification metadata only (`specification()`, `version()`, `supports()`, `migrate()`). See `.project/adr/ADR-001-modules-vs-standards.md`.

---

# Purpose

The AI Standards Layer provides a unified abstraction for all current and future AI web standards.

Instead of re-implementing spec-compliance logic per module, every Standard wraps its owning Module's existing engine registrations and adds version-negotiation and deprecation-lifecycle metadata on top, through a centralized Standards Registry.

Every standard shares the same lifecycle, validation model, scoring integration and monitoring workflow.

---

# Vision

Standards evolve.

Architecture should not.

The system should support today's AI standards and tomorrow's specifications without changing the core platform.

---

############################################################

ARCHITECTURE

############################################################

                  Oxy Core

                      │

          Standards Registry

                      │

      ┌───────────────┼────────────────┐

   LLMS         MCP        Agent Skills

      │

   auth.md

      │

   ai.txt

      │

 humans.txt

      │

 Future Standards

---

############################################################

STANDARD OWNERSHIP (per Module)

############################################################

| Owning Module | Standard(s) |
|---|---|
| Robots | robots.txt |
| LLMS | llms.txt |
| Markdown | Markdown Negotiation |
| Content Signals | Content Signals |
| MCP | MCP |
| Agent Skills | Agent Skills |
| API Catalog | api-catalog |
| Discovery | ai.txt, humans.txt, auth.md |
| OAuth Discovery | openid-configuration, oauth-authorization-server, oauth-protected-resource |

Modules with no owned Standard (pure WordPress/platform features): Dashboard, Audit, Headers, Settings, Logs, License, Updater, Commerce, Analytics, Monitoring, Reports.

---

############################################################

STANDARD LIFECYCLE

############################################################

Install

↓

Register

↓

Discover

↓

Generate

↓

Validate

↓

Score

↓

Monitor

↓

Report

↓

Update

↓

Deprecate

↓

Remove

---

############################################################

STANDARD INTERFACE

############################################################

Every standard implements

StandardInterface

Methods

id()

name()

version()

specification()

discover()

generate()

validate()

score()

monitor()

report()

supports()

migrate()

---

############################################################

STANDARD TYPES

############################################################

Discovery

Metadata

Protocol

Authentication

Negotiation

Content

Capabilities

Skills

API

Security

Future

---

############################################################

SUPPORTED STANDARDS

############################################################

robots.txt

llms.txt

auth.md

ai.txt

humans.txt

api-catalog

Markdown Negotiation

Content Signals

MCP

Agent Skills

OpenAPI

JSON Feed

RSS

Sitemap

Future Standards

---

############################################################

STANDARD REGISTRY

############################################################

Register

Enable

Disable

Version

Priority

Dependencies

Capabilities

Compatibility

Status

---

############################################################

DEPENDENCIES

############################################################

Required Standards

Optional Standards

Conflicting Standards

Minimum Version

Maximum Version

---

############################################################

STANDARD STATES

############################################################

Installed

Enabled

Disabled

Deprecated

Experimental

Preview

Stable

Legacy

Removed

---

############################################################

DISCOVERY

############################################################

Every standard defines

Detection Rules

Discovery Files

HTTP Headers

URLs

Resources

Validation Rules

---

############################################################

GENERATION

############################################################

Static

Dynamic

Hybrid

Scheduled

On Demand

---

############################################################

VALIDATION

############################################################

Syntax

Schema

Semantics

Compatibility

Performance

Security

Best Practices

---

############################################################

SCORING

############################################################

Each standard contributes

AI Readiness Score

Trust Score

Discovery Score

Future Readiness

Compliance

Confidence

---

############################################################

MONITORING

############################################################

Availability

Integrity

Updates

Compatibility

Changes

Deprecation

---

############################################################

REPORTING

############################################################

Coverage

Health

Adoption

Impact

Recommendations

History

---

############################################################

VERSIONING

############################################################

Current Version

Supported Versions

Deprecated Versions

Migration Paths

Compatibility Matrix

---

############################################################

REST API

############################################################

GET

/standards

GET

/standards/{id}

GET

/standards/status

GET

/standards/health

POST

/standards/register

POST

/standards/validate

POST

/standards/generate

POST

/standards/update

---

############################################################

EVENTS

############################################################

StandardRegistered

StandardEnabled

StandardDisabled

StandardUpdated

StandardDeprecated

StandardRemoved

---

############################################################

FUTURE FEATURES

############################################################

Cloud Standards

Community Standards

Enterprise Standards

Marketplace

Automatic Specification Updates

AI Generated Standards

Standards Marketplace

---

# Success Criteria

The AI Standards Layer should isolate AI specifications from business logic.

Adding support for a new standard should require only implementing the StandardInterface and registering it with the Standards Registry.