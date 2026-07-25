# Oxy AI Readiness

# Developer Guide

Version 1.1

---

> **Canonical note (ADR-001 / ADR-002):** This is the reference implementation of the canonical module structure. `app/Standards/` does not exist as a top-level directory — a Standard is defined inside its owning module (see the "CREATING AN AI STANDARD" section below, updated accordingly). See `.project/adr/ADR-001-modules-vs-standards.md` and `.project/adr/ADR-002-folder-structure.md`.

---

# Purpose

This guide explains how developers install, understand, extend, test and contribute to Oxy AI Readiness.

It is the primary implementation reference for core contributors, module developers, integration teams and external extension authors.

The guide focuses on practical workflows, architectural boundaries and production-ready development standards.

---

# Audience

Core Developers

Module Developers

Frontend Developers

API Developers

QA Engineers

DevOps Engineers

Security Reviewers

Third-Party Extension Authors

---

# Development Principles

Respect Architectural Boundaries

Program Against Interfaces

Keep Modules Isolated

Prefer Dependency Injection

Avoid Global State

Use Events for Cross-Module Communication

Validate Every Input

Escape Every Output

Write Tests Before Release

Never Modify Core for Extension Logic

---

############################################################

SYSTEM REQUIREMENTS

############################################################

PHP

8.1 or newer

Recommended

8.3+

---

WordPress

Latest stable version

Supported previous versions are defined in the compatibility matrix.

---

Database

MySQL 5.7+

MySQL 8 recommended

MariaDB 10.6+

---

Node.js

20 LTS or newer

---

Composer

2.7 or newer

---

Recommended Tools

Git

WP-CLI

Docker

Docker Compose

Xdebug

Redis

Playwright

PHPUnit

PHPStan

PHPCS

---

############################################################

REPOSITORY SETUP

############################################################

Clone the repository.

```bash
git clone <repository-url> oxy-ai-readiness
cd oxy-ai-readiness
```

Install PHP dependencies.

```bash
composer install
```

Install JavaScript dependencies.

```bash
npm install
```

Create the local environment configuration.

```bash
cp .env.example .env
```

Build assets.

```bash
npm run build
```

For active frontend development:

```bash
npm run dev
```

---

############################################################

RECOMMENDED LOCAL ENVIRONMENT

############################################################

Use Docker Compose for a reproducible environment.

Recommended services:

WordPress

PHP-FPM

MySQL

Nginx

Redis

Mailpit

WP-CLI

Node Development Server

---

Example environment layout:

```text
docker/
├── nginx/
├── php/
├── mysql/
├── wordpress/
└── scripts/
```

---

############################################################

ENVIRONMENT VARIABLES

############################################################

Example variables:

```env
OXY_ENV=local
OXY_DEBUG=true
OXY_LOG_LEVEL=debug
OXY_CACHE_DRIVER=array
OXY_QUEUE_DRIVER=sync
OXY_DISABLE_TELEMETRY=true
OXY_LICENSE_MODE=development
```

Never commit real secrets.

Never store production credentials in `.env.example`.

---

############################################################

PROJECT STRUCTURE

############################################################

```text
oxy-ai-readiness/
├── app/
│   ├── Core/
│   ├── Admin/
│   ├── Modules/
│   ├── Services/
│   ├── Repositories/
│   ├── Contracts/
│   ├── DTO/
│   ├── Events/
│   ├── Exceptions/
│   ├── Http/
│   ├── Console/
│   ├── Support/
│   └── Traits/
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
├── oxy-ai-readiness.php
├── composer.json
├── package.json
└── README.md
```

Note: `app/Standards/` does not exist as a top-level directory. A Standard is owned by exactly one Module and lives inside that module's folder (see "CREATING AN AI STANDARD" below).

---

############################################################

BOOTSTRAP FLOW

############################################################

Plugin File

↓

Application Bootstrap

↓

Service Container

↓

Core Service Providers

↓

Module Registry

↓

Standards Registry

↓

Routes

↓

Events

↓

Admin Application

↓

Ready Event

---

############################################################

CORE BOUNDARIES

############################################################

Core may depend on:

Contracts

