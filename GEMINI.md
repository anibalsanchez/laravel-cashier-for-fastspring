# Gemini Context for Laravel Cashier for Fastspring

This document outlines the project's technical stack, coding standards, and architectural conventions for the `laravel-cashier-for-fastspring` package. It combines the universal best practices from the broader Photalika ecosystem with the specific requirements of this package.

## 1. Project Overview

`laravel-cashier-for-fastspring` is a backend Laravel package that provides an eloquent, Cashier-like interface to Fastspring's subscription and payment billing services. It handles subscriptions, accounts, invoices, and webhook events.

## 2. Development Stack

**Backend:**

- **PHP:** ^8.2 (Development on ^8.3)
- **Laravel Framework Components:** v12.0 (`illuminate/database`, `illuminate/support`, etc.)
- **External APIs:** Guzzle (`guzzlehttp/guzzle`), MoneyPHP (`moneyphp/money`)

**Testing & Quality Assurance:**

- **Framework:** PHPUnit 12 (`phpunit/phpunit`)
- **Package Testing:** Orchestra Testbench 10 (`orchestra/testbench`)
- **Mocking:** Mockery (`mockery/mockery`)
- **Formatting:** Laravel Pint (`laravel/pint`)
- **Database:** SQLite in-memory for testing via Testbench Traits.

## 3. Coding Standards & Best Practices

### 3.1 General Principles

- **Clean Code:** Meaningful Names, Small Functions (≤20-30 lines), Single Responsibility, DRY, KISS, YAGNI. Favor readable formatting (80-120 chars line length) and comment sparingly to explain "why" rather than "what".
- **SOLID Principles:** Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion.
- **Design Guidelines:** Favor Composition over Inheritance, Encapsulate What Varies, Tell, Don't Ask.

### 3.2 PHP Specific

- **PHP Version:** Features up to PHP 8.2 (like readonly properties, constructor property promotion, nullsafe operator).
- **Standards:** PSR-1, PSR-2, PSR-12 (enforced by Laravel Pint).
- **Strict Types:** `declare(strict_types=1);` at the top of every new PHP file.
- **Type Declarations:** Always use explicit return types (including `void`), short nullable notation (`?string`), and typed properties.
- **Control Flow:** "Happy path last" (early returns), avoid excessive `else` blocks, separate conditions, and always use curly braces.
- **Naming:** PascalCase for classes/traits, camelCase for methods/variables. Descriptive names without abbreviations.
- **Arrays & Collections:** Prefer built-in array functions (`array_filter`, `array_map`) or Laravel Collections over manual `foreach` loops when transforming data.
- **Whitespace:** Blank lines between statements for readability, no extra empty lines in `{}`.

### 3.3 Testing Best Practices

- **PHPUnit 12:** Utilize native attributes (e.g., `#[Test]`, `#[DataProvider]`) and modern assertions.
- **Testbench:** Rely on Orchestra Testbench for setting up the Laravel environment, migrations, and database state during tests.
- **Coverage:** Ensure adequate coverage for Models, Helpers, Listeners, and the Cashier facade.

## 4. Architecture & Project Structure

As a Laravel package, the structure is focused on exportable resources rather than a monolithic app structure:

- `src/`: Core logic, models, events, listeners, and the Cashier facade.
  - `Models/`: Eloquent models (e.g., `Account`, `Subscription`, `Invoice`).
  - `Helpers/`: Utility classes (e.g., `MoneyHelper`, `SubscriptionBuilder`).
  - `Concerns/`: Traits used by Billable models (`ManagesSubscriptions`, `ManagesInvoices`).
  - `Fastspring/`: Fastspring API Client implementations.
  - `Http/Controllers/`: Webhook handling logic.
- `database/migrations/`: Package migrations.
- `config/`: Configuration files to be published (`fastspring.php`).
- `tests/`: PHPUnit tests mirroring the `src/` structure where possible.

### Key Conventions

- **Models:** Use relationships extensively. Avoid `$fillable` where explicit property assignment or `Model::unguard()` (in tests) is more appropriate.
- **Events & Webhooks:** Fastspring webhooks trigger specific internal events (e.g., `SubscriptionActivated`), which are then handled by mapped Listeners.
- **Cashier Facade:** `src/Cashier.php` acts as the primary registry for configuring custom models, formats, and webhooks.

## 5. Security

- **Webhooks:** Webhook signatures from Fastspring must be validated using the configured HMAC secret.
- **Data Protection:** Never log sensitive customer data or complete API credentials.
- **Dependencies:** Regularly update `guzzlehttp/guzzle` and illuminate components to patch vulnerabilities.

## 6. Development Workflow

### Useful Commands

- **Run Tests:** `vendor/bin/phpunit`
- **Format Code:** `vendor/bin/pint`
- **Test Coverage:** `vendor/bin/phpunit --coverage-html=coverage`

### Before Finalizing Any Task

1. Verify the logic changes locally.
2. Ensure new files have `declare(strict_types=1);` and proper namespaces.
3. Run `rector` and `vendor/bin/pint` to enforce styling rules.
4. Run `vendor/bin/phpunit` to ensure all tests pass (including any newly written tests).
