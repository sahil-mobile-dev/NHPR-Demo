---
trigger: always_on
---

# NHPR Portal - Project Engineering Standards

Version: 1.0

These rules apply to the entire Laravel project.

Every generated code, file, feature, API integration, UI screen, controller, service, validation, database migration, and component MUST follow these standards.

---

# 1. General Principles

- Always write production-ready code.
- Never generate demo or placeholder implementations unless explicitly requested.
- Keep the code clean, scalable, and maintainable.
- Follow SOLID Principles.
- Follow DRY (Don't Repeat Yourself).
- Follow KISS (Keep It Simple).
- Avoid unnecessary complexity.
- Write self-explanatory code.
- Prefer readability over cleverness.

---

# 2. Laravel Standards

Always follow official Laravel conventions.

Follow:

- PSR-12 Coding Standard
- Laravel 12 Best Practices
- Laravel Service Layer Architecture
- Dependency Injection
- Route Model Binding
- Form Request Validation
- Config Driven Development

Never place business logic inside controllers.

Controllers should only:

- Validate Request
- Call Service
- Return Response

---

# 3. Project Structure

Use the following structure.

app/

    Http/
        Controllers/

    Services/

    Repositories/ (if required)

    Models/

    DTO/

    Helpers/

    Traits/

    Jobs/

    Events/

    Listeners/

    Policies/

resources/

    views/

public/

config/

routes/

database/

tests/

Never place random helper functions throughout the project.

---

# 4. Service Layer

Every external API must have its own Service.

Example

Services/

    GatewayTokenService.php

    AadhaarService.php

    OTPService.php

    HPRRegistrationService.php

Never mix multiple APIs inside one service.

One Service = One Responsibility.

---

# 5. Controllers

Controllers should remain lightweight.

Maximum responsibilities:

- Validate Request
- Call Service
- Handle Exceptions
- Return View/JSON

No business logic.

No HTTP calls.

No database logic.

---

# 6. Environment Variables

Never hardcode:

- API URLs
- API Keys
- Client Secret
- Client ID
- Tokens

Everything belongs inside

.env

Access only using

config()

Never use env() directly inside Services.

---

# 7. API Integration Standards

For every API:

Create

- Service
- DTO (if needed)
- Validation
- Error Handling
- Logging

Use Laravel HTTP Client.

Never use raw cURL.

Every request must include

- Timeout
- Retry
- Exception Handling
- Logging

---

# 8. Error Handling

Never expose internal errors.

Use

try/catch

Return meaningful messages.

Log every exception.

Never ignore exceptions.

---

# 9. Logging

Log:

API Request

API Response

Validation Errors

Unexpected Errors

Authentication Failures

Timeouts

Do not log secrets.

Never log:

Client Secret

Access Token

Refresh Token

Passwords

OTP

---

# 10. Validation

Always validate user input.

Use Form Request Validation.

Never validate inside Blade.

Validation messages should be user friendly.

---

# 11. Security

Escape every output.

Use CSRF Protection.

Use HTTPS.

Validate every request.

Prevent XSS.

Prevent SQL Injection.

Prevent Mass Assignment.

Never trust client input.

---

# 12. Authentication

Use Laravel Authentication.

Use Middleware.

Protect every internal page.

Use Roles & Permissions.

Never expose APIs without authorization.

---

# 13. UI/UX Standards

The application must feel modern, professional and enterprise-grade.

Avoid default Bootstrap appearance.

Create a premium interface.

Focus on usability.

Maintain consistency.

Every page should have:

- Proper spacing
- Good typography
- Responsive layout
- Meaningful icons
- Loading indicators
- Empty states
- Error states
- Success messages

Use:

Cards

Tables

Badges

Alerts

Breadcrumbs

Responsive Forms

Proper Buttons

Consistent Colors

Modern Shadows

Rounded Corners

Hover Effects

Transitions

---

# 14. User Experience

Every interaction should provide feedback.

Show:

Loading

Success

Failure

Validation

Progress

Disable buttons while processing.

Prevent duplicate submissions.

---

# 15. Forms

Every form should include

Required Indicators

Inline Validation

Loading State

Reset Option

Success Notification

Error Notification

Accessible Labels

Keyboard Navigation

---

# 16. API Response Handling

Handle

200

201

400

401

403

404

409

422

429

500

503

Never assume success.

---

# 17. Code Quality

Use

PHPStan

Laravel Pint

PHP CS Fixer

Follow

PSR-12

Strict typing wherever possible.

No duplicated code.

Meaningful variable names.

Meaningful function names.

---

# 18. Naming Convention

Controllers

UserController

Services

GatewayTokenService

Models

HealthcareProfessional

Views

token.blade.php

Routes

nhpr.token.generate

Variables

camelCase

Classes

PascalCase

Constants

UPPER_SNAKE_CASE

---

# 19. Database Standards

Use

Foreign Keys

Indexes

Unique Constraints

Soft Deletes where appropriate

Never use raw SQL unless necessary.

---

# 20. Git Standards

Small commits.

Meaningful commit messages.

Examples

feat: Add Gateway Token Integration

fix: Handle Unauthorized API Response

refactor: Extract Token Service

---

# 21. Testing

Every major feature should include

Feature Tests

Unit Tests

Service Tests

Validate success and failure scenarios.

---

# 22. Performance

Avoid N+1 Queries.

Use eager loading.

Use caching where applicable.

Paginate large datasets.

Lazy load when required.

---

# 23. Accessibility

Keyboard accessible.

Proper contrast.

ARIA labels when necessary.

Responsive for:

Desktop

Tablet

Mobile

---

# 24. Documentation

Every Service should include

Purpose

Inputs

Outputs

Exceptions

Example Usage

Complex logic must include comments.

---

# 25. NHPR Specific Standards

Generate new REQUEST-ID for every request.

Generate ISO8601 TIMESTAMP.

Read credentials only from config.

Reuse Access Token until expiration.

Refresh token when required.

Log all API failures.

Never expose NHPR secrets.

---

# 26. Before Completing Any Feature

Verify

✓ Code Compiles

✓ No PHP Errors

✓ No Laravel Errors

✓ Validation Works

✓ UI Responsive

✓ API Works

✓ Exceptions Handled

✓ Logging Added

✓ Security Reviewed

✓ Production Ready

---

# 27. Final Rule

Every feature generated should be production-ready, scalable, secure, maintainable, reusable, and follow Laravel best practices.

Never generate quick fixes or temporary implementations unless explicitly requested.