# Oxy AI Readiness

# LLMS Module Specification

Version 1.0

---

# Purpose

The LLMS Module is responsible for generating, maintaining and validating the llms.txt file.

The module should automatically create an optimized AI-readable representation of the website.

Unlike a simple text editor, this module should intelligently understand the website structure and expose the most valuable content for AI systems.

---

# Vision

The llms.txt file should become the AI homepage of the website.

Instead of forcing AI systems to crawl thousands of pages, Oxy should guide them toward the highest-quality content.

---

# Responsibilities

Generate llms.txt

Auto Update

Validate

Preview

Export

Multi-language

Collections

Priority Rules

Version Control

Health Monitoring

---

# Supported Standards

Current llms.txt

Future llms.txt versions

Markdown Compatible

UTF-8

RFC Compatible

---

# Content Sources

Pages

Posts

Products

Categories

Tags

Documentation

Knowledge Base

FAQs

Tutorials

Landing Pages

Custom Post Types

Elementor Templates

WooCommerce

EDD

LifterLMS

LearnDash

---

# Smart Discovery

Automatically detect

About Page

Contact Page

Privacy Policy

Terms

Documentation

API Docs

Knowledge Base

FAQ

Support

Pricing

Blog

Services

Products

Categories

Authors

---

# Automatic Prioritization

Every page receives a priority score.

Factors

Traffic

Internal Links

Backlinks

Freshness

Content Length

Structured Data

Schema

SEO Score

AI Score

Manual Priority

---

# Priority Levels

Critical

High

Medium

Low

Hidden

---

# File Structure

Title

Description

Base URL

Language

Organization

Contact

Sections

Resources

Documentation

Products

Articles

Support

---

# Dynamic Sections

Documentation

Products

Articles

FAQs

Services

Categories

Tutorials

Guides

Resources

Downloads

---

# Multi-language Support

English

Arabic

French

German

Spanish

Italian

Japanese

Chinese

Korean

Auto Detect

WPML

Polylang

TranslatePress

Multisite

---

# Builder

Visual Builder

Drag & Drop

Enable

Disable

Reorder

Rename

Collapse

Expand

Preview

---

# Smart Recommendations

Detect

Duplicate Pages

Low Quality Pages

Thin Content

Broken URLs

Redirect Chains

404 Pages

Orphan Pages

Missing Metadata

---

# Validation

Check

Encoding

Formatting

Required Fields

Duplicate Entries

Broken Links

Response Time

Content Length

Invalid Markdown

---

# Version Control

Every generation creates

Snapshot

Timestamp

Editor

Changes

Rollback Support

---

# Auto Generation Modes

Manual

Daily

Weekly

Monthly

After Publishing

After Updating

After Deleting

---

# Live Preview

Desktop

Mobile

Raw Markdown

Rendered Markdown

Word Count

Character Count

Link Count

---

# Filters

Exclude Categories

Exclude Tags

Exclude Authors

Exclude Products

Exclude Drafts

Exclude Password Protected

Exclude Private Pages

---

# Search

Find by

Title

URL

Slug

Category

Language

Priority

---

# Templates

Corporate Website

Medical Website

Law Firm

University

Agency

Blog

WooCommerce

Documentation

Developer Portal

---

# REST Endpoints

GET

/llms

GET

/llms/preview

GET

/llms/history

POST

/llms/generate

POST

/llms/save

POST

/llms/validate

POST

/llms/reset

POST

/llms/export

DELETE

/llms/version/{id}

---

# Audit Rules

llms.txt exists

HTTP 200

Correct MIME Type

UTF-8

No Broken Links

Valid Markdown

Sections Present

Organization Present

Description Present

Readable

Accessible

Fresh Content

No Empty Sections

---

# UI Layout

Top Cards

Health

Pages

Resources

Languages

Last Generated

Main Sections

Builder

Preview

Validation

History

Templates

Settings

---

# Builder Components

Content Sources

Priority

Ordering

Visibility

Language

Custom Sections

Metadata

---

# Settings

Automatic Generation

Cache

Compression

Preview Theme

Language

Maximum Links

Maximum Pages

Priority Strategy

---

# Performance

Maximum generation

3 seconds

Validation

1 second

Preview

Instant

Caching

Enabled

---

# Security

Capability Validation

Nonce

Sanitization

Escaping

Rate Limiting

Audit Logs

---

# Accessibility

Keyboard Navigation

Screen Readers

High Contrast

ARIA Labels

---

# Events

LLMSGenerated

LLMSSaved

LLMSValidated

LLMSReset

LLMSExported

SectionAdded

SectionRemoved

PriorityChanged

---

# Filters

oxy_ai_llms_before_generate

oxy_ai_llms_after_generate

oxy_ai_llms_sections

oxy_ai_llms_priority

oxy_ai_llms_output

---

# Future Features

AI Content Ranking

Automatic Summaries

Topic Clustering

Knowledge Graph Export

Citation Mapping

LLM Optimization Suggestions

AI Confidence Score

Semantic Sections

---

# Success Criteria

A website owner should be able to generate a complete, standards-compliant llms.txt file in one click.

The generated file should always prioritize the most valuable content for AI systems while remaining human-readable, extensible and automatically maintained.