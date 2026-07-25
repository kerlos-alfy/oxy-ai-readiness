# Oxy AI Readiness

# Discovery Engine

Version 1.0

---

# Purpose

The Discovery Engine is responsible for automatically discovering every AI-related capability exposed by the website.

It acts as the central intelligence layer that scans, indexes and maps all AI resources before they are validated, scored or reported.

Instead of every module implementing its own discovery logic, all modules rely on the Discovery Engine.

---

# Vision

The Discovery Engine should understand a website exactly as an AI crawler would.

It should answer:

What AI resources exist?

Where are they?

Are they reachable?

Are they valid?

Are they connected?

---

# Responsibilities

Resource Discovery

Capability Discovery

Endpoint Discovery

File Discovery

Header Discovery

Schema Discovery

Module Discovery

Plugin Discovery

Theme Discovery

Server Discovery

Future Standard Discovery

---

# Discovery Categories

Website

↓

Server

↓

WordPress

↓

Modules

↓

Files

↓

Endpoints

↓

Headers

↓

Schemas

↓

AI Standards

---

##################################################

RESOURCE DISCOVERY

##################################################

robots.txt

llms.txt

auth.md

humans.txt

ai.txt

api-catalog

MCP

Agent Skills

Markdown

OpenAPI

RSS

Atom

Sitemap

JSON Feed

Manifest

favicon

well-known

---

##################################################

HEADER DISCOVERY

##################################################

Link

Content-Type

Vary

Content-Signal

ETag

Last-Modified

Cache-Control

Server-Timing

Security Headers

Future Headers

---

##################################################

ENDPOINT DISCOVERY

##################################################

REST API

Custom REST

GraphQL

WooCommerce API

Elementor API

Future APIs

---

##################################################

PLUGIN DISCOVERY

##################################################

Rank Math

Yoast

SEOPress

Elementor

WooCommerce

WP Rocket

LiteSpeed

Cloudflare

Custom Plugins

---

##################################################

SERVER DISCOVERY

##################################################

Apache

Nginx

LiteSpeed

OpenLiteSpeed

IIS

Cloudflare

Fastly

Varnish

Reverse Proxy

HTTP Version

Compression

---

##################################################

AI DISCOVERY

##################################################

MCP

Agent Skills

LLMS

Markdown

OAuth Discovery

API Catalog

Discovery Documents

AI Signals

Future Standards

---

# Discovery Pipeline

Initialize

↓

Module Scan

↓

Website Scan

↓

Headers Scan

↓

Files Scan

↓

Endpoint Scan

↓

Validation Queue

↓

Result Cache

↓

Discovery Map

---

# Discovery Map

Every discovered object contains

ID

Type

Location

Status

Version

Module

Health

Dependencies

Source

Last Checked

---

# REST API

GET

/discovery

GET

/discovery/map

GET

/discovery/resources

GET

/discovery/modules

POST

/discovery/scan

POST

/discovery/reset

---

# Audit Rules

Discovery Engine Loaded

Resources Found

Files Reachable

Headers Reachable

Endpoints Reachable

Dependencies Valid

---

# Events

DiscoveryStarted

DiscoveryFinished

ResourceDiscovered

ResourceUpdated

DiscoveryFailed

---

# Performance

Quick Discovery

<500ms

Deep Discovery

<5 seconds

Incremental Discovery

<100ms

---

# Security

Read Only

Capability Checks

Audit Logs

Permission Validation

---

# Future Features

Distributed Discovery

Cloud Discovery

Remote Discovery

AI Network Discovery

Federated Discovery

Automatic Standard Detection

---

# Success Criteria

Every AI resource exposed by the website should be automatically discovered, classified and made available to the rest of the platform through a unified Discovery Map.

The Discovery Engine must become the single source of truth for every module that needs to know what AI capabilities the website exposes.