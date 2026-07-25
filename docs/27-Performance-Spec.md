# Oxy AI Readiness

# Performance Specification

Version 1.0

---

# Purpose

The Performance Layer ensures that Oxy AI Readiness remains fast, efficient and scalable across small WordPress websites, large WooCommerce stores, multisite installations and enterprise environments.

Performance must be treated as a core architectural requirement rather than a later optimization.

---

# Vision

Fast by default.

Asynchronous when possible.

Incremental instead of repetitive.

Cached without becoming stale.

Observable at every layer.

---

############################################################

PERFORMANCE PRINCIPLES

############################################################

Lazy Loading

Incremental Processing

Background Execution

Minimal Database Writes

Efficient Queries

Cache First

Batch Operations

Fail Gracefully

Measure Everything

No Frontend Blocking

---

############################################################

PERFORMANCE ARCHITECTURE

############################################################

User Request

↓

REST Controller

↓

Service Layer

↓

Cache Layer

↓

Repository

↓

Database

↓

Queue or Background Worker

↓

Result Storage

↓

Notification

---

############################################################

PERFORMANCE TARGETS

############################################################

Admin Dashboard Initial Load

< 1.5 seconds

API Read Request

< 300ms

API Write Request

< 500ms

Quick Audit

< 2 seconds

Full Audit Small Website

< 10 seconds

Full Audit Large Website

Background Process

Score Calculation

< 100ms

Validation Resource

< 250ms

Generation Resource

< 500ms

Database Query

< 100ms

---

############################################################

FRONTEND IMPACT

############################################################

The plugin must not negatively affect the public-facing website.

Requirements

No unnecessary frontend scripts

No unnecessary frontend styles

No synchronous external requests

No heavy database queries during page rendering

No audit execution during normal frontend requests

No monitoring work during visitor requests

No large autoloaded options

No blocking file generation

---

############################################################

ADMIN PERFORMANCE

############################################################

Admin pages must use

Code Splitting

Lazy Loaded Routes

Pagination

Virtualized Tables

Debounced Search

Optimistic Updates

Cached Dashboard Data

Skeleton Loaders

Background Refresh

---

############################################################

ASSET LOADING

############################################################

Load assets only on Oxy pages.

Separate vendor bundles.

Minify JavaScript.

Minify CSS.

Tree shake unused code.

Use hashed filenames.

Preload only critical resources.

Avoid duplicate WordPress packages.

---

############################################################

DATABASE PERFORMANCE

############################################################

Use indexed columns.

Avoid SELECT *.

Avoid unbounded queries.

Use pagination.

Use cursor pagination for large datasets.

Use batch inserts.

Use transactions when appropriate.

Avoid excessive option updates.

Avoid large serialized values.

Avoid autoloading operational data.

---

############################################################

QUERY RULES

############################################################

Maximum Query Duration

100ms target

Maximum Queries Per Dashboard Request

20 target

Maximum Rows Per Request

100 default

Maximum Export Rows Per Batch

1000 default

Slow Query Threshold

250ms

---

############################################################

INDEXING STRATEGY

############################################################

Indexes should exist for

audit_id

rule_id

status

severity

created_at

updated_at

resource

module

standard

user_id

scheduled_at

checksum

Composite indexes should support common filters.

Examples

audit_id + severity

status + created_at

module + status

resource + checksum

scheduled_at + status

---

############################################################

CACHE ARCHITECTURE

############################################################

Request Cache

Object Cache

Transient Cache

Filesystem Cache

Persistent Cache

Redis Support

CDN Support

Browser Cache

---

############################################################

CACHE TYPES

############################################################

Configuration Cache

Discovery Cache

Validation Cache

Generation Cache

Score Cache

Dashboard Cache

Report Cache

API Response Cache

---

############################################################

CACHE KEYS

############################################################

Every cache key should include

Site ID

Module

Resource

Version

Language

Checksum

Configuration Version

---

############################################################

CACHE INVALIDATION

############################################################

Invalidate cache when

Content changes

Settings change

Plugin updates

Theme changes

Module changes

Standard changes

Resource checksum changes

Manual reset

Scheduled expiration

---

############################################################

CACHE TAGGING

############################################################

Cache entries may be tagged by

Module

Standard

Resource

Audit

Site

Language

User

This allows selective invalidation.

---

############################################################

