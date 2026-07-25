# Oxy AI Readiness

# Module Specifications

Version 1.1

---

> **Canonical note (ADR-001):** A Module is the WordPress integration shell (UI, REST, settings, permissions, lifecycle). Modules that implement a published, externally-versioned AI specification additionally register a `StandardInterface` descriptor with the Standards Registry (see `docs/23-AI-Standards-Layer.md`) — the Standard delegates to the same Generator/Validator/ScoreProvider/Monitor/Reporter the Module already registers; it never duplicates that logic. See `.project/adr/ADR-001-modules-vs-standards.md` for the full decision and the Module→Standard ownership table.

---

# Philosophy

Everything inside Oxy AI Readiness is a Module.

Nothing is hardcoded.

Every feature can be:

Enabled

Disabled

Installed

Updated

Removed

without affecting the rest of the plugin.

---

# Module Lifecycle

Register

↓

Boot

↓

Initialize

↓

Load Assets

↓

Load REST Routes

↓

Register Hooks

↓

Ready

↓

Shutdown

---

# Module Interface

Every module MUST implement:

ModuleInterface

Methods

register()

boot()

init()

assets()

routes()

settings()

permissions()

audit()

shutdown()

---

##########################################################

MODULE

Dashboard

##########################################################

Purpose

Main control center.

Responsibilities

Display AI Score

Display Quick Actions

Display Module Status

Display Recent Activity

Display Notifications

Display Recommendations

Services

DashboardService

DashboardController

DashboardRepository

DashboardWidget

REST

/dashboard

/dashboard/widgets

/dashboard/score

/dashboard/activity

Widgets

AI Score

Quick Actions

Latest Scan

Warnings

Recommendations

Performance

Buttons

Run Audit

Auto Fix

Generate Files

Export Report

---

##########################################################

MODULE

Audit

##########################################################

Purpose

Scan the website.

Responsibilities

Run Checks

Calculate Score

Generate Report

Recommend Fixes

Services

AuditEngine

Scanner

RuleRunner

IssueDetector

ScoreCalculator

ReportBuilder

Checks

robots.txt

headers

markdown

llms

auth

schema

canonical

og

twitter

jsonld

mcp

oauth

api

dns

security

performance

REST

/audit/start

/audit/results

/audit/history

/audit/fix

Events

AuditStarted

AuditFinished

ScoreCalculated

IssueDetected

---

##########################################################

MODULE

Robots

##########################################################

Purpose

Manage robots.txt

Responsibilities

Generate robots

Validate robots

Merge robots

Detect conflicts

Builder

Visual Builder

Preview

Validate

Export

Supported Crawlers

GPTBot

ClaudeBot

Claude-SearchBot

Claude-User

Google-Extended

ChatGPT-User

OAI-SearchBot

PerplexityBot

AppleBot

BingBot

Meta External Agent

Future AI Crawlers

REST

/robots

/robots/save

/robots/validate

Audit

Check existence

Syntax

AI Rules

Conflicts

---

##########################################################

MODULE

LLMS

##########################################################

Purpose

Generate llms.txt

Responsibilities

Auto Generate

Edit

Preview

Validate

Builder

Visual Editor

Markdown Preview

REST

/llms

/generate-llms

Audit

Exists

Readable

Correct Format

---

##########################################################

MODULE

Markdown

##########################################################

Purpose

Provide Markdown Negotiation.

Responsibilities

HTML

↓

Markdown

Support

Pages

Posts

Products

Elementor

Gutenberg

Classic Editor

Settings

Enable

Negotiation

Cache

Preview

REST

/markdown

/render

Audit

Negotiation

Headers

Output

---

##########################################################

MODULE

Headers

##########################################################

Purpose

Manage HTTP Headers.

Headers

Link

Vary

Content-Type

Content-Signal

AI Headers

Security Headers

REST

/headers

Audit

Missing

Invalid

Duplicate

---

##########################################################

MODULE

Content Signals

##########################################################

Purpose

Generate AI Signals.

Supports

Training

Search

Input

Output

Inference

Future Standards

Audit

Presence

Validation

---

##########################################################

MODULE

Discovery

##########################################################

Purpose

Generate AI Discovery Files.

Files

llms.txt

auth.md

ai.txt

humans.txt

robots.txt

well-known

Audit

Every file

Status

Health

---

##########################################################

MODULE

API Catalog

##########################################################

Purpose

Generate

/.well-known/api-catalog

Responsibilities

Scan REST API

Generate Catalog

Validate

Export

REST

/api-catalog

Audit

Exists

Schema

Routes

---

##########################################################

MODULE

MCP

##########################################################

Purpose

Model Context Protocol

Responsibilities

Generate

Server Card

Metadata

Capabilities

Resources

Audit

Exists

Schema

Validation

---

##########################################################

MODULE

OAuth Discovery

##########################################################

Purpose

Generate

OAuth Discovery

Files

openid-configuration

oauth-authorization-server

oauth-protected-resource

Audit

Existence

Correct JSON

Security

---

##########################################################

MODULE

Agent Skills

##########################################################

Purpose

Publish AI Skills.

Responsibilities

Skill Generator

JSON Schema

Validation

Marketplace Ready

REST

/agent-skills

Audit

Exists

Schema

---

##########################################################

MODULE

Commerce

##########################################################

Purpose

Future AI Commerce.

Supports

x402

Machine Payments

AI Checkout

Agent Purchases

AI Transactions

Audit

Standards

---

##########################################################

MODULE

Analytics

##########################################################

Purpose

Track AI Usage.

Metrics

AI Crawlers

Visits

Markdown Requests

LLMS Requests

Agent Requests

Charts

Daily

Weekly

Monthly

---

##########################################################

MODULE

Monitoring

##########################################################

Purpose

Health Monitoring.

Detect

Broken Headers

Missing Files

Deleted Files

Changed Robots

Notify Admin

---

##########################################################

MODULE

Reports

##########################################################

Purpose

Generate Reports.

Formats

PDF

HTML

JSON

CSV

Markdown

---

##########################################################

MODULE

Settings

##########################################################

Purpose

Global Configuration.

Tabs

General

Discovery

Headers

Markdown

Performance

Security

Advanced

Developer

Import

Export

Reset

---

##########################################################

MODULE

Logs

##########################################################

Purpose

Debugging.

Levels

Info

Warning

Error

Critical

Debug

Export

Clear

Search

Filter

---

##########################################################

MODULE

License

##########################################################

Purpose

Commercial Version.

Supports

Activation

Updates

Subscriptions

Agency

Enterprise

Offline Validation

---

##########################################################

MODULE

Updater

##########################################################

Purpose

Automatic Updates.

Channels

Stable

Beta

Nightly

Rollback

---

##########################################################

Future Modules

AI SEO

AI Content

AI Sitemap

AI Assistant

AI Chat Widget

AI Agents

Cloud Dashboard

Marketplace

White Label

AI Security

AI Monitor

AI Performance

Remote Sync

Enterprise Console

No architectural changes should be required to add these modules.

---

# Final Rule

Every Module owns:

Services

Controllers

Repositories

Routes

Views

Tests

Assets

Configuration

Events

Documentation

No module should directly manipulate another module's internals.

All communication must happen through:

Interfaces

Events

Service Container