# Oxy AI Readiness

# Markdown Module Specification

Version 1.0

---

# Purpose

The Markdown Module provides an AI-optimized representation of WordPress content.

Instead of forcing AI systems to parse complex HTML,
the module generates clean, semantic, standards-compliant Markdown.

The generated Markdown must preserve the meaning of the original page while removing unnecessary presentation markup.

---

# Vision

Every page on the website should have a high-quality Markdown representation.

This representation should become the preferred format for AI systems.

The conversion should be automatic, accurate and cacheable.

---

# Responsibilities

Generate Markdown

Serve Markdown

Validate Markdown

Cache Markdown

Optimize Markdown

Preview Markdown

Export Markdown

Monitor Markdown Health

---

# Supported Sources

Pages

Posts

Products

Categories

Tags

Archives

Authors

Custom Post Types

Elementor

Gutenberg

Classic Editor

WooCommerce

LearnDash

LifterLMS

EDD

---

# Supported Content

Paragraphs

Headings

Lists

Tables

Images

Code Blocks

Quotes

Horizontal Rules

Videos

Audio

Downloads

Buttons

Cards

Accordions

Tabs

FAQ

Timeline

Pricing Tables

Forms (optional)

---

# HTML Conversion

Convert

<h1>

↓

#

-------------------

<h2>

↓

##

-------------------

<ul>

↓

-

-------------------

<ol>

↓

1.

-------------------

<blockquote>

↓

>

-------------------

<strong>

↓

**

-------------------

<em>

↓

*

-------------------

<a>

↓

[]

()

-------------------

<img>

↓

![]

---

# Smart Cleaning

Remove

Inline Styles

Unused Classes

JavaScript

Tracking Attributes

Animation Classes

Framework Classes

Builder Artifacts

Editor Metadata

Empty Containers

---

# Preserve

Headings

Content Order

Links

Image ALT

Captions

Lists

Tables

Quotes

Footnotes

Code Blocks

References

Metadata

---

# Elementor Support

Extract

Text

Images

Headings

Containers

Buttons

Accordions

Tabs

Testimonials

Counters

FAQs

Call To Actions

Pricing

Icon Lists

Progress Bars

---

# Gutenberg Support

Core Blocks

Columns

Groups

Media

Gallery

Quote

Table

Code

Embed

Buttons

Cover

---

# WooCommerce Support

Product Title

Short Description

Long Description

Specifications

Attributes

Price (Optional)

Availability

SKU (Optional)

FAQ

Reviews (Optional)

Downloads

---

# AI Optimization

Improve Heading Hierarchy

Remove Visual Noise

Normalize Lists

Flatten Layout

Improve Readability

Generate Section Anchors

Optimize Link Labels

Detect Repeated Content

Normalize Whitespace

---

# Metadata

Title

Description

Canonical

Author

Published

Updated

Language

Reading Time

Word Count

Tags

Categories

---

# Front Matter

Optional YAML Support

Example

Title

Author

Updated

Language

Canonical

Description

---

# URL Strategy

/page/

↓

/page/?output=markdown

or

/page.md

or

Accept:

text/markdown

Negotiation configurable.

---

# REST API

GET

/markdown

GET

/markdown/page/{id}

GET

/markdown/post/{id}

POST

/markdown/regenerate

POST

/markdown/cache

POST

/markdown/clear

---

# Content Negotiation

Supported

text/markdown

text/plain

text/html

application/json

Priority configurable.

---

# Cache

Memory

Transient

File Cache

Redis

Object Cache

Expiration configurable.

---

# Chunking

Automatic Section Detection

Maximum Chunk Size

Maximum Tokens

Semantic Splitting

Heading Splitting

Paragraph Splitting

---

# Images

Preserve ALT

Preserve Caption

Optional Width

Optional Height

Lazy Attributes Removed

Tracking Removed

---

# Links

Internal

External

Anchor

Relative

Absolute

Broken Link Detection

---

# Tables

Convert HTML Tables

↓

Markdown Tables

Alignment Preserved

Headers Preserved

---

# Code Blocks

Language Detection

Syntax Preservation

Markdown Fence

Optional Highlight Metadata

---

# Footnotes

Support

Markdown Footnotes

Automatic Numbering

Back References

---

# Validation

UTF-8

Broken Links

Empty Sections

Invalid Markdown

Duplicate Anchors

Invalid Images

Invalid Tables

Invalid Headings

---

# Preview

Raw Markdown

Rendered Markdown

Split View

Desktop

Mobile

Dark Mode

---

# Audit Rules

Markdown Available

HTTP 200

Correct Content-Type

Negotiation Enabled

Readable

No Empty Output

No Invalid Markdown

Response Time

Cached

---

# UI

Overview

Statistics

Content Sources

Preview

Validation

History

Settings

Export

---

# Statistics

Pages Generated

Cache Size

Average Response Time

Word Count

Broken Pages

Conversion Errors

---

# Settings

Automatic Generation

Automatic Cache

Negotiation

Front Matter

Image Handling

Table Handling

Chunk Size

Cache Duration

---

# Export

Markdown

ZIP

JSON

CSV Index

---

# Events

MarkdownGenerated

MarkdownUpdated

MarkdownDeleted

MarkdownCached

MarkdownValidated

MarkdownExported

---

# Filters

oxy_ai_markdown_before_generate

oxy_ai_markdown_after_generate

oxy_ai_markdown_content

oxy_ai_markdown_output

oxy_ai_markdown_headers

---

# Security

Nonce Validation

Capabilities

Sanitization

Escaping

Rate Limiting

Access Logs

---

# Accessibility

Keyboard Navigation

Screen Readers

WCAG AA

High Contrast

---

# Performance Targets

Generation

<300ms

Cached Response

<20ms

Large Pages

<1 second

---

# Future Features

Streaming Markdown

Incremental Rendering

Semantic Compression

AI Summaries

Topic Maps

Entity Extraction

Citation Generation

Knowledge Graph Export

Vector Embedding Support

RAG Export

---

# Success Criteria

Every published page should be convertible into high-quality Markdown without losing semantic meaning.

The generated Markdown should be clean, deterministic, AI-friendly and significantly easier for language models to consume than raw HTML.