# Oxy AI Readiness

# AI Audit Engine

Version 1.1

---

> **Canonical note (ADR-005):** The "AI Readiness Score" section below now references the single canonical Score/Grade/Label table defined in `docs/15-Scoring-Engine.md`'s GRADE SYSTEM section. See `.project/adr/ADR-005-scoring-grading.md`.

---

# Vision

The Audit Engine is the intelligence core of Oxy AI Readiness.

Its mission is not simply to scan a website.

Its mission is to determine whether an AI system can:

Discover

Understand

Trust

Access

Consume

Interact with

the website.

---

# Core Objectives

The engine must:

Scan

Validate

Analyze

Score

Recommend

Auto Fix

Verify

Report

---

# Scan Categories

The engine evaluates the website across multiple categories.

Discovery

↓

Content

↓

Infrastructure

↓

Standards

↓

Security

↓

Performance

↓

Future AI Compatibility

---

# Scan Flow

Initialize

↓

Load Rules

↓

Load Modules

↓

Run Scanners

↓

Collect Results

↓

Calculate Score

↓

Generate Report

↓

Generate Recommendations

↓

Generate Auto Fixes

↓

Complete

---

# Scan Types

Quick Scan

Less than 5 seconds

----------------

Full Scan

Everything

----------------

Deep Scan

Includes API

Performance

Security

Headers

Future Standards

----------------

Developer Scan

Verbose

JSON Output

Logs

---

# Rule Engine

Every check is a Rule.

Rules are independent.

Every rule returns:

PASS

WARNING

FAIL

INFO

SKIPPED

---

# Rule Object

Each rule contains:

ID

Title

Description

Category

Severity

Impact

Priority

Documentation

Auto Fix Available

Estimated Fix Time

Estimated SEO Impact

Estimated AI Impact

Dependencies

---

# Severity Levels

Critical

High

Medium

Low

Info

---

# Priority

P1

Immediate

P2

Today

P3

Soon

P4

Future

---

# Categories

Discovery

Headers

Robots

Markdown

LLMS

API

Schema

Metadata

Performance

Security

Commerce

MCP

OAuth

Content

Accessibility

Monitoring

Analytics

Future Standards

---

#######################################################

DISCOVERY CHECKS

#######################################################

robots.txt exists

robots syntax

robots accessible

robots AI rules

robots conflicts

robots crawl delay

robots host

robots sitemap

llms.txt exists

auth.md exists

humans.txt

ai.txt

well-known directory

api-catalog

mcp card

agent skills

dns aid

canonical

alternate

hreflang

---

#######################################################

HEADER CHECKS

#######################################################

Link

Vary

Content Type

Cache

Content Signal

Server

Security Headers

CSP

Permissions Policy

X Frame Options

Referrer Policy

HSTS

---

#######################################################

CONTENT CHECKS

#######################################################

Title

Meta Description

Canonical

OpenGraph

Twitter

Schema

JSON-LD

Headings

Images

Alt

Structured Content

Internal Links

External Links

Markdown Output

Semantic HTML

---

#######################################################

AI CHECKS

#######################################################

Markdown Negotiation

LLMS

Discovery

Prompt Injection Protection

Context Window

Chunk Size

Agent Friendly URLs

Content Signals

Knowledge Graph

Future Standards

---

#######################################################

PERFORMANCE CHECKS

#######################################################

TTFB

Compression

Caching

Minification

HTTP2

HTTP3

Lazy Load

Image Optimization

Critical CSS

---

#######################################################

SECURITY CHECKS

#######################################################

HTTPS

Mixed Content

Security Headers

Directory Listing

REST Exposure

Debug Mode

Version Exposure

Sensitive Files

XMLRPC

Login Protection

---

#######################################################

WORDPRESS CHECKS

#######################################################

Version

PHP

MySQL

Plugins

Theme

REST

Permalinks

Cron

Uploads

Media

Users

Roles

Capabilities

---

#######################################################

SCORE ENGINE

#######################################################

Every rule contributes to a weighted score.

Critical

20 points

High

10 points

Medium

5 points

Low

2 points

Info

0

---

# AI Readiness Score

The AI Readiness Score uses the single canonical Score/Grade/Label table defined in
docs/15-Scoring-Engine.md's GRADE SYSTEM section (ADR-005):

98-100 A+ Excellent

95-97 A Excellent

90-94 A- Excellent

85-89 B+ Advanced

80-84 B Advanced

75-79 B- Advanced

70-74 C+ Good

60-69 C Good

40-59 D Basic

0-39 F Poor

---

# Confidence Score

Measures confidence in the audit.

Low

Medium

High

Very High

---

# Health Indicators

Discovery Health

Content Health

Infrastructure Health

Security Health

Performance Health

Future Compatibility

---

# Recommendation Engine

Every issue produces:

Problem

Reason

Impact

Recommendation

Documentation

Auto Fix

Estimated Time

Priority

---

Example

Problem

Markdown Negotiation Missing

Reason

AI systems cannot request Markdown output.

Impact

High

Recommendation

Enable Markdown Module.

Auto Fix

Available

Time

10 seconds

---

# Auto Fix Engine

Every fix has:

Safe

Requires Confirmation

Developer Only

Unsupported

---

Auto Fix Workflow

Issue

↓

Validate

↓

Backup

↓

Apply Fix

↓

Verify

↓

Success

---

# Verification

Every Auto Fix is verified immediately.

If verification fails:

Rollback

↓

Log

↓

Notify User

---

# Reporting

Reports include:

Summary

AI Score

Health Score

Timeline

Charts

Recommendations

Export

---

# Export Formats

PDF

HTML

Markdown

CSV

JSON

---

# Historical Scans

Keep history.

Daily

Weekly

Monthly

Compare scans.

---

# Diff Engine

Compare

Today

↓

Yesterday

↓

Last Week

↓

Last Month

Display:

Improved

Regressed

Unchanged

---

# Notifications

Notify when:

Score drops

Critical issue found

Auto Fix fails

Module disabled

Discovery broken

---

# API

GET

/audit

GET

/audit/history

POST

/audit/start

POST

/audit/fix

POST

/audit/verify

---

# Logging

Every action logged.

Scan

Fix

Rollback

Verification

Failure

Success

---

# Performance

Quick Scan

<5 seconds

Full Scan

<20 seconds

Deep Scan

<60 seconds

---

# Extensibility

Third-party developers can register custom rules.

Example

add_action(

'oxy_ai_register_rules',

...

);

---

# Final Principle

The Audit Engine should answer:

Can AI discover this website?

Can AI understand it?

Can AI trust it?

Can AI use it?

Can AI interact with it?

Can AI recommend it?

If the answer is not "Yes"...

The engine must explain why and provide the fastest path to fix it.