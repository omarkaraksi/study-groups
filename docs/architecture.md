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

## 9. Localization Architecture

### Overall Architecture
The project has two independent localization systems with clear architectural boundaries:

**Laravel Backend** is responsible for:
- API localization (validation messages, success/error messages)
- Admin panel localization
- User locale preference management
- Database content translations
- Accept-Language header handling
- Locale resolution

**React Frontend** is responsible for:
- React UI translations
- Locale state/context management
- RTL/LTR handling
- Date/time/number formatting
- Frontend UI mirroring

**Architectural Independence**: React must NOT depend on Laravel translation files. Laravel must NOT depend on React translation files. The API is the communication boundary between them.

### Supported Languages
- English (`en`) - default and fallback locale
- Arabic (`ar`) - Right-to-Left (RTL)

Architecture allows adding future languages without redesigning the system.

### User Locale Storage
Since `user_profiles` exists and is the established location for user preferences in the database architecture:
- User locale preference is stored in `user_profiles.locale` column
- This extends the existing profile structure without creating unnecessary architecture

### Locale Resolution Strategy
Clear priority order for determining the current locale:

1. **Explicitly requested valid locale** - via `?locale=ar` query parameter (must be validated, must NOT bypass authorization/security)
2. **Authenticated user's preferred locale** - from `user_profiles.locale`
3. **Accept-Language HTTP header** - browser preference
4. **Default locale** - `en` (English)

Only supported locales may be selected.

### Laravel Localization Integration
Uses Laravel's built-in translation system:
- Language files stored in `resources/lang/{locale}/`
- PHP translation files for Blade views (Admin panel)
- JSON translation files for API responses
- Middleware to detect and set user's locale via the resolution strategy

### Translation File Structure
```text
resources/lang/
├── en/
│   ├── json/
│   │   ├── messages.json      # API messages
│   │   ├── validation.json    # Validation messages
│   │   └── ...
│   ├── auth.php             # Admin/auth messages
│   ├── pagination.php        # Pagination labels
│   └── ...
└── ar/
    ├── json/
    │   ├── messages.json
    │   ├── validation.json
    │   └── ...
    ├── auth.php
    ├── pagination.php
    └── ...
```

