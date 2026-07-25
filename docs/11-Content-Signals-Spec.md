# Oxy AI Readiness

# AI Content Signals Module Specification

Version 1.0

---

# Purpose

The Content Signals Module generates machine-readable semantic metadata that helps AI systems understand the purpose, quality and context of website content.

Instead of requiring AI systems to infer meaning from HTML alone, Oxy exposes explicit content signals.

These signals improve discoverability, trust and contextual understanding.

---

# Vision

Every page should describe itself.

An AI system should immediately know:

What this page is.

Who it is for.

How trustworthy it is.

When it was updated.

Why it exists.

Whether it should be cited.

Whether it should be indexed.

Whether it can be used for AI training.

---

# Responsibilities

Generate Signals

Validate Signals

Monitor Signals

Visual Editor

Templates

Auto Detection

Versioning

Export

REST API

Audit

---

# Signal Categories

Identity

↓

Purpose

↓

Audience

↓

Trust

↓

Freshness

↓

Knowledge

↓

AI Usage

↓

Compliance

---

####################################################

IDENTITY SIGNALS

####################################################

Content Type

Topic

Sub Topic

Industry

Language

Country

Region

Organization

Department

Author

Brand

Website

---

####################################################

PURPOSE SIGNALS

####################################################

Article

Landing Page

Documentation

Guide

Tutorial

FAQ

Support

Product

Service

Knowledge Base

Policy

News

Research

Blog

---

####################################################

AUDIENCE SIGNALS

####################################################

Consumers

Businesses

Developers

Researchers

Doctors

Students

Teachers

Engineers

Patients

Lawyers

Children

Parents

Professionals

General Public

---

####################################################

EXPERTISE SIGNALS

####################################################

Expert Reviewed

Medical Reviewed

Legal Reviewed

Editorial Reviewed

Fact Checked

Peer Reviewed

Verified

Original Content

First-Hand Experience

---

####################################################

FRESHNESS SIGNALS

####################################################

Published Date

Updated Date

Review Date

Expiration Date

Review Frequency

Content Status

Active

Archived

Draft

Deprecated

---

####################################################

TRUST SIGNALS

####################################################

References

Citations

Sources

Official Organization

Contact Available

Privacy Policy

Terms

Verified Author

Verified Organization

Evidence Level

Confidence Score

---

####################################################

KNOWLEDGE SIGNALS

####################################################

Entities

Topics

Keywords

Concepts

Related Topics

Knowledge Graph ID

Wikipedia

Wikidata

Internal References

External References

---

####################################################

READABILITY SIGNALS

####################################################

Reading Time

Word Count

Reading Level

Difficulty

Paragraph Count

Heading Count

Table Count

Image Count

---

####################################################

MEDIA SIGNALS

####################################################

Images

Videos

Audio

PDF

Downloads

Interactive

Infographics

3D

---

####################################################

AI USAGE SIGNALS

####################################################

AI Training

Allowed

Restricted

Prohibited

AI Citation

Preferred

Optional

Disabled

AI Summary

Allowed

AI Translation

Allowed

Embedding

Allowed

Inference

Allowed

---

####################################################

CONTENT QUALITY

####################################################

Uniqueness

Completeness

Depth

Accuracy

Authority

Coverage

Consistency

Originality

---

####################################################

COMPLIANCE

####################################################

GDPR

CCPA

HIPAA

Accessibility

Copyright

Licensing

Medical Disclaimer

Legal Disclaimer

Financial Disclaimer

---

# Automatic Detection

Automatically detect

Article Type

Audience

Topic

Entity

Schema

Language

Reading Time

Reading Level

Content Quality

Duplicate Content

---

# Manual Override

Every automatically detected value can be edited manually.

---

# Templates

Medical

Law Firm

Agency

Corporate

University

Documentation

WooCommerce

Blog

Portfolio

Government

---

# Builder

Visual Card Interface

Categories

Signals

Validation

Preview

History

Templates

---

# Preview

JSON

HTML

Markdown

Human View

AI View

---

# Validation

Missing Fields

Conflicting Signals

Invalid Values

Duplicate Signals

Outdated Information

Unknown Types

---

# Audit Rules

Signals Present

Identity Complete

Purpose Defined

Audience Defined

Trust Signals Present

Freshness Available

References Present

Entities Available

Knowledge Complete

---

# REST API

GET

/content-signals

GET

/content-signals/{id}

POST

/content-signals/save

POST

/content-signals/generate

POST

/content-signals/validate

POST

/content-signals/reset

---

# Dashboard

Signal Coverage

Trust Score

Knowledge Score

Freshness

Entity Count

Citation Count

Missing Signals

Recommendations

---

# Recommendations

Missing Audience

Missing Expertise

Missing Sources

Missing References

Missing Knowledge Graph

Low Freshness

Weak Authority

---

# Version History

Every update stores

Editor

Timestamp

Difference

Rollback

---

# Events

SignalsGenerated

SignalsValidated

SignalsUpdated

SignalsDeleted

TrustChanged

KnowledgeUpdated

AudienceChanged

---

# Filters

oxy_ai_content_signals_before_generate

oxy_ai_content_signals_after_generate

oxy_ai_content_signals_validate

oxy_ai_content_signals_output

---

# Performance

Generation

<500ms

Validation

<100ms

Preview

Instant

---

# Security

Nonce

Capabilities

Sanitization

Escaping

Audit Logs

Permission Validation

---

# Accessibility

Keyboard Navigation

Screen Reader Friendly

High Contrast

ARIA Labels

WCAG AA

---

# Future Features

Automatic Entity Extraction

Knowledge Graph Generation

Citation Builder

Evidence Scoring

Authority Index

Semantic Topic Maps

Vector Metadata

RAG Optimization

LLM Confidence Prediction

Machine Learning Classification

---

# Success Criteria

Every page should expose rich semantic information that allows AI systems to understand not only the content itself, but also its purpose, intended audience, authority, trustworthiness and permitted AI usage.

The module should transform ordinary web pages into AI-native knowledge resources.