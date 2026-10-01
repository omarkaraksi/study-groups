# Study Groups — Architecture v1.0

## 1. Goal
The project is both a learning project and a portfolio project.

> Simple by default; complexity only when justified by a real requirement.

Patterns and abstractions must solve an identifiable problem.

## 2. High-Level Architecture

```text
React Frontend
      |
      v
Laravel Backend
      |
      +-- HTTP/API
      +-- Application
      +-- Domain
      +-- Infrastructure
      |
      +---- MySQL
      +---- Elasticsearch
      +---- File Storage
```

React is the user-facing frontend.

Laravel owns authentication, authorization, validation, business workflows, persistence, API responses, events/jobs, notifications, and search integration.

MySQL is the source of truth.

Elasticsearch is the search/index layer and may be eventually consistent.

## 3. Backend Layers

### HTTP
Responsibilities:
- Routing
- Controllers
- Form Requests
- API Resources
- HTTP-specific concerns

Controllers stay thin.

### Application
Responsibilities:
- Use cases
- Business workflows
- Coordination
- Transactions
- Dispatching events/jobs

Examples:
- CreateStudyGroup
- UpdateStudyGroup
- JoinStudyGroup
- LeaveStudyGroup
- ApproveJoinRequest
- CreateStudySession
- AddBookReview

Not every simple read requires a Use Case.

### Domain
Responsibilities:
- Business concepts
- Business rules
- Domain models
- Domain exceptions
- Policies where appropriate
- Domain events
- Contracts only when justified

### Infrastructure
Responsibilities:
- Persistence implementations when abstraction is needed
- Elasticsearch
- External APIs
- File storage
- Technical integrations

## 4. Suggested Project Structure

```text
app/
├── Domain/
│   ├── Users/
│   ├── StudyGroups/
│   ├── Courses/
│   ├── Books/
│   ├── LearningMaterials/
│   └── StudySessions/
│
├── Application/
│   ├── Users/
│   ├── StudyGroups/
│   ├── Courses/
│   ├── Books/
│   └── StudySessions/
│
├── Infrastructure/
│   ├── Persistence/
│   ├── Search/
│   ├── Storage/
│   └── ExternalServices/
│
└── Http/
    ├── Controllers/
    ├── Requests/
    └── Resources/
```

The structure grows with actual features; do not create empty abstractions prematurely.

## 5. Domain Structure

Example:

```text
Domain/
└── StudyGroups/
    ├── Models/
    ├── Policies/
    ├── Events/
    ├── Exceptions/
    └── Contracts/
```

Eloquent models may live in Domain in this moderate Laravel architecture. A fully separate pure-domain model layer is intentionally avoided until there is a real reason.

## 6. Application Structure

Use-case oriented:

```text
Application/
└── StudyGroups/
    ├── CreateStudyGroup.php
    ├── UpdateStudyGroup.php
    ├── JoinStudyGroup.php
    ├── LeaveStudyGroup.php
    ├── ApproveJoinRequest.php
    └── CreateStudySession.php
```

Avoid one giant `StudyGroupService`.

Avoid creating Use Cases solely to increase file count.

## 7. API
Version:
`/api/v1`

Examples:
```text
GET    /api/v1/study-groups
GET    /api/v1/study-groups/{slug}
POST   /api/v1/study-groups
PATCH  /api/v1/study-groups/{slug}
DELETE /api/v1/study-groups/{slug}
POST   /api/v1/study-groups/{slug}/join
```

Use Laravel API Resources and pagination.

Typical statuses:
- 200
- 201
- 204
- 401
- 403
- 404
- 422

## 8. Authentication vs Authorization
Authentication answers: Who is this user?

Authorization answers: Is this user allowed to perform this action?

Use:
- Roles
- Permissions
- Policies/Gates

Ownership is a policy condition, not a universal bypass.

## 9. Repository Strategy
Repositories are optional.

Eloquent already provides persistence capabilities. Do not create a repository for every model.

Introduce a repository or abstraction when there is a concrete boundary, such as:
- Multiple data sources
- Complex persistence logic
- External providers
- A meaningful infrastructure substitution