BACKGROUND PROCESSING

############################################################

Long-running operations must execute asynchronously.

Examples

Full Audits

Large Reports

Bulk Auto Fix

Site Crawling

Historical Analysis

Exports

Cloud Sync

Monitoring

Benchmarking

---

############################################################

QUEUE SYSTEM

############################################################

Queue jobs must support

Priority

Retries

Timeouts

Backoff

Failure Handling

Dead Letter Queue

Cancellation

Progress Tracking

Deduplication

---

############################################################

JOB PRIORITIES

############################################################

Critical

Security validation

Rollback

---------------

High

Auto Fix

Monitoring alerts

---------------

Normal

Audits

Generation

---------------

Low

Reports

Analytics

Cleanup

---

############################################################

JOB STATES

############################################################

Pending

Scheduled

Running

Completed

Failed

Retrying

Cancelled

Expired

---

############################################################

SCHEDULER

############################################################

Support

WP-Cron

Action Scheduler

Real Cron

CLI Workers

Cloud Workers

Enterprise Queue Workers

---

############################################################

INCREMENTAL AUDITING

############################################################

The Audit Engine should not scan the entire website after every change.

It should identify affected resources.

Example

Post updated

↓

Validate post Markdown

↓

Validate related metadata

↓

Update content score

↓

Invalidate related reports

↓

Avoid full website audit

---

############################################################

CHANGE DETECTION

############################################################

Use

Checksums

Content hashes

Last modified timestamps

Version numbers

Dependency graphs

Event tracking

---

############################################################

CONTENT PROCESSING

############################################################

Large websites should process content in batches.

Batch Size

Configurable

Default

50 resources

Memory-aware adaptation

Resume support

Progress tracking

---

############################################################

MEMORY MANAGEMENT

############################################################

Avoid loading full datasets into memory.

Use generators.

Use streaming.

Release references.

Limit payload sizes.

Use chunked processing.

Track memory peaks.

---

############################################################

MEMORY TARGETS

############################################################

Normal Admin Request

< 32MB additional usage

Quick Audit

< 64MB

Background Audit

< 128MB default

Large Export

Streamed

---

############################################################

HTTP PERFORMANCE

############################################################

Use

Conditional requests

ETag

Last-Modified

Compression

Keep-Alive

HTTP/2

HTTP/3 readiness

Connection timeouts

Retry policies

---

############################################################

EXTERNAL REQUESTS

############################################################

Every external request must define

Connect timeout

Read timeout

Maximum retries

Exponential backoff

Circuit breaker

Cache policy

Fallback behavior

---

############################################################

DEFAULT HTTP LIMITS

############################################################

Connection Timeout

3 seconds

Request Timeout

10 seconds

Maximum Retries

2

Maximum Response Size

5MB

---

############################################################

CIRCUIT BREAKER

############################################################

States

Closed

Open

Half Open

Used for

Cloud services

Remote validation

Licensing

Webhooks

External APIs

---

############################################################

RATE CONTROL

############################################################

Internal Rate Limits

External Request Limits

Crawler Concurrency Limits

Queue Concurrency Limits

API Rate Limits

Per-Site Limits

Per-User Limits

---

############################################################

CRAWLING PERFORMANCE

############################################################

Crawler must support

URL limits

Depth limits

Concurrency limits

Respect for robots.txt

Deduplication

Canonical normalization

Resume support

Timeout controls

---

############################################################

DEFAULT CRAWLER LIMITS

############################################################

Maximum URLs

500 free or default

Maximum Depth

5

Concurrent Requests

3

Request Delay

Configurable

Maximum Page Size

5MB

---

############################################################

REPORTING PERFORMANCE

############################################################

Reports should use

Pre-aggregated data

Cached metrics

Background rendering

Streamed exports

Paginated previews

Reusable templates

---

############################################################

EXPORT PERFORMANCE

############################################################

Large exports must be

Chunked

Streamed

Queued

Compressed

Resumable where possible

---

############################################################

MULTISITE PERFORMANCE

############################################################

Support

Per-site isolation

Network-level queues

Shared standards cache

Site batching

Network reports

Rate-limited fleet scans

---

############################################################

WOOCOMMERCE PERFORMANCE

############################################################

Product processing must support

Batch queries

Variation limits

Inventory-safe reads

Incremental updates

Product checksum tracking

Background generation

