# Oxy AI Readiness

# REST API Specification

Version 1.1

---

> **Canonical note (ADR-003):** Every route below is a full path under the base URL — no route may omit its owning module/engine's slug as its first path segment. The MONITORING API and REPORTING API sections have been corrected to be fully prefixed (they previously listed bare `/events`, `/history`, `/templates`, `/generate`, `/export`, `/share`, which would have collided with identically-named routes in other modules). See `.project/adr/ADR-003-rest-api-naming.md`.

---

# Purpose

The REST API exposes every capability of Oxy AI Readiness through a secure, versioned and fully documented interface.

Every feature available in the dashboard should also be available through the API.

The REST API enables integrations with cloud services, CI/CD pipelines, developer tools, enterprise platforms and third-party applications.

---

# Vision

API First.

Every feature is an API.

The dashboard is simply one client consuming the API.

---

############################################################

API DESIGN PRINCIPLES

############################################################

RESTful

Versioned

Stateless

Predictable

Discoverable

Secure

Documented

Backward Compatible

---

############################################################

BASE URL

############################################################

/wp-json/oxy-ai/v1

---

############################################################

API VERSIONING

############################################################

v1

v2

v3

Experimental

Deprecated

Legacy

---

############################################################

CONTENT TYPES

############################################################

application/json

application/problem+json

application/ld+json

text/plain

text/markdown

application/xml

---

############################################################

AUTHENTICATION

############################################################

WordPress Authentication

Application Passwords

OAuth 2.1

JWT

API Keys

Bearer Tokens

Future Enterprise SSO

---

############################################################

AUTHORIZATION

############################################################

Administrator

Editor

Developer

Auditor

Viewer

API Client

Enterprise Roles

---

############################################################

COMMON RESPONSE

############################################################

Success

Status

Message

Data

Meta

Pagination

Execution Time

Version

Request ID

---

############################################################

ERROR RESPONSE

############################################################

Status

Error Code

Message

Details

Documentation URL

Request ID

Timestamp

---

############################################################

DISCOVERY API

############################################################

GET

/discovery

GET

/discovery/files

GET

/discovery/resources

POST

/discovery/run

---

############################################################

AUDIT API

############################################################

GET

/audit

GET

/audit/history

GET

/audit/issues

POST

/audit/run

POST

/audit/reset

---

############################################################

SCORING API

############################################################

GET

/score

GET

/score/history

GET

/score/categories

POST

/score/recalculate

---

############################################################

VALIDATION API

############################################################

GET

/validation

POST

/validation/run

POST

/validation/file

POST

/validation/schema

---

############################################################

GENERATION API

############################################################

GET

/generation

POST

/generation/run

POST

/generation/preview

POST

/generation/publish

---

############################################################

AUTOFIX API

############################################################

GET

/autofix

POST

/autofix/run

POST

/autofix/batch

POST

/autofix/rollback

---

############################################################

MONITORING API

############################################################

GET

/monitoring

GET

/monitoring/events

GET

/monitoring/history

GET

/monitoring/status

POST

/monitoring/start

POST

/monitoring/stop

POST

/monitoring/reset

---

############################################################

REPORTING API

############################################################

GET

/reports

GET

/reports/history

GET

/reports/templates

POST

/reports/generate

POST

/reports/export

POST

/reports/share

DELETE

/reports/cache

---

############################################################

MODULE API

############################################################

GET

/modules

GET

/modules/{id}

POST

/modules/install

POST

/modules/enable

POST

/modules/disable

DELETE

/modules/{id}

---

############################################################

STANDARD API

############################################################

GET

/standards

GET

/standards/{id}

POST

/standards/register

POST

/standards/update

---

############################################################

SDK API

############################################################

GET

/sdk

GET

/sdk/hooks

GET

/sdk/events

GET

/sdk/interfaces

---

############################################################

WEBHOOKS

############################################################

Audit Completed

Score Updated

Issue Found

Issue Fixed

Monitoring Alert

Module Installed

Module Updated

License Changed

---

############################################################

RATE LIMITING

############################################################

Anonymous

60/hour

Authenticated

1000/hour

Enterprise

Unlimited (Configurable)

---

############################################################

PAGINATION

############################################################

page

per_page

cursor

limit

offset

---

############################################################

FILTERING

############################################################

category

severity

status

date

module

standard

search

---

############################################################

SORTING

############################################################

date

severity

priority

score

title

status

---

############################################################

OPENAPI

############################################################

OpenAPI 3.1

Swagger UI

JSON Schema

Examples

SDK Generation

---

############################################################

EVENT STREAMING

############################################################

Webhooks

Server Sent Events (SSE)

Future WebSocket Support

---

############################################################

API SECURITY

############################################################

HTTPS Only

CSRF Protection

Nonce Validation

Permission Checks

Request Signing

Rate Limiting

Input Validation

Output Escaping

Audit Logging

---

############################################################

OBSERVABILITY

############################################################

Request IDs

Execution Time

Metrics

Tracing

Health Endpoint

Readiness Endpoint

Liveness Endpoint

---

############################################################

DEPRECATION POLICY

############################################################

Minimum Support

24 Months

Migration Guides

Deprecation Headers

Compatibility Layer

---

############################################################

FUTURE FEATURES

############################################################

GraphQL API

gRPC

Cloud Sync API

Multi-site API

Enterprise Federation

Streaming APIs

AI Agent API

MCP Remote API

---

# Success Criteria

Every capability of Oxy AI Readiness must be available through a documented, versioned and secure API.

No dashboard functionality should exist without a corresponding API endpoint.