Elasticsearch should have a clear search boundary.

Example:

```text
Domain/StudyGroups/Contracts/StudyGroupRepository.php
Infrastructure/Persistence/Eloquent/EloquentStudyGroupRepository.php
```

Only add this when the feature actually benefits from it.

## 10. Transactions
Use transactions when multiple writes must succeed/fail atomically.

Example:

```text
ApproveJoinRequest
  |
  +-- update request
  +-- create membership
  +-- commit
```

## 11. Events and Jobs
Use events when an action produces secondary effects.

Example:

```text
StudyGroupJoinApproved
        |
        +-- Notification
        +-- Activity
        +-- Future integrations
```

Do not create events for every CRUD operation.

Use jobs for slow/non-critical work such as:
- Search indexing
- External synchronization
- Large notification batches

## 12. Search Architecture

```text
React
  |
  v
Search API
  |
  v
Search Service
  |
  +--> Elasticsearch
  |
  +--> MySQL when relational data is required
```

MySQL remains authoritative.

Elasticsearch is eventually consistent.

Database changes can dispatch indexing jobs/events.

Soft-deleted resources must disappear from search results.

## 13. JoinStudyGroup Example

```text
POST /api/v1/study-groups/{slug}/join
          |
          v
Controller
          |
          v
Form Request
          |
          v
Authorization
          |
          v
JoinStudyGroup
          |
          +-- active?
          +-- already member?
          +-- full?
          +-- public/private?
          |
          v
Transaction
          |
          +-- public  -> membership
          +-- private -> join request
```

Domain methods may express rules:

```php
$group->hasMember($user);
$group->isFull();
$group->requiresJoinApproval();
```

The Use Case coordinates the workflow.

No repository is required unless a real infrastructure boundary appears.

## 14. Admin Dashboard
Admin UI:
- Laravel Blade
- Tabler HTML template

Suggested:

```text
resources/views/admin/
├── layouts/
│   ├── app.blade.php
│   ├── navbar.blade.php
│   ├── sidebar.blade.php
│   └── footer.blade.php
├── dashboard.blade.php
├── users/
├── study-groups/
├── study-sessions/
├── courses/
└── books/
```

The admin UI is presentation only; business authority remains in Laravel.

## 15. Testing
```text
tests/
├── Feature/
└── Unit/
```

Feature tests verify behavior through application boundaries.

Examples:
- User can create a group
- Unauthorized user cannot update another user's group
- Public group can be joined
- Private group creates a pending request
- Approved request creates membership

Unit tests are used for isolated rules where they add value.

## 16. Core Rules
1. Controllers stay thin.
2. Workflows belong in Application.
3. Domain contains business concepts/rules.
4. Infrastructure handles technical integrations.
5. MySQL is source of truth.
6. Elasticsearch is search layer.
7. Authorization is server-side.
8. Never trust client authorization data.
9. Use transactions for critical multi-record workflows.
10. API Resources define stable API responses.
11. Database changes require review.
12. Abstractions require a concrete reason.
13. Prefer simple solutions.
14. Avoid duplicated business rules.
15. Soft-delete normal independent entities.
16. Do not implement future features prematurely.
17. Tests protect behavior.
18. Security rules are backend-enforced.

## 17. OpenCode Boundary
OpenCode is an implementation agent, not the architecture owner.

```text
Requirement
    |
    v
Architecture decision
    |
    v
Human approval
    |
    v
OpenCode implementation
    |
    v
Tests
    |
    v
Review
```

OpenCode must not change database architecture without proposing the change and explaining why.

It should not introduce patterns merely because they are common.

## 18. ADRs
Initial ADRs:
- ADR-001: MySQL + Elasticsearch Search Architecture
- ADR-002: Repository Strategy
- ADR-003: Layered Domain/Application/Infrastructure Architecture

Each ADR records:
- Context
- Decision
- Reasons
- Consequences
- Alternatives

## 19. Status
Architecture v1.0 is ready for implementation.

Next:
1. Laravel project setup
2. Tabler integration
3. Authentication/admin foundation
4. First vertical feature: Create Study Group