### React Localization Integration
React uses its own independent translation system:
- Separate translation files/resources (NOT reading Laravel's files)
- Locale context/state for managing current locale
- RTL CSS classes applied conditionally for Arabic
- Date/time/number formatting with locale-aware libraries
- Text alignment and UI mirroring for RTL
- Receives user's locale preference from Laravel via API

### Database Content Translations
**Supported from the beginning** - NOT deferred to a future feature.

**Reusable Translation Pattern**:
```text
Main Entity Table (e.g., study_groups)
├── id
├── slug
├── created_by
├── status
└── ... (language-independent fields only)

Translation Table (e.g., study_group_translations)
├── id
├── study_group_id  (FK to main entity)
├── locale          (e.g., 'en', 'ar')
├── name            (translated)
├── description     (translated)
├── rules           (translated, if applicable)
└── UNIQUE(study_group_id, locale)
```

**Translation Table Requirements**:
- Contains parent entity ID (foreign key)
- Contains locale field
- Contains all genuinely translatable fields
- Must have uniqueness constraint: `unique(parent_id, locale)`
- Prevents duplicate translations for the same entity and locale

**Entities to Apply Pattern**:
- Study Groups → `study_group_translations`
- Courses → `course_translations`
- Books → `book_translations`
- Learning Materials → `learning_material_translations`
- Categories → `category_translations`
- Subjects → `subject_translations`

**Exclusion**: Do NOT create translation tables for fields that are NOT language-dependent.

### Translation Fallback Strategy
Consistent fallback across all entities:
1. Attempt to retrieve translation for requested locale
2. If not found, fallback to English (`en`) translation
3. If English translation also not found, return empty/null (entity may not be properly configured)

This ensures users always see content in either their preferred language or English, never broken content.

### RTL / LTR Implementation

**Backend-Wide RTL/LTR Support** (Laravel Admin UI):
- **Central Control**: Document direction is controlled centrally via the main layout files
- **Direction Logic**: `ar` locale → `dir="rtl"`, all other locales → `dir="ltr"`
- **Tabler RTL Integration**: Uses Tabler's built-in RTL CSS (`tabler.rtl.min.css`) for Arabic locale
- **Automatic Inheritance**: All admin pages extend shared layouts that include direction control

**Implementation Details**:
- **Main Layout** (`resources/views/admin/layouts/app.blade.php`):
  - Sets `dir` attribute on `<html>` element based on current locale
  - Conditionally includes Tabler RTL CSS when `app()->getLocale() === 'ar'`
- **Login Layout** (`resources/views/admin/login.blade.php`):
  - Same direction and RTL CSS logic for the login page
- **Direction Mapping**:
  - `en` → `ltr` (Left-to-Right)
  - `ar` → `rtl` (Right-to-Left)

**Shared Components Covered**:
- Main layout wrapper
- Header/navbar
- Sidebar/navigation  
- Tables
- Forms
- Buttons
- Alerts/notifications
- Pagination
- Modals
- All existing and future Admin CRUD pages inherit RTL/LTR automatically

- **React Frontend**: Independently manages direction based on its locale context

### Language Switcher Implementation

**User-Facing Language Switcher** for Laravel Admin UI:
- **Location**: Integrated into the shared navbar (`resources/views/admin/layouts/navbar.blade.php`)
- **Placement**: In the top-right dropdown menu alongside user profile
- **UI**: Dropdown with language icons and names (English/عربية)
- **Accessibility**: Available on all admin pages including login page

**Controller & Routing**:
- **Controller**: `LocaleController` (`app/Http/Controllers/Admin/LocaleController.php`)
- **Route**: `GET /admin/locale/{locale}` named `admin.locale.switch`
- **Supported**: Both authenticated and guest users
- **Method**: `switch()` handles locale change with proper validation

**Persistence Mechanism**:
- **Authenticated Users**: Locale stored in `user_profiles.locale` (existing mechanism)
- **Guests**: Locale stored in session via `session()->put('locale', $locale)`
- **Validation**: Only supported locales (`en`, `ar`) are accepted
- **Safety**: Unsupported locales return 400 error with clear message

**Integration with RTL/LTR**:
- Language switcher automatically triggers direction change via locale update
- Switching to Arabic (`ar`) → RTL direction + RTL CSS loaded
- Switching to English (`en`) → LTR direction + RTL CSS not loaded
- Seamless integration with existing RTL/LTR system

**Behavior**:
- Switching language immediately updates the UI direction
- Persistence ensures the selected language is maintained across sessions
- Visual feedback shows currently selected language in switcher
- Active language highlighted in dropdown menu

### API Localization
API supports localized content via:
- Validation error messages from Laravel translation files
- Success/error messages from Laravel translation files
- Translatable database content returned based on resolved locale
- Locale determination via the resolution strategy (NOT coupled to React implementation)

API responses include:
- All translatable fields in the resolved locale
- Fallback to English if requested locale translation unavailable
- Locale metadata in response headers (optional, for debugging)

### Core Architectural Decisions
1. **Separate Systems**: Laravel and React have independent translation systems
2. **API Boundary**: All communication via API, no direct file sharing
3. **User Profile Extension**: Use existing `user_profiles` table for locale storage
4. **Database Pattern**: Reusable translation table pattern for all translatable entities
5. **Fallback to English**: Consistent fallback strategy across entire application
6. **RTL from Start**: Full RTL support for Arabic from day one
7. **Future-Ready**: Adding new languages requires only new locale files and translations

### Mandatory Localization Rule — Permanent Project-Wide Definition of Done (Backend/Admin)

> **PERMANENT PROJECT-WIDE RULE — Effective for all future Backend/Admin features. Localization is part of the Definition of Done. A feature is NOT complete without it. See also §17 Core Rules (Rule 19) and §20 Status. UI/System translations and database content translations are separate concerns.**

The project supports:

* English (`en`) — default and fallback locale, Left-to-Right (LTR)
* Arabic (`ar`) — Right-to-Left (RTL)

Every new Backend/Admin feature MUST be localization-ready from the beginning. Do not implement a feature first and translate it later. Localization must be part of the feature implementation.

#### UI Localization — No Hard-Coded Translatable Text

Any user-facing text that is translatable MUST use the existing Laravel localization system (`resources/lang/{en,ar}/`, `__()`, `@lang()`). **Never hard-code translatable UI text directly in application/UI code.**

This applies to **ALL** Backend/Admin UI, including but not limited to:

* Sidebar navigation / Navbar / Header / Main layouts / Navigation menus / Shared layouts & components
* Page titles / Breadcrumbs / Tabs / Dropdowns / Tooltips
* Buttons / Form labels / Placeholders / Filters / Search UI
* Table headers / Pagination / Empty states
* Alerts / Notifications / Validation messages / Confirmation messages / Modals / Status & action labels
* Permissions/roles UI and any other user-facing translatable text

This applies equally to shared UI — sidebar, navbar, main layouts, navigation menus, and shared components must not introduce hard-coded translatable text.

#### Translation Requirement — EN + AR Required

Every new translatable UI string MUST have:

* English (`en`) translation in `resources/lang/en/`
* Arabic (`ar`) translation in `resources/lang/ar/`

English strings are the source of truth. Keys must be consistent.

#### RTL/LTR — Preserve Centralized System

The existing centralized direction system must be preserved:

* English → `dir="ltr"`
* Arabic → `dir="rtl"` + Tabler RTL CSS (`tabler.rtl.min.css`)

Every new UI feature must work correctly in both LTR and RTL. All Admin pages inherit direction from the shared layouts (`resources/views/admin/layouts/app.blade.php` and `resources/views/admin/login.blade.php`). **Do not add feature-specific RTL logic when the shared system already handles it.**

#### Database Content vs UI/System Text — Separate Concerns

* **UI/System text** (labels, buttons, messages, navigation, etc.) → Laravel translation resources (`resources/lang/`).
* **Translatable database content** (e.g., Study Group `name`, `description`, `rules`) → Existing database translation architecture — separate `{entity}_translations` tables with `locale`, `UNIQUE(parent_id, locale)`, and application-level fallback to `en` (see §9 Database Content Translations and `docs/database.md §18`).

If a new entity contains translatable database content, follow the established database translation pattern documented in the project. Do NOT replace it with a generic translation framework unless explicitly required by architecture.

#### Definition of Done — Localization Checklist

A Backend/Admin feature is **NOT complete** unless:

1. All translatable UI text has English (`en`) translations.
2. All translatable UI text has Arabic (`ar`) translations.
3. No unnecessary hard-coded translatable UI strings were introduced.
4. The feature works in English (LTR).
5. The feature works in Arabic (RTL).
6. RTL/LTR behavior works correctly via the shared centralized system.
7. Relevant tests are added or updated when appropriate (including RTL/LTR and locale coverage where applicable).

This rule is enforced via code review and the Definition of Done. Refer to §17 (Rule 19) and `docs/requirements.md §17` for context.

### Core Rules for Localization (Retained)
1. All static text must be translatable
2. Never hardcode user-facing strings
3. Use consistent translation keys across Laravel and React
4. English strings are the source of truth
5. RTL support is mandatory for Arabic - implemented centrally in shared layouts
6. Database content uses translation tables, not multi-language columns
7. Laravel and React localization systems remain independent
8. API is the only integration point between frontend and backend localization

## 10. Repository Strategy
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

## 11. Transactions
Use transactions when multiple writes must succeed/fail atomically.

Example:

```text
ApproveJoinRequest
  |
  +-- update request
  +-- create membership
  +-- commit
```

## 12. Events and Jobs
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

## 13. Search Architecture

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

## 14. JoinStudyGroup Example

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

## 15. Admin Dashboard
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

## 16. Testing
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

## 17. Core Rules
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
19. **Localization is part of Definition of Done — every Backend/Admin feature must ship with EN+AR UI translations, no hard-coded translatable text, RTL/LTR verified (see §9 Mandatory Localization Rule).**

## 18. OpenCode Boundary
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

## 19. ADRs
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

## 20. Status
Architecture v1.0 is ready for implementation. Amended with **Mandatory Localization Rule (§9 + §17 Rule 19)** as a permanent project-wide Definition of Done for all future Backend/Admin features — enforced for every vertical slice going forward.

Next:
1. Laravel project setup
2. Tabler integration
3. Authentication/admin foundation
4. First vertical feature: Create Study Group
5. All future vertical slices must satisfy the Mandatory Localization Rule (EN+AR translations, no hard-coded UI text, RTL/LTR verified)
