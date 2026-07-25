# Oxy AI Readiness

# Model Context Protocol (MCP) Module Specification

Version 1.0

---

# Purpose

The MCP Module enables WordPress websites to expose standardized resources, tools and capabilities through the Model Context Protocol (MCP).

Its goal is to make the website interoperable with AI assistants, autonomous agents and future AI ecosystems.

The module should support both local and remote MCP discovery.

---

# Vision

WordPress should become an AI-native platform.

AI agents should be able to:

Discover

Authenticate

Read

Search

Execute

Retrieve Context

Call Tools

without requiring custom integrations.

---

# Responsibilities

Generate MCP Server Card

Publish Resources

Register Tools

Register Prompts

Manage Capabilities

Validate Output

Monitor Health

Expose APIs

Support Authentication

Support Versioning

---

# Supported MCP Versions

Current Stable

Experimental

Future Versions

Version Negotiation

Backward Compatibility

---

####################################################

SERVER CARD

####################################################

Fields

Server Name

Description

Organization

Website

Version

Logo

Contact

License

Documentation

Homepage

Capabilities

Resources

Tools

Prompts

Authentication

Languages

Timezone

Region

---

####################################################

CAPABILITIES

####################################################

Resources

Tools

Prompts

Logging

Sampling

Completion

Search

Streaming

Notifications

Authentication

Pagination

Filtering

Sorting

Experimental

---

####################################################

RESOURCE TYPES

####################################################

Pages

Posts

Products

Categories

Tags

Authors

Media

Documentation

FAQs

Knowledge Base

WooCommerce Orders (Optional)

Custom Post Types

Static Files

Generated Markdown

LLMS Resources

Content Signals

---

####################################################

TOOLS

####################################################

Search Content

Read Article

Get Product

Find FAQ

Generate Markdown

Retrieve Metadata

Retrieve Headers

Retrieve Signals

Website Health

Audit Website

Validate Robots

Validate llms

Generate Report

Future Custom Tools

---

####################################################

PROMPTS

####################################################

Website Summary

Business Overview

Product Summary

Support Assistant

FAQ Assistant

Medical Assistant

Developer Assistant

Sales Assistant

Custom Prompt Library

---

####################################################

RESOURCE PROVIDERS

####################################################

WordPress

WooCommerce

Elementor

LearnDash

EDD

REST API

Custom Providers

Third Party Extensions

---

####################################################

AUTHENTICATION

####################################################

Anonymous

API Key

Bearer Token

OAuth

WordPress Login

JWT

Future MCP Authentication

---

####################################################

DISCOVERY

####################################################

Automatic Discovery

Server Card

Resources

Capabilities

Authentication

Version

Health

---

####################################################

VALIDATION

####################################################

JSON Validation

Schema Validation

Required Fields

Capabilities

Duplicate Resources

Invalid IDs

Broken Resources

Invalid URLs

---

####################################################

HEALTH CHECKS

####################################################

Server Reachable

Schema Valid

Authentication Working

Resources Reachable

Tools Reachable

Prompts Reachable

Performance

Version

---

####################################################

REST ENDPOINTS

####################################################

GET

/mcp

GET

/mcp/server

GET

/mcp/resources

GET

/mcp/tools

GET

/mcp/prompts

GET

/mcp/health

POST

/mcp/generate

POST

/mcp/validate

POST

/mcp/reset

---

####################################################

ADMIN UI

####################################################

Overview

Server Card

Capabilities

Resources

Tools

Prompts

Authentication

Validation

Logs

History

Settings

---

####################################################

SERVER CARD EDITOR

####################################################

General

Identity

Branding

Capabilities

Resources

Authentication

Metadata

Preview

Export

---

####################################################

RESOURCE MANAGER

####################################################

Search

Enable

Disable

Filter

Category

Provider

Visibility

Priority

Preview

---

####################################################

TOOLS MANAGER

####################################################

Tool Name

Description

Input Schema

Output Schema

Permissions

Enabled

Preview

Version

---

####################################################

PROMPT LIBRARY

####################################################

Template Name

Category

Variables

Language

Preview

Export

Duplicate

Delete

---

####################################################

DASHBOARD

####################################################

Resources

Tools

Prompts

Health

Version

Validation

Last Sync

Warnings

---

####################################################

AUDIT RULES

####################################################

Server Card Exists

Resources Published

Tools Published

Prompts Published

Authentication Valid

Schema Valid

Version Supported

Response Time

Health Status

---

####################################################

VERSIONING

####################################################

Every update stores

Timestamp

User

Version

Changes

Rollback

---

####################################################

EVENTS

####################################################

MCPGenerated

MCPValidated

MCPUpdated

ResourceAdded

ResourceRemoved

ToolAdded

PromptAdded

AuthenticationChanged

HealthChecked

---

####################################################

FILTERS

####################################################

oxy_ai_mcp_before_generate

oxy_ai_mcp_after_generate

oxy_ai_mcp_resources

oxy_ai_mcp_tools

oxy_ai_mcp_prompts

oxy_ai_mcp_output

---

####################################################

PERFORMANCE

####################################################

Generation

<500ms

Validation

<200ms

Health Check

<1 second

---

####################################################

SECURITY

####################################################

Capability Validation

Nonce

Sanitization

Escaping

Permission Checks

Rate Limiting

Audit Logging

---

####################################################

ACCESSIBILITY

####################################################

Keyboard Navigation

ARIA Labels

Screen Readers

WCAG AA

---

####################################################

FUTURE FEATURES

####################################################

Remote MCP Servers

Multi-Server Management

Marketplace

External Resources

Streaming Resources

AI Tool Marketplace

Remote Tool Execution

Federated Discovery

Cloud Synchronization

Distributed Resources

---

# Success Criteria

A WordPress website should be able to expose standards-compliant MCP resources with minimal configuration.

The module should provide a complete visual interface for managing server identity, resources, tools, prompts and capabilities while remaining compatible with future versions of the Model Context Protocol.