---

############################################################

OBSERVABILITY

############################################################

Track

Request duration

Database duration

Cache hit ratio

Queue wait time

Job duration

Memory usage

CPU usage

External request duration

Audit duration

Generation duration

---

############################################################

PERFORMANCE METRICS

############################################################

P50

P75

P95

P99

Average

Maximum

Error Rate

Throughput

Cache Hit Rate

Queue Depth

---

############################################################

PERFORMANCE LOGGING

############################################################

Slow Queries

Slow API Requests

Slow Jobs

Memory Threshold Breaches

Timeouts

Cache Miss Spikes

External Service Delays

---

############################################################

PROFILING

############################################################

Developer Mode may expose

Query Timeline

Service Timeline

Memory Timeline

Hook Timeline

API Timeline

Cache Timeline

Queue Timeline

---

############################################################

PERFORMANCE BUDGETS

############################################################

Admin JavaScript

< 300KB compressed initial bundle

Admin CSS

< 100KB compressed

API JSON Response

< 500KB default

Dashboard Query Count

< 20

Dashboard Blocking Requests

< 5

---

############################################################

DEGRADATION STRATEGY

############################################################

When resources are limited

Pause low priority jobs

Reduce batch size

Disable live refresh

Use cached values

Delay analytics

Skip optional checks

Notify administrator

---

############################################################

RESOURCE AWARENESS

############################################################

Detect

PHP memory limit

Execution time

Disk space

CPU availability

Object cache

Redis

Cron availability

Database version

Server load

---

############################################################

CLEANUP

############################################################

Scheduled cleanup should remove

Expired cache

Old temporary files

Expired jobs

Old logs

Old monitoring events

Expired reports

Unused snapshots

Orphaned records

---

############################################################

PERFORMANCE REST API

############################################################

GET

/performance

GET

/performance/metrics

GET

/performance/slow-queries

GET

/performance/jobs

GET

/performance/cache

POST

/performance/test

POST

/performance/cache/clear

POST

/performance/jobs/retry

---

############################################################

WP-CLI

############################################################

wp oxy performance status

wp oxy performance test

wp oxy cache clear

wp oxy queue status

wp oxy queue retry

wp oxy cleanup run

---

############################################################

EVENTS

############################################################

PerformanceThresholdExceeded

SlowQueryDetected

SlowJobDetected

MemoryLimitApproaching

CacheInvalidated

QueueOverloaded

ExternalServiceDelayed

---

############################################################

FILTERS

############################################################

oxy_ai_performance_budget

oxy_ai_batch_size

oxy_ai_queue_concurrency

oxy_ai_cache_ttl

oxy_ai_http_timeout

oxy_ai_crawler_limit

---

############################################################

COMPATIBILITY

############################################################

Compatible with

Redis Object Cache

Memcached

LiteSpeed Cache

WP Rocket

W3 Total Cache

Cloudflare

Varnish

Nginx FastCGI Cache

Action Scheduler

WP-CLI

---

############################################################

PERFORMANCE TESTING

############################################################

Load Testing

Stress Testing

Endurance Testing

Spike Testing

Database Testing

Cache Testing

Queue Testing

Large Dataset Testing

Multisite Testing

---

############################################################

TEST DATASETS

############################################################

Small Site

100 posts

Medium Site

10,000 resources

Large Site

100,000 resources

Enterprise Site

1,000,000 resources

WooCommerce Store

100,000 products and variations

---

############################################################

FAILURE CONDITIONS

############################################################

The plugin must never

Exhaust PHP memory

Lock the database

Block public page rendering

Run an unlimited crawl

Create endless cron jobs

Retry failed jobs forever

Generate unbounded logs

Load entire reports into memory

---

############################################################

FUTURE FEATURES

############################################################

Adaptive Batch Sizing

Machine Learning Optimization

Distributed Queues

Cloud Workers

Edge Validation

Remote Crawlers

Automatic Query Optimization

Predictive Caching

Serverless Report Generation

Enterprise Performance Center

---

# Success Criteria

Oxy AI Readiness must remain responsive and resource-efficient across websites of all sizes.

Long-running tasks must execute asynchronously.

Repeated work must be avoided through incremental processing and intelligent caching.

The plugin must have no measurable negative impact on normal frontend page delivery.

Performance must be observable, testable and governed by explicit budgets.