# Oxy AI Readiness

# Plugin SDK

Version 1.1

---

> **Canonical note (ADR-002 / ADR-001):** The MODULE STRUCTURE section below is superseded by the canonical template in `docs/04-Folder-Structure.md`. `StandardInterface` (registered only by modules that own an AI Standard) is listed alongside the other SDK interfaces below. See `.project/adr/ADR-002-folder-structure.md` and `.project/adr/ADR-001-modules-vs-standards.md`.

---

# Purpose

The Oxy Plugin SDK provides a unified framework for extending Oxy AI Readiness.

Instead of modifying the core plugin, developers build independent modules that integrate through the SDK.

Every module becomes a first-class citizen inside the platform.

---

# Vision

The core should never require modification.

Everything should be extensible.

Every capability should be pluggable.

---

# SDK PRINCIPLES

Open for extension.

Closed for modification.

Dependency Injection.

Convention over Configuration.

Interface Driven.

Event Driven.

Module Isolation.

Backward Compatibility.

---

############################################################

ARCHITECTURE

############################################################

                 Oxy Core

                     │

     ┌───────────────┼───────────────┐

 Discovery       Validation      Generation

     │               │               │

 Scoring        Monitoring      Reporting

     │               │               │

 Recommendation AutoFix       REST API

                     │

              Module Manager

                     │

      ┌──────────────┼──────────────┐

   Module A       Module B      Module C

---

############################################################

MODULE LIFECYCLE

############################################################

Install

↓

Register

↓

Boot

↓

Ready

↓

Running

↓

Suspend

↓

Resume

↓

Update

↓

Disable

↓

Uninstall

---

############################################################

MODULE STRUCTURE

############################################################

See docs/04-Folder-Structure.md (canonical, per ADR-002):

MyModule/

    MyModuleModule.php

    MyModuleServiceProvider.php

    MyModuleStandard.php (only if this module owns a Standard)

    config/

    Discovery/

    Generators/

    Validators/

    Scoring/

    AutoFix/

    Monitoring/

    Reports/

    Repositories/

    Http/

        Controllers/

        Requests/

    Routes/

    Database/

        Migrations/

    Resources/

    Events/

    Tests/

        Unit/

        Integration/

    README.md

---

############################################################

BASE CLASS

############################################################

Every module extends

OxyModule

---

Required methods

name()

id()

version()

description()

author()

boot()

register()

shutdown()

---

############################################################

SERVICE PROVIDER

############################################################

Every module provides

Services

Bindings

Singletons

Configuration

Commands

Events

Hooks

---

############################################################

MODULE CAPABILITIES

############################################################

Register Generator

Register Validator

Register Discovery Provider

Register Score Provider

Register Monitor

Register Reporter

Register Auto Fix

Register Recommendation Provider

Register Dashboard Widget

Register REST Routes

Register CLI Commands

Register Scheduler

Register Notification Provider

Register Exporter

Register Importer

---

############################################################

HOOK SYSTEM

############################################################

Before Generation

After Generation

Before Validation

After Validation

Before Audit

After Audit

Before AutoFix

After AutoFix

Before Monitoring

After Monitoring

---

############################################################

EVENT BUS

############################################################

ModuleInstalled

ModuleEnabled

ModuleDisabled

ModuleUpdated

ResourceGenerated

ValidationCompleted

ScoreCalculated

IssueResolved

NotificationSent

---

############################################################

DEPENDENCY SYSTEM

############################################################

Require Modules

Optional Modules

Conflicting Modules

Minimum Version

Maximum Version

PHP Requirements

WordPress Requirements

---

############################################################

CONFIGURATION

############################################################

Every module supports

Default Configuration

Environment Overrides

Runtime Configuration

User Configuration

Migration Configuration

---

############################################################

ASSET PIPELINE

############################################################

JavaScript

TypeScript

CSS

SCSS

Images

Fonts

Icons

Localization

---

############################################################

DATABASE

############################################################

Modules may register

Tables

Indexes

Views

Options

Settings

Caches

Queues

---

############################################################

CLI

############################################################

Modules may expose

Commands

Maintenance

Debugging

Imports

Exports

Generation

Repair

Monitoring

---

############################################################

REST API

############################################################

Modules register

Routes

Controllers

Schemas

Permissions

OpenAPI Metadata

Rate Limits

---

############################################################

DASHBOARD

############################################################

Widgets

Cards

Charts

Reports

Actions

Notifications

Settings

Health Indicators

---

############################################################

PERMISSIONS

############################################################

Register Capabilities

Register Roles

Permission Policies

Visibility Rules

---

############################################################

SCHEDULER

############################################################

Cron Jobs

Intervals

Background Tasks

Queues

Workers

---

############################################################

LOGGING

############################################################

Structured Logs

Module Logs

Debug Logs

Audit Logs

Performance Logs

---

############################################################

TESTING

############################################################

Unit Tests

Integration Tests

Snapshot Tests

Performance Tests

Compatibility Tests

Regression Tests

---

############################################################

PACKAGE MANIFEST

############################################################

Every module contains

module.json

Includes

Name

ID

Version

Author

License

Dependencies

Permissions

Entry Point

Minimum Oxy Version

Compatibility

---

############################################################

MARKETPLACE READY

############################################################

Digital Signature

Integrity Check

License Validation

Automatic Updates

Version Compatibility

Dependency Resolution

---

############################################################

SDK INTERFACES

############################################################

GeneratorInterface

ValidatorInterface

StandardInterface

DiscoveryInterface

ScoreProviderInterface

MonitorInterface

ReporterInterface

RecommendationInterface

AutoFixInterface

ExporterInterface

ImporterInterface

DashboardWidgetInterface

NotificationProviderInterface

---

############################################################

FUTURE FEATURES

############################################################

Remote Modules

Cloud Modules

Marketplace

Module Store

Commercial SDK

Enterprise Extensions

Private Repositories

Hot Reload

Live Module Updates

Remote Debugging

AI Generated Modules

---

# Success Criteria

A developer should be able to build, package and distribute a complete Oxy module without modifying the core platform.

The SDK should provide stable APIs, clear extension points and long-term backward compatibility.