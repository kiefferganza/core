# Coding Style

This document provides comprehensive guidelines for AI assistants when working on the JamTime Laravel application. These guidelines are based on analysis of the existing codebase patterns and architectural decisions.

## Table of Contents
- [Architecture Overview](#architecture-overview)
- [Actions Pattern](#actions-pattern)
- [Policies Pattern](#policies-pattern)
- [Models and Relationships](#models-and-relationships)
- [Services Pattern](#services-pattern)
- [DTOs (Data Transfer Objects)](#dtos-data-transfer-objects)
- [Controllers](#controllers)
- [Testing Patterns](#testing-patterns)
- [File Organization](#file-organization)
- [Naming Conventions](#naming-conventions)
- [Best Practices](#best-practices)

## Architecture Overview

This Laravel application follows a clean architecture pattern with distinct layers:

- **Actions**: Business logic encapsulation for specific operations
- **Services**: Complex business logic and external service integrations
- **DTOs**: Data transfer between layers
- **Policies**: Authorization logic
- **Models**: Data layer with traits for reusable functionality
- **Controllers**: HTTP request/response handling (thin layer)
- **Modules**: Feature-based modular organization (excluded from analysis per request)

## Actions Pattern

### When to Use Actions

Use Actions for:
- **Single-responsibility business operations** that are reusable across different contexts
- **Complex business logic** that involves multiple models or services
- **Operations with side effects** like notifications, caching, or external API calls
- **Transaction-wrapped operations** that need to be atomic

### Action Structure

```php
<?php

namespace App\Actions\{Domain};

use Illuminate\Support\Facades\DB;

class {ActionName}Action
{
    public function handle({parameters}): {return_type}
    {
        return DB::transaction(function () use ({parameters}) {
            // Business logic here
            // Side effects (notifications, etc.)
            return $result;
        });
    }
}
```

### Examples from Codebase

1. **DidPracticeTodayAction**: Handles streak logging with notifications
2. **AddStudentAction**: Generates invite codes with caching logic
3. **AddNoteAction**: Creates notes with instrument validation

### Action Guidelines

- Use descriptive names ending with "Action"
- Place in domain-specific subdirectories (`Student/`, `Teacher/`)
- Wrap database operations in transactions when needed
- Handle side effects like notifications within the action
- Use dependency injection for services and repositories
- Return meaningful values or objects

## Policies Pattern

### When to Use Policies

Use Policies for:
- **Authorization logic** that determines if a user can perform an action
- **Complex permission checks** involving relationships between models
- **Reusable authorization rules** across multiple controllers
- **Role-based access control** with business rules

### Policy Structure

```php
<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class {Model}Policy
{
    use HandlesAuthorization;

    public function {action}({User} $user, {Model} $model): Response
    {
        if (/* condition fails */) {
            return Response::deny('Descriptive error message.');
        }

        return Response::allow();
    }
}
```

### Examples from Codebase

1. **StudentPolicy**: Manages teacher-student relationships
2. **StudentNotePolicy**: Handles note CRUD permissions with instrument validation

### Policy Guidelines

- Always return `Response::allow()` or `Response::deny()` with descriptive messages
- Use relationship methods to check permissions (`$user->hasStudent($student)`)
- Group related permissions in the same policy
- Use meaningful method names that describe the action
- Handle edge cases with clear error messages

## Models and Relationships

### Model Organization

- **Base models** in `app/Models/`
- **Traits** in `app/Models/Traits/` for reusable functionality
- **Contracts** in `app/Models/Contracts/` for interface definitions

### Trait Usage Pattern

Use traits for:
- **Reusable model behavior** across multiple models
- **Relationship definitions** that are shared
- **Business logic** specific to model capabilities

### Examples from Codebase

1. **HasInstruments**: Manages instrument relationships
2. **HasStreak**: Handles streak logging functionality
3. **ManagesStudent**: Teacher-student relationship methods

### Model Guidelines

- Use contracts/interfaces for defining model capabilities
- Implement traits for shared behavior
- Use descriptive relationship method names
- Leverage Eloquent features like query scopes
- Follow Laravel naming conventions for relationships

## Services Pattern

### When to Use Services

Use Services for:
- **Complex business logic** that doesn't fit in a single action
- **External API integrations** (payment gateways, email services)
- **Cross-cutting concerns** like caching, logging
- **Algorithm implementations** and complex calculations

### Service Structure

```php
<?php

namespace App\Services;

class {ServiceName}Service
{
    public function {method}({parameters}): {return_type}
    {
        // Complex business logic
        // External service calls
        // Calculations and algorithms
    }
}
```

### Examples from Codebase

1. **CacheService**: Implements stale-while-revalidate caching pattern
2. **MarketingManager**: Handles marketing platform integrations (Klaviyo, Mailchimp)

### Service Guidelines

- Use for complex, stateless operations
- Implement interfaces for external services
- Use manager pattern for multiple implementations
- Make methods static when appropriate
- Handle errors gracefully with meaningful messages

## DTOs (Data Transfer Objects)

### When to Use DTOs

Use DTOs for:
- **Data transfer between layers** (API requests/responses)
- **Validation and type safety** for complex data structures
- **Immutable data containers** for passing data
- **API resource transformation** data preparation

### DTO Structure

```php
<?php

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;

class {Name}DTO implements \JsonSerializable, Arrayable
{
    public function __construct(
        public readonly string $property1,
        public readonly ?string $property2 = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            property1: (string) ($data['property1'] ?? ''),
            property2: $data['property2'] ?? null,
        );
    }

    public function toArray(array $except = []): array
    {
        return Arr::except([
            'property1' => $this->property1,
            'property2' => $this->property2,
        ], $except);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
```

### DTO Guidelines

- Use readonly properties for immutability
- Implement `JsonSerializable` and `Arrayable` interfaces
- Provide `fromArray()` static constructor
- Use type hints for all properties
- Include `toArray()` method with optional exclusions

## Controllers

### Controller Philosophy

Controllers should be **thin** and act as HTTP coordinators:
- Handle HTTP requests/responses
- Delegate business logic to Actions/Services
- Handle authorization checks
- Format responses using Resources

### Controller Structure

```php
<?php

namespace App\Http\Controllers\{Domain};

use App\Actions\{Domain}\{Action}Action;
use App\Http\Controllers\Controller;

class {Name}Controller extends Controller
{
    public function {method}({Request} $request, {Action}Action $action): JsonResponse
    {
        $this->authorize('action-name', $model);
        
        $result = $action->handle($request->validated());
        
        return response()->json(['data' => $result]);
    }
}
```

### Controller Guidelines

- Organize in domain subdirectories (`Teacher/`, `Student/`)
- Use action classes for business logic
- Always authorize actions using policies
- Use form requests for validation
- Use resource classes for response formatting
- Keep methods focused on HTTP concerns

## Testing Patterns

### Test Organization

- Use **Pest testing framework**
- **Feature tests** for HTTP endpoints
- **Unit tests** for isolated logic
- Group related tests using `describe()` blocks

### Test Structure

```php
<?php

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

beforeEach(function () {
    // Setup test data
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('Feature Name', function () {
    test('it should do something successfully', function () {
        $response = postJson('/api/endpoint', $payload);
        
        $response->assertOk()
            ->assertJsonStructure(['data' => []]);
    });
    
    test('it should handle error cases', function () {
        $response = postJson('/api/endpoint', $invalidPayload);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['field']);
    });
})->group('feature-group', 'related-feature');
```

### Testing Guidelines

- Use descriptive test names with "it should..."
- Set up test data in `beforeEach()` hooks
- Test both success and failure scenarios
- Use specific assertion methods (`assertCreated()`, `assertForbidden()`)
- Group tests logically with meaningful names
- Mock external services appropriately
- Test authorization policies thoroughly

## File Organization

### Directory Structure

```
app/
├── Actions/
│   ├── Student/
│   └── Teacher/
├── DTOs/
├── Enums/
├── Http/
│   ├── Controllers/
│   │   ├── Student/
│   │   └── Teacher/
│   └── Requests/
├── Models/
│   ├── Contracts/
│   └── Traits/
├── Policies/
└── Services/
    └── Marketing/
```

### Organization Guidelines

- Group by domain/feature when possible
- Use subdirectories for related classes
- Keep interfaces/contracts in separate directories
- Follow Laravel conventions for standard directories

## Naming Conventions

### Class Naming

- **Actions**: `{Verb}{Noun}Action` (e.g., `AddStudentAction`)
- **Policies**: `{Model}Policy` (e.g., `StudentPolicy`)
- **DTOs**: `{Purpose}DTO` (e.g., `NoteDTO`)
- **Services**: `{Purpose}Service` (e.g., `CacheService`)
- **Traits**: `{Capability}` (e.g., `HasInstruments`)
- **Contracts**: `I{Model}` (e.g., `IStudent`)

### Method Naming

- **Actions**: `handle()` for main execution
- **Policies**: Action-based names (`create`, `update`, `managesStudent`)
- **Services**: Descriptive action names
- **Models**: Laravel conventions for relationships

## Best Practices

### General Guidelines

1. **Single Responsibility**: Each class should have one reason to change
2. **Dependency Injection**: Use constructor injection for dependencies
3. **Type Declarations**: Always use return types and parameter types
4. **Error Handling**: Provide meaningful error messages
5. **Documentation**: Use PHPDoc blocks for complex methods

### Code Quality

- Use PHP 8+ features (constructor property promotion, union types)
- Leverage Laravel's built-in features before custom solutions
- Write self-documenting code with clear names
- Handle edge cases explicitly
- Use transactions for data consistency

### Performance Considerations

- Implement caching strategies using services
- Use eager loading to prevent N+1 queries
- Consider query optimization in repositories
- Use background jobs for time-consuming operations

### Security

- Always authorize actions using policies
- Validate input using form requests
- Use HTTPS for external links (as enforced in tests)
- Sanitize user input appropriately

---

This document should be updated as the codebase evolves and new patterns emerge. When in doubt, follow existing patterns in the codebase and Laravel best practices.
