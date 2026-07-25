# Oxy AI Readiness

# Robots Module Specification

Version 1.0

---

# Purpose

The Robots Module manages everything related to robots.txt.

Its mission is to provide a modern visual interface for managing search engine crawlers, AI crawlers, future AI agents, and custom rules while ensuring compatibility with WordPress and existing SEO plugins.

The module should never overwrite user data without confirmation.

---

# Responsibilities

Generate robots.txt

Read existing robots.txt

Detect conflicts

Merge rules

Validate syntax

Generate AI directives

Generate Sitemap lines

Generate Host lines

Manage custom directives

Support multisite

Support multilingual websites

---

# Features

Visual Robots Builder

Live Preview

Syntax Validation

AI Crawlers Manager

Classic Crawlers Manager

Export

Import

Backup

Restore

Version History

One Click Restore

Conflict Detection

Auto Merge

---

# Supported Modes

Mode 1

WordPress Virtual Robots

-------------------

Mode 2

Physical robots.txt

-------------------

Mode 3

Cloudflare Compatible

-------------------

Mode 4

Reverse Proxy

-------------------

Mode 5

Read Only

---

# Supported Crawlers

Googlebot

Googlebot-Image

Googlebot-News

Googlebot-Video

AdsBot-Google

Mediapartners-Google

Google-Extended

Bingbot

BingPreview

DuckDuckBot

YandexBot

BaiduSpider

Applebot

FacebookBot

Twitterbot

LinkedInBot

PinterestBot

Slackbot

Discordbot

WhatsApp

TelegramBot

GPTBot

ChatGPT-User

OAI-SearchBot

ClaudeBot

Claude-SearchBot

Claude-User

PerplexityBot

CCBot

Amazonbot

Bytespider

Meta-ExternalAgent

Meta-ExternalFetcher

Future AI Crawlers

Custom Crawlers

---

# Robots Builder

Visual Rule Builder

Each rule contains

User Agent

Allow

Disallow

Crawl Delay

Host

Sitemap

Priority

Enabled

Comment

---

# Rule Validation

Every rule validated before saving.

Validation checks

Duplicate User Agent

Invalid Path

Invalid Wildcards

Conflicting Rules

Invalid Host

Invalid Sitemap

Invalid Encoding

---

# Live Preview

Generated robots.txt displayed instantly.

Line numbers

Syntax highlighting

Error highlighting

Download

Copy

Compare

---

# Version Control

Every save creates a snapshot.

User can restore any version.

Version contains

Date

User

Changes

Reason

---

# Auto Backup

Backup before every modification.

Backups stored inside

storage/backups/robots/

---

# Merge Engine

Detect plugins

Rank Math

Yoast SEO

All In One SEO

SEOPress

The SEO Framework

Jetpack

Merge rules safely.

Never duplicate entries.

---

# AI Templates

Template

Allow AI

-----------------

Template

Block AI

-----------------

Template

Research Friendly

-----------------

Template

Maximum Privacy

-----------------

Template

Developer

---

# Audit Rules

robots.txt exists

robots readable

HTTP 200

Correct Content-Type

UTF-8

No syntax errors

Sitemap exists

Host exists

AI directives present

No duplicate user agents

No duplicate paths

No conflicts

---

# REST Endpoints

GET

/robots

GET

/robots/history

GET

/robots/preview

POST

/robots/save

POST

/robots/restore

POST

/robots/validate

POST

/robots/reset

DELETE

/robots/version/{id}

---

# Admin UI

Sidebar

Robots

Top Cards

Health

Version

AI Rules

Warnings

Main Layout

Builder

Preview

Validation

History

Templates

---

# Builder Table

Columns

Enabled

User Agent

Allow

Disallow

Delay

Host

Comment

Actions

---

# Toolbar

New Rule

Import

Export

Validate

Generate

Restore

History

Settings

---

# Import Formats

TXT

JSON

CSV

---

# Export Formats

TXT

JSON

CSV

Markdown

---

# Permissions

manage_options

manage_robots

developer_mode

---

# Events

RobotsGenerated

RobotsSaved

RobotsValidated

RobotsRestored

RuleAdded

RuleRemoved

RuleUpdated

ConflictDetected

---

# Filters

oxy_ai_robot_rules

oxy_ai_robot_content

oxy_ai_robot_validate

oxy_ai_robot_before_save

oxy_ai_robot_after_save

---

# Performance

Maximum validation time

1 second

Maximum save time

2 seconds

Preview generation

Instant

---

# Security

Nonce verification

Capability check

Input sanitization

Path validation

Rate limiting

CSRF protection

Audit logging

---

# Accessibility

Keyboard support

ARIA labels

Screen reader friendly

Color contrast AA

---

# Future Support

Robots AI Extensions

Dynamic Rules

Geo Rules

Time Based Rules

Conditional Rules

API Controlled Rules

Cloud Synchronization

Remote Deployment

---

# Success Criteria

The module must allow a beginner to create a valid robots.txt in less than two minutes without editing raw text while giving advanced users full control over every directive.