DTOs

Repositories

Services

Infrastructure Adapters

---

Modules may depend on:

Public Core Contracts

SDK Interfaces

Shared DTOs

Events

Approved Services

---

Modules must not depend directly on:

Another module's internal classes

Private Core classes

Admin UI implementation details

Concrete database drivers

Undocumented global state

---

############################################################

SERVICE CONTAINER

############################################################

Services should be registered through service providers.

Example:

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example;

use OxyAI\Core\Container\ServiceProvider;
use OxyAI\Contracts\ValidatorInterface;

final class ExampleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(
            ExampleValidator::class,
            static fn (): ExampleValidator => new ExampleValidator()
        );

        $this->container->tag(
            ExampleValidator::class,
            ValidatorInterface::class
        );
    }

    public function boot(): void
    {
        // Register runtime hooks here.
    }
}
```

Use `register()` for bindings.

Use `boot()` for runtime behavior.

Do not execute heavy operations during registration.

---

############################################################

DEPENDENCY INJECTION

############################################################

Preferred:

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example;

use OxyAI\Contracts\LoggerInterface;
use OxyAI\Contracts\RepositoryInterface;

final class ExampleService
{
    public function __construct(
        private readonly RepositoryInterface $repository,
        private readonly LoggerInterface $logger
    ) {
    }
}
```

Avoid:

```php
global $wpdb;
```

Avoid:

```php
new ExampleRepository();
```

inside business services.

Dependencies should be injected through constructors whenever possible.

---

############################################################

CREATING A MODULE

############################################################

Create a directory:

```text
app/Modules/Example/
```

Recommended structure:

```text
Example/
├── ExampleModule.php
├── ExampleServiceProvider.php
├── config/
│   └── example.php
├── Discovery/
├── Generators/
├── Validators/
├── Scoring/
├── AutoFix/
├── Monitoring/
├── Reports/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Routes/
├── Resources/
├── Database/
│   └── Migrations/
└── Tests/
```

---

############################################################

MODULE CLASS

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example;

use OxyAI\SDK\Module\OxyModule;

final class ExampleModule extends OxyModule
{
    public function id(): string
    {
        return 'example';
    }

    public function name(): string
    {
        return 'Example';
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function description(): string
    {
        return 'Example Oxy AI Readiness module.';
    }

    public function register(): void
    {
        $this->serviceProvider(ExampleServiceProvider::class);

        $this->generator(Generators\ExampleGenerator::class);

        $this->validator(Validators\ExampleValidator::class);

        $this->scoreProvider(Scoring\ExampleScoreProvider::class);

        $this->autoFix(AutoFix\ExampleAutoFix::class);

        $this->monitor(Monitoring\ExampleMonitor::class);

        $this->reporter(Reports\ExampleReporter::class);
    }

    public function boot(): void
    {
        // Register runtime hooks and events.
    }
}
```

---

############################################################

MODULE MANIFEST

############################################################

Every distributable module must contain `module.json`.

```json
{
  "id": "example",
  "name": "Example",
  "version": "1.0.0",
  "description": "Example Oxy module",
  "author": "Oxy",
  "license": "proprietary",
  "entry": "ExampleModule",
  "minimum_oxy_version": "1.0.0",
  "minimum_php_version": "8.1",
  "minimum_wordpress_version": "6.5",
  "dependencies": [],
  "optional_dependencies": [],
  "conflicts": [],
  "capabilities": [
    "view_example",
    "manage_example"
  ]
}
```

---

############################################################

CREATING AN AI STANDARD

############################################################

A Standard is owned by exactly one Module and lives inside that module's folder — there is no
separate `app/Standards/` directory (ADR-001). Only add a Standard class if the module implements
a published, externally-versioned AI specification (see the ownership table in
docs/23-AI-Standards-Layer.md).

```text
app/Modules/Example/
├── ExampleModule.php
├── ExampleServiceProvider.php
├── ExampleStandard.php
├── Generators/
├── Validators/
├── Scoring/
├── Monitoring/
├── Reports/
└── ...
```

---

############################################################

STANDARD CLASS

############################################################

A Standard's lifecycle methods delegate to the same Generator/Validator/ScoreProvider/Monitor/
Reporter the owning module already registers via its ServiceProvider — it never re-implements
that logic. Its only unique responsibility is external-specification metadata
(specification/version/supports/migrate).

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example;

use OxyAI\Standards\Contracts\StandardInterface;
use OxyAI\Standards\DTO\StandardDefinition;
use OxyAI\Modules\Example\Generators\ExampleGenerator;
use OxyAI\Modules\Example\Validators\ExampleValidator;

final class ExampleStandard implements StandardInterface
{
    public function __construct(
        private readonly ExampleGenerator $generator,
        private readonly ExampleValidator $validator
    ) {
    }

    public function definition(): StandardDefinition
    {
        return new StandardDefinition(
            id: 'example-standard',
            name: 'Example Standard',
            version: '1.0',
            status: 'experimental'
        );
    }

    public function supports(): bool
    {
        return true;
    }

    public function discover(): array
    {
        return [];
    }

    public function generate(): mixed
    {
        // Delegates to the module's registered generator.
        return $this->generator->generate(/* ...context */);
    }

    public function validate(): array
    {
        // Delegates to the module's registered validator.
        return [$this->validator->validate(/* ...resource */)];
    }

    public function score(): float
    {
        return 0.0;
    }

    public function monitor(): array
    {
        return [];
    }

    public function report(): array
    {
        return [];
    }
}
```

---

############################################################

ADDING A GENERATOR

############################################################

Every generator implements `GeneratorInterface`.

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Generators;

use OxyAI\Contracts\Generation\GeneratorInterface;
use OxyAI\DTO\Generation\GenerationContext;
use OxyAI\DTO\Generation\GeneratedResource;

final class ExampleGenerator implements GeneratorInterface
{
    public function supports(string $resourceType): bool
    {
        return $resourceType === 'example';
    }

    public function generate(
        GenerationContext $context
    ): GeneratedResource {
        $content = [
            'name' => get_bloginfo('name'),
            'url'  => home_url('/'),
        ];

        return new GeneratedResource(
            type: 'application/json',
            content: wp_json_encode(
                $content,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ),
            checksum: hash(
                'sha256',
                wp_json_encode($content)
            )
        );
    }
}
```

Generators must be:

Deterministic

Side-effect free before publishing

Version aware

Validatable

Cacheable

Rollback compatible

---

############################################################

ADDING A VALIDATOR

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Validators;

use OxyAI\Contracts\Validation\ValidatorInterface;
use OxyAI\DTO\Validation\ValidationResult;

final class ExampleValidator implements ValidatorInterface
{
    public function supports(mixed $resource): bool
    {
        return is_string($resource);
    }

    public function validate(mixed $resource): ValidationResult
    {
        if ($resource === '') {
            return ValidationResult::failure(
                code: 'example.empty',
                message: 'The generated resource is empty.'
            );
        }

        return ValidationResult::success(
            code: 'example.valid',
            message: 'The generated resource is valid.'
        );
    }
}
```

Validators must never mutate the validated resource.

Validation must be deterministic.

Errors must include machine-readable codes.

---

############################################################

ADDING AN AUDIT RULE

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Audit;

use OxyAI\Contracts\Audit\AuditRuleInterface;
use OxyAI\DTO\Audit\AuditContext;
use OxyAI\DTO\Audit\AuditResult;

final class ExampleAuditRule implements AuditRuleInterface
{
    public function id(): string
    {
        return 'example.resource_exists';
    }

    public function category(): string
    {
        return 'discovery';
    }

    public function severity(): string
    {
        return 'high';
    }

    public function evaluate(
        AuditContext $context
    ): AuditResult {
        $exists = $context->resources()->has('example');

        if ($exists) {
            return AuditResult::pass(
                ruleId: $this->id(),
                message: 'Example resource exists.'
            );
        }

        return AuditResult::fail(
            ruleId: $this->id(),
            message: 'Example resource is missing.',
            recommendation: 'Generate the example resource.',
            autoFixAvailable: true
        );
    }
}
```

Every rule must define:

Stable ID

Category

Severity

Expected Condition

Failure Message

Recommendation

Auto Fix Availability

Score Contribution

---

############################################################

ADDING AN AUTOFIX

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\AutoFix;

use OxyAI\Contracts\AutoFix\AutoFixInterface;
use OxyAI\DTO\AutoFix\AutoFixContext;
use OxyAI\DTO\AutoFix\AutoFixResult;

final class ExampleAutoFix implements AutoFixInterface
{
    public function supports(string $issueId): bool
    {
        return $issueId === 'example.resource_exists';
    }

    public function riskLevel(): string
    {
        return 'safe';
    }

    public function execute(
        AutoFixContext $context
    ): AutoFixResult {
        $snapshot = $context->snapshots()->create(
            resource: 'example'
        );

        try {
            $context->generation()->generateAndPublish('example');

            $validation = $context->validation()->validate('example');

            if (! $validation->passed()) {
                $context->snapshots()->restore($snapshot);

                return AutoFixResult::failed(
                    message: 'Validation failed. The previous state was restored.'
                );
            }

            return AutoFixResult::success(
                message: 'The example resource was generated successfully.',
                rollbackId: $snapshot->id()
            );
        } catch (\Throwable $exception) {
            $context->snapshots()->restore($snapshot);

            return AutoFixResult::failed(
                message: $exception->getMessage()
            );
        }
    }
}
```

Every Auto Fix must:

Check permissions

Create a backup

Perform atomic changes

Validate after execution

Rollback on failure

Log the operation

Return a structured result

---

############################################################

ADDING A SCORE PROVIDER

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Scoring;

use OxyAI\Contracts\Scoring\ScoreProviderInterface;
use OxyAI\DTO\Scoring\ScoreContribution;

final class ExampleScoreProvider implements ScoreProviderInterface
{
    public function contributions(): array
    {
        return [
            new ScoreContribution(
                id: 'example.resource',
                category: 'discovery',
                weight: 5,
                earned: 5,
                confidence: 1.0
            ),
        ];
    }
}
```

Score providers must not calculate the overall score.

They only return individual contributions.

The Scoring Engine owns aggregation and grading.

---

############################################################

ADDING A MONITOR

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Monitoring;

use OxyAI\Contracts\Monitoring\MonitorInterface;
use OxyAI\DTO\Monitoring\MonitorResult;

final class ExampleMonitor implements MonitorInterface
{
    public function id(): string
    {
        return 'example.resource_health';
    }

    public function check(): MonitorResult
    {
        $available = wp_remote_retrieve_response_code(
            wp_remote_get(home_url('/example'))
        ) === 200;

        return $available
            ? MonitorResult::healthy('Example resource is available.')
            : MonitorResult::critical('Example resource is unavailable.');
    }
}
```

External calls must define timeouts and retries.

Monitors must remain lightweight.

Long-running checks must use background jobs.

---

############################################################

ADDING A REPORTER

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Reports;

use OxyAI\Contracts\Reporting\ReporterInterface;

final class ExampleReporter implements ReporterInterface
{
    public function section(): array
    {
        return [
            'title' => 'Example Standard',
            'metrics' => [
                'status' => 'healthy',
                'score'  => 100,
            ],
        ];
    }
}
```

Reporters return structured data.

They must not directly generate PDF or HTML.

The Reporting Engine handles presentation and export.

---

############################################################

ADDING A REST ROUTE

############################################################

Routes belong in versioned route files.

```php
<?php

use OxyAI\Modules\Example\Http\Controllers\ExampleController;

return static function (): void {
    register_rest_route(
        'oxy-ai/v1',
        '/example',
        [
            'methods' => 'GET',
            'callback' => [ExampleController::class, 'index'],
            'permission_callback' => [ExampleController::class, 'authorize'],
        ]
    );
};
```

Controller example:

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

final class ExampleController
{
    public function authorize(): bool
    {
        return current_user_can('view_oxy_example');
    }

    public function index(
        WP_REST_Request $request
    ): WP_REST_Response {
        return new WP_REST_Response(
            [
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                ],
            ],
            200
        );
    }
}
```

REST endpoints must include:

Authentication

Authorization

Request validation

Consistent responses

Error codes

OpenAPI metadata

Rate-limit policy

---

############################################################

REQUEST VALIDATION

############################################################

Request objects should validate and sanitize input.

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Http\Requests;

use InvalidArgumentException;
use WP_REST_Request;

final class GenerateExampleRequest
{
    public function fromRequest(
        WP_REST_Request $request
    ): array {
        $mode = sanitize_key(
            (string) $request->get_param('mode')
        );

        if (! in_array($mode, ['preview', 'publish'], true)) {
            throw new InvalidArgumentException(
                'Invalid generation mode.'
            );
        }

        return [
            'mode' => $mode,
        ];
    }
}
```

---

############################################################

ADDING A WP-CLI COMMAND

############################################################

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Console;

use WP_CLI;

final class ExampleCommand
{
    /**
     * Generate the example resource.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
     *
     * ## EXAMPLES
     *
     *     wp oxy example generate
     */
    public function generate(
        array $args,
        array $assocArgs
    ): void {
        WP_CLI::success(
            'Example resource generated.'
        );
    }
}
```

Register under:

```bash
wp oxy example generate
```

CLI commands must support appropriate exit codes.

Machine-readable output should support JSON where useful.

---

############################################################

ADDING AN ADMIN SCREEN

############################################################

Admin screens should be API-driven.

Recommended frontend stack:

React

TypeScript

WordPress Components

WordPress Data

WordPress API Fetch

React Query where approved

---

Screen structure (under the centralized React app — see docs/04-Folder-Structure.md's React section):

```text
assets/react/Example/
├── ExampleScreen.tsx
├── Components/
├── Hooks/
├── Store/
├── Utils/
└── Tests/
```

---

Example:

```tsx
import { Button, Card, CardBody } from '@wordpress/components';

export function ExampleScreen(): JSX.Element {
    return (
        <Card>
            <CardBody>
                <h2>Example Standard</h2>

                <Button variant="primary">
                    Run Validation
                </Button>
            </CardBody>
        </Card>
    );
}
```

Do not access the database directly from the frontend.

Do not embed business logic inside React components.

Use API services and typed hooks.

---

############################################################

FRONTEND API SERVICE

############################################################

```ts
import apiFetch from '@wordpress/api-fetch';

export interface ExampleStatus {
    status: string;
}

export async function fetchExampleStatus(): Promise<ExampleStatus> {
    const response = await apiFetch<{
        success: boolean;
        data: ExampleStatus;
    }>({
        path: '/oxy-ai/v1/example',
    });

    return response.data;
}
```

---

############################################################

STATE MANAGEMENT

############################################################

Use local component state for simple UI state.

Use server-state libraries or WordPress data stores for shared remote state.

Avoid storing duplicate server data in multiple stores.

Keep filters and pagination in URL state when appropriate.

---

############################################################

DATABASE MIGRATIONS

############################################################

Create migrations under:

```text
database/migrations/
```

Example:

```php
<?php

declare(strict_types=1);

namespace OxyAI\Database\Migrations;

use OxyAI\Contracts\Database\MigrationInterface;

final class CreateExampleTable implements MigrationInterface
{
    public function version(): string
    {
        return '1.1.0';
    }

    public function up(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'oxy_example';

        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            uuid CHAR(36) NOT NULL,
            status VARCHAR(32) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uuid (uuid),
            KEY status_created_at (status, created_at)
        ) {$charsetCollate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }

    public function down(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'oxy_example';

        $wpdb->query(
            "DROP TABLE IF EXISTS {$table}"
        );
    }
}
```

Never concatenate user input into SQL.

Every schema change must have a migration.

Every migration must be tested against upgrade scenarios.

---

############################################################

REPOSITORIES

############################################################

Repositories isolate persistence logic.

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Repositories;

use OxyAI\Modules\Example\Contracts\ExampleRepositoryInterface;

final class ExampleRepository implements ExampleRepositoryInterface
{
    public function findByUuid(string $uuid): ?array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'oxy_example';

        $query = $wpdb->prepare(
            "SELECT id, uuid, status, created_at, updated_at
             FROM {$table}
             WHERE uuid = %s
             LIMIT 1",
            $uuid
        );

        $result = $wpdb->get_row($query, ARRAY_A);

        return is_array($result) ? $result : null;
    }
}
```

Business services must depend on repository interfaces.

---

############################################################

EVENTS

############################################################

Use events when multiple listeners may respond to a completed action.

Example:

```php
$this->events->dispatch(
    new ResourceGenerated(
        resourceType: 'example',
        checksum: $resource->checksum()
    )
);
```

Do not use events when a synchronous return value is required.

Do not hide critical business logic behind undocumented event chains.

---

############################################################

WORDPRESS HOOKS

############################################################

Use WordPress actions and filters for public extension points.

Internal communication should prefer the Oxy Event Bus.

Public hooks must be:

Documented

Namespaced

Stable

Typed where possible

Backward compatible

---

Example filter:

```php
$weights = apply_filters(
    'oxy_ai_example_score_weights',
    $weights,
    $context
);
```

---

############################################################

ERROR HANDLING

############################################################

Use domain-specific exceptions.

Examples:

GenerationException

ValidationException

AuthorizationException

ModuleDependencyException

MigrationException

RollbackException

---

Do not expose internal stack traces to normal users.

Log the full exception internally.

Return safe, actionable messages publicly.

---

############################################################

LOGGING

############################################################

Use structured logging.

```php
$this->logger->info(
    'Example resource generated.',
    [
        'resource' => 'example',
        'checksum' => $checksum,
        'request_id' => $requestId,
    ]
);
```

Never log:

Passwords

Raw tokens

License keys

Private API keys

Sensitive personal data

---

############################################################

CONFIGURATION

############################################################

Default configuration belongs in:

```text
config/example.php
```

Example:

```php
<?php

return [
    'enabled' => true,
    'cache_ttl' => 3600,
    'monitoring_interval' => 'hourly',
    'generation_mode' => 'dynamic',
];
```

Use the configuration repository.

Do not call `get_option()` throughout business logic.

---

############################################################

FEATURE FLAGS

############################################################

Experimental features should use feature flags.

Example:

```php
if (! $this->features->enabled('example_streaming')) {
    return;
}
```

Feature flags must define:

ID

Description

Default State

Edition

Environment

Expiration or Review Date

---

############################################################

CACHING

############################################################

Cache through the shared cache service.

Example:

```php
return $this->cache->remember(
    key: 'example.status',
    ttl: 300,
    callback: fn (): array => $this->repository->status()
);
```

Cache keys must be site-aware and version-aware.

Every cached resource must define invalidation rules.

---

############################################################

BACKGROUND JOBS

############################################################

Use background jobs for:

Large audits

Crawling

Report generation

Bulk generation

Bulk Auto Fix

Cloud synchronization

Long external requests

---

Example job:

```php
<?php

declare(strict_types=1);

namespace OxyAI\Modules\Example\Jobs;

use OxyAI\Contracts\Queue\JobInterface;

final class GenerateExampleJob implements JobInterface
{
    public function handle(): void
    {
        // Execute generation.
    }

    public function retries(): int
    {
        return 3;
    }

    public function timeout(): int
    {
        return 60;
    }
}
```

Jobs must be idempotent whenever possible.

---

############################################################

SECURITY REQUIREMENTS

############################################################

Every write operation must validate:

Authentication

Capability

Nonce where applicable

Input schema

Resource ownership

Feature availability

License entitlement

---

Every file operation must validate:

Canonical path

Allowed directory

Filename

MIME type

Extension

Permissions

Checksum

---

############################################################

LOCALIZATION

############################################################

All user-facing strings must be translatable.

PHP:

```php
esc_html__(
    'Example resource generated.',
    'oxy-ai-readiness'
);
```

JavaScript:

```ts
import { __ } from '@wordpress/i18n';

__('Example resource generated.', 'oxy-ai-readiness');
```

Never concatenate translated fragments into sentences.

Support RTL layouts.

---

############################################################

ACCESSIBILITY

############################################################

Admin UI must support:

Keyboard Navigation

Visible Focus

ARIA Labels

Screen Readers

Reduced Motion

Semantic HTML

WCAG 2.2 AA Contrast

Accessible Error Messages

---

############################################################

CODING STANDARDS

############################################################

PHP

Strict Types

WordPress Coding Standards

PSR-12 where compatible

Typed Properties

Return Types

Final Classes by default

Readonly properties where appropriate

No dynamic properties

---

JavaScript and TypeScript

TypeScript Strict Mode

ESLint

Prettier

No implicit any

Typed API responses

Functional components

Accessible components

---

CSS

Component scope

CSS variables

Logical properties

Responsive design

No unnecessary `!important`

No global selector leakage

---

############################################################

NAMING CONVENTIONS

############################################################

Classes

PascalCase

Methods

camelCase

Database Tables

snake_case

REST Paths

kebab-case

Event Names

Past Tense

Interfaces

Descriptive names ending with Interface

DTOs

Descriptive names ending with DTO only when needed

---

############################################################

TESTING A NEW FEATURE

############################################################

Every new feature should include:

Unit Test

Integration Test

Authorization Test

Failure Test

Regression Test where applicable

Snapshot Test for generated output

Browser Test for critical UI

---

Example unit test:

```php
<?php

declare(strict_types=1);

namespace OxyAI\Tests\Unit\Modules\Example;

use OxyAI\Modules\Example\Validators\ExampleValidator;
use PHPUnit\Framework\TestCase;

final class ExampleValidatorTest extends TestCase
{
    public function test_empty_resource_fails_validation(): void
    {
        $validator = new ExampleValidator();

        $result = $validator->validate('');

        self::assertFalse($result->passed());
        self::assertSame(
            'example.empty',
            $result->code()
        );
    }
}
```

---

############################################################

STATIC ANALYSIS

############################################################

Run:

```bash
composer analyse
```

Target:

PHPStan Level 8 minimum

No baseline additions without explanation

No ignored errors without documented justification

---

############################################################

LINTING

############################################################

PHP:

```bash
composer lint
```

JavaScript:

```bash
npm run lint
```

Type checking:

```bash
npm run typecheck
```

CSS:

```bash
npm run lint:css
```

---

############################################################

RUNNING TESTS

############################################################

PHP unit tests:

```bash
composer test
```

Integration tests:

```bash
composer test:integration
```

JavaScript tests:

```bash
npm test
```

Browser tests:

```bash
npm run test:e2e
```

All quality checks:

```bash
composer quality
npm run quality
```

---

############################################################

GIT WORKFLOW

############################################################

Main branches:

main

develop

release/*

hotfix/*

feature/*

---

Recommended workflow:

Create Branch

↓

Implement

↓

Add Tests

↓

Run Quality Checks

↓

Update Documentation

↓

Open Pull Request

↓

Code Review

↓

CI Approval

↓

Merge

---

############################################################

BRANCH NAMING

############################################################

```text
feature/llms-generator
fix/score-boundary
refactor/cache-layer
docs/developer-guide
test/autofix-rollback
security/rest-permissions
```

---

############################################################

COMMIT FORMAT

############################################################

Use conventional commits.

```text
feat: add llms generation service
fix: prevent duplicate discovery headers
docs: add module development guide
test: cover failed autofix rollback
refactor: extract score calculator
security: harden webhook validation
```

---

############################################################

PULL REQUEST REQUIREMENTS

############################################################

Every pull request must include:

Summary

Reason

Implementation Notes

Testing Performed

Screenshots for UI

Migration Notes

Security Impact

Performance Impact

Backward Compatibility

Documentation Updates

---

############################################################

CODE REVIEW CHECKLIST

############################################################

Architecture respected

Interfaces used

Permissions checked

Input validated

Output escaped

Queries prepared

Failures handled

Tests included

Documentation updated

No secrets committed

No frontend performance impact

No breaking public API change

---

############################################################

BACKWARD COMPATIBILITY

############################################################

Do not remove public APIs without deprecation.

Do not rename hooks silently.

Do not change response schemas without versioning.

Do not change database meaning without migration.

Do not break module contracts within a major version.

---

############################################################

DEPRECATION PROCESS

############################################################

Mark Deprecated

↓

Document Replacement

↓

Emit Developer Warning

↓

Maintain Compatibility

↓

Provide Migration Guide

↓

Remove in Major Release

---

############################################################

VERSIONING

############################################################

Use Semantic Versioning.

MAJOR

Breaking changes

MINOR

Backward-compatible features

PATCH

Backward-compatible fixes

---

############################################################

RELEASE WORKFLOW

############################################################

Update Version

↓

Run Full CI Matrix

↓

Run Security Audit

↓

Run Migration Tests

↓

Build Production Assets

↓

Create Distribution Package

↓

Install Package on Clean WordPress

↓

Run Smoke Tests

↓

Generate Checksums

↓

Sign Release

↓

Publish Changelog

↓

Release

---

############################################################

BUILDING THE PLUGIN

############################################################

```bash
npm run build
composer install --no-dev --optimize-autoloader
composer package
```

Expected output:

```text
dist/oxy-ai-readiness.zip
```

The production package must exclude:

Tests

Source Maps unless required

Development Configuration

Git Files

Local Environment Files

CI Configuration

Uncompiled Assets

Documentation not intended for distribution

---

############################################################

DEBUG MODE

############################################################

Developer mode may expose:

Service Container

Registered Modules

Registered Standards

Event Timeline

REST Requests

Database Queries

Cache Events

Queue Jobs

Performance Metrics

Validation Details

---

Developer mode must never be enabled automatically in production.

---

############################################################

TROUBLESHOOTING

############################################################

Plugin Does Not Activate

Check PHP version.

Check WordPress version.

Run Composer autoload validation.

Check PHP error logs.

---

REST Route Returns 403

Check user capability.

Check authentication method.

Check nonce.

Check policy decision logs.

---

Audit Is Stuck

Check queue status.

Check cron availability.

Check failed jobs.

Check resource limits.

---

Generated File Is Not Published

Check filesystem permissions.

Check publishing mode.

Check validation result.

Check rollback history.

Check cache or CDN.

---

Score Is Not Updating

Check audit completion.

Check score provider registration.

Clear score cache.

Check failed background jobs.

---

############################################################

CONTRIBUTION RULES

############################################################

Contributors must:

Follow this guide

Respect module isolation

Provide tests

Update documentation

Avoid undocumented hooks

Avoid direct core modification

Avoid adding dependencies without review

Avoid collecting telemetry without approval

---

############################################################

NEW DEPENDENCY PROCESS

############################################################

Before adding a dependency, document:

Purpose

Alternatives

Package Size

Maintenance Status

License

Security History

WordPress Compatibility

PHP Compatibility

Frontend Impact

---

############################################################

DOCUMENTATION REQUIREMENTS

############################################################

Every public class should include useful documentation.

Every public API must include examples.

Every module must include a README.

Every standard must reference its specification.

Every breaking change must include a migration guide.

---

############################################################

MODULE README TEMPLATE

############################################################

```md
# Module Name

## Purpose

## Features

## Requirements

## Installation

## Configuration

## Capabilities

## REST Endpoints

## WP-CLI Commands

## Events

## Filters

## Database Tables

## Testing

## Changelog
```

---

############################################################

DEFINITION OF DONE

############################################################

A development task is complete only when:

Code is implemented

Architecture is respected

Tests pass

Static analysis passes

Permissions are verified

Failure paths are tested

Documentation is updated

Migration is included if needed

Performance impact is acceptable

Security impact is reviewed

CI is green

---

############################################################

FUTURE DEVELOPMENT TOOLING

############################################################

Module Scaffolding CLI

Standard Scaffolding CLI

Automatic OpenAPI Generation

Automatic Documentation Generation

Local Compatibility Lab

Module Hot Reload

Remote Debugging

AI-Assisted Code Review

AI-Assisted Test Generation

Automated Upgrade Analysis

---

# Success Criteria

A developer should be able to install the project, understand its architecture and implement a production-ready extension without modifying the core.

Every development workflow must be reproducible.

Every public extension point must be documented.

Every feature must meet architecture, testing, security and performance requirements before release.