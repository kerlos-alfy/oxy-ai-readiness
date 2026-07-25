# Oxy AI Readiness

Folder Structure

Version 1.1

---

> **Canonical note (ADR-002):** This document's Module Structure section below is the canonical module template, aligned with `docs/29-Developer-Guide.md`'s worked examples. `app/Standards/` is not a top-level directory — Standards live inside their owning Module (ADR-001). See `.project/adr/ADR-002-folder-structure.md`.

---

# Philosophy

The project must follow Enterprise Software Architecture.

Every responsibility must live in its own folder.

No large files.

No God Classes.

No mixed responsibilities.

The project should remain maintainable for 10+ years.

---

# Root Structure

oxy-ai-readiness/

│
├── app/
├── assets/
├── config/
├── database/
├── docs/
├── languages/
├── routes/
├── storage/
├── templates/
├── tests/
├── vendor/
│
├── composer.json
├── package.json
├── phpcs.xml
├── phpstan.neon
├── webpack.config.js
├── vite.config.js
├── readme.txt
├── README.md
├── uninstall.php
├── oxy-ai-readiness.php

---

# App

Contains all application source code.

app/

Admin/

Core/

Domain/

Application/

Infrastructure/

Modules/

Services/

Repositories/

Support/

Traits/

Contracts/

DTO/

Events/

Exceptions/

Helpers/

Http/

Console/

Jobs/

Models/

Policies/

Providers/

Validators/

---

# Admin

Everything related to wp-admin.

Admin/

Dashboard/

Pages/

Settings/

Widgets/

Components/

Assets/

Views/

Controllers/

Middleware/

---

# Core

Contains plugin kernel.

Core/

Application.php

Bootstrap.php

Plugin.php

Loader.php

Container.php

Kernel.php

Config.php

Hooks.php

Scheduler.php

ModuleRegistry.php

StandardsRegistry.php

---

# Modules

Every feature is a separate module.

Modules/

Audit/

Robots/

Headers/

Markdown/

LLMS/

Auth/

ContentSignals/

Discovery/

ApiCatalog/

MCP/

Commerce/

OAuth/

AgentSkills/

Reports/

Monitoring/

Security/

Performance/

AISEO/

Analytics/

---

# Module Structure

Canonical structure (ADR-002). Every module follows exactly this structure.

Example

Modules/

Robots/

RobotsModule.php

RobotsServiceProvider.php

RobotsStandard.php (only if the module owns a Standard — see docs/23-AI-Standards-Layer.md)

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

There is no per-module Views/ or Assets/ folder. The admin UI is a single centralized React SPA (see the React section below), not per-module server-rendered views.

---

# Services

Contains shared business services.

Services/

AuditEngine.php

MarkdownEngine.php

HeaderManager.php

FileGenerator.php

Logger.php

Scanner.php

ScoreCalculator.php

DiscoveryService.php

HealthService.php

CacheService.php

ModuleManager.php

SchedulerService.php

LicenseService.php

---

# Repositories

Repositories communicate with WordPress.

Repositories/

OptionsRepository.php

PostRepository.php

FileRepository.php

TransientRepository.php

UserRepository.php

---

# Contracts

Contains interfaces.

Contracts/

ModuleInterface.php

StandardInterface.php

ScannerInterface.php

GeneratorInterface.php

RepositoryInterface.php

AuditRuleInterface.php

ServiceInterface.php

LoggerInterface.php

---

# DTO

Data Transfer Objects.

DTO/

AuditResult.php

AuditItem.php

HeaderResult.php

ScanSummary.php

ScoreResult.php

Issue.php

Recommendation.php

---

# Events

Events/

AuditCompleted.php

AuditStarted.php

ModuleEnabled.php

ModuleDisabled.php

RobotsGenerated.php

HeadersUpdated.php

MarkdownCreated.php

ScoreUpdated.php

---

# Listeners

Listeners/

UpdateDashboard.php

ClearCache.php

GenerateReport.php

NotifyAdmin.php

LogEvent.php

---

# Support

Reusable utility classes.

Support/

Filesystem.php

Path.php

Collection.php

Json.php

ArrayHelper.php

Text.php

Url.php

Slug.php

Version.php

Platform.php

---

# Traits

Traits/

Singleton.php

HasSettings.php

HasLogger.php

HasFilesystem.php

HasModules.php

---

# Validators

Validators/

HeaderValidator.php

MarkdownValidator.php

RobotsValidator.php

JsonValidator.php

SchemaValidator.php

---

# Exceptions

Exceptions/

ModuleException.php

AuditException.php

ValidationException.php

GenerationException.php

RepositoryException.php

---

# HTTP

Http/

Middleware/

Controllers/

Requests/

Responses/

Resources/

---

# REST API

routes/

api.php

admin.php

webhooks.php

health.php

---

# Config

config/

audit.php

modules.php

headers.php

robots.php

llms.php

markdown.php

security.php

performance.php

ui.php

---

# Assets

assets/

css/

scss/

js/

react/

images/

icons/

fonts/

animations/

vendors/

---

# React

assets/react/

Dashboard/

Audit/

Robots/

Markdown/

Headers/

Settings/

Components/

Layouts/

Hooks/

Contexts/

Store/

Utils/

---

# Storage

storage/

logs/

cache/

generated/

reports/

exports/

imports/

tmp/

---

# Templates

templates/

emails/

reports/

cards/

markdown/

robots/

headers/

---

# Database

database/

migrations/

seeders/

schemas/

Although WordPress doesn't require migrations,
the plugin should support them for enterprise editions.

---

# Tests

tests/

Unit/

Feature/

Integration/

Performance/

Security/

Snapshots/

Fixtures/

Mock/

Factories/

---

# Languages

languages/

en_US.mo

en_US.po

ar.mo

ar.po

fr.po

de.po

es.po

---

# Documentation

docs/

Vision.md

Architecture.md

UI.md

Modules.md

API.md

REST.md

Security.md

Roadmap.md

DeveloperGuide.md

Contributing.md

---

# Composer

composer.json

PSR-4

Autoload

Scripts

Development Dependencies

Production Dependencies

---

# Build

Node.js

Vite

Tailwind

PostCSS

ESLint

Prettier

---

# Distribution

dist/

Final production build.

Only production files.

No tests.

No docs.

No source maps.

---

# Principle

No file should exceed approximately 500 lines whenever reasonably possible.

If a file becomes too large...

Split it.

---

# Module Independence

Every module must be removable.

Deleting one module should never break another.

---

# Future Expansion

The folder structure must support:

Cloud Sync

Marketplace

Enterprise Edition

White Label

SaaS Dashboard

AI Monitoring

Remote Management

Without architectural changes.

---

# Final Rule

Folder names must always describe responsibility.

Never describe implementation.

Example

Good

Audit

Markdown

Headers

Discovery

Bad

Utils2

HelpersFinal

NewSystem

Test123