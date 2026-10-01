# Study Groups — Database Architecture v1.0

## 1. Global Rules
- Primary keys: `BIGINT UNSIGNED`
- Table names: plural snake_case
- Foreign keys: `{singular_table}_id`
- Public resources use unique slugs
- Soft deletes for normal entities with independent lifecycles
- Pivot tables are not soft-deleted
- Notifications/reports/point transactions are retained records
- MySQL is the source of truth
- Elasticsearch is the search layer
- Polymorphic relationships are enforced at application level

## 2. Users
### users
- id PK
- username UNIQUE nullable
- first_name
- last_name
- email UNIQUE
- password
- email_verified_at nullable
- status
- remember_token nullable
- timestamps
- deleted_at
- index(status)

### user_profiles
- id
- user_id UNIQUE FK users
- bio nullable
- avatar nullable
- date_of_birth nullable
- academic_level_id nullable
- institution nullable
- field_of_study nullable
- city nullable
- area nullable
- timestamps
- deleted_at

### academic_levels
- id
- name UNIQUE
- slug UNIQUE
- description nullable
- is_active
- timestamps
- deleted_at

### interests
- id
- name UNIQUE
- slug UNIQUE
- description nullable
- is_active
- timestamps
- deleted_at

### user_interests
- user_id
- interest_id
- composite PK(user_id, interest_id)

### subjects
- id
- name UNIQUE
- slug UNIQUE
- description nullable
- timestamps
- deleted_at

### user_subjects
- user_id
- subject_id
- composite PK(user_id, subject_id)

## 3. Authorization
### roles
- id
- name UNIQUE
- slug UNIQUE
- description
- is_active
- timestamps
- deleted_at

### permissions
- id
- name UNIQUE
- slug UNIQUE
- description
- is_active
- timestamps
- deleted_at

### user_roles
- user_id
- role_id
- composite PK(user_id, role_id)

### role_permissions
- role_id
- permission_id
- composite PK(role_id, permission_id)

Use Laravel Policies/Gates for resource-level authorization.

## 4. Social
### follows
- id
- follower_id FK users
- following_id FK users
- created_at
- UNIQUE(follower_id, following_id)
- CHECK(follower_id <> following_id)
- index(following_id)

## 5. Notifications
### notifications
- id
- user_id FK users
- type
- title
- message
- data JSON nullable
- read_at nullable
- timestamps
- index(user_id, created_at)
- index(user_id, read_at)

## 6. Reports
### reports
- id
- reporter_id FK users
- reportable_type
- reportable_id
- reason
- description nullable
- status
- resolved_by nullable FK users
- resolved_at nullable
- timestamps
- index(reportable_type, reportable_id)
- index(status, created_at)

## 7. Categories
### categories
- id
- parent_id nullable self-FK
- name
- slug UNIQUE
- description
- image nullable
- is_active
- timestamps
- deleted_at
- index(parent_id)

## 8. Courses
### courses
- id
- title
- slug UNIQUE
- description
- thumbnail nullable
- instructor_name nullable
- difficulty_level_id nullable
- external_url nullable
- created_by nullable FK users
- is_active
- timestamps
- deleted_at

### difficulty_levels
- id
- name UNIQUE
- slug UNIQUE
- is_active
- timestamps
- deleted_at

### course_categories
- course_id
- category_id
- composite PK(course_id, category_id)

### study_group_courses
- study_group_id
- course_id
- composite PK(study_group_id, course_id)

### course_learning_materials
- course_id
- learning_material_id
- composite PK(course_id, learning_material_id)

## 9. Future Course Offers / Referrals
Database-prepared only.

### course_offers
- id
- course_id
- provider_name
- original_price
- discounted_price nullable
- currency
- discount_code nullable
- offer_url
- starts_at
- expires_at
- is_active
- timestamps
- deleted_at

### referrals
- id
- user_id
- course_offer_id
- code
- url
- status
- timestamps
- deleted_at

### referral_usages
- id
- referral_id
- user_id
- clicked_at
- converted_at nullable
- status
- created_at

## 10. Books
### books
- id
- title
- slug UNIQUE
- description
- cover_image nullable
- publication_year nullable
- created_by nullable FK users
- is_active
- timestamps
- deleted_at

### book_editions
- id
- book_id
- isbn_10 nullable UNIQUE
- isbn_13 nullable UNIQUE
- language_id
- publisher nullable
- publication_date nullable
- format nullable
- page_count nullable
- cover_image nullable
- timestamps
- deleted_at

### languages
- id
- name UNIQUE
- code UNIQUE
- timestamps
- deleted_at

### authors
- id
- name
- slug UNIQUE
- bio nullable
- photo nullable
- timestamps
- deleted_at

### book_authors
- book_id
- author_id
- role default author
- UNIQUE(book_id, author_id, role)

### book_categories
- book_id
- category_id
- composite PK(book_id, category_id)

### book_subjects
- book_id
- subject_id
- composite PK(book_id, subject_id)

### external_sources
- id
- name UNIQUE
- slug UNIQUE
- type
- base_url
- is_active
- timestamps
- deleted_at

### book_sources
- id
- book_id
- external_source_id
- external_id
- url
- metadata JSON nullable
- timestamps
- UNIQUE(book_id, external_source_id)

### edition_sources
- id
- book_edition_id
- external_source_id
- external_id
- url
- metadata JSON nullable
- timestamps
- UNIQUE(book_edition_id, external_source_id)

### book_reviews
- id
- book_id
- user_id
- rating 1..5
- title nullable
- body nullable
- status
- timestamps
- deleted_at
- UNIQUE(user_id, book_id)

### book_review_comments
- id
- review_id
- user_id
- parent_id nullable
- body
- status
- timestamps
- deleted_at

### book_external_ratings
- id
- book_id
- external_source_id
- rating
- rating_count
- metadata JSON nullable
- fetched_at
- timestamps
- deleted_at
- UNIQUE(book_id, external_source_id)

## 11. Learning Materials
### learning_materials
- id
- title
- slug UNIQUE
- description
- type
- content_url
- thumbnail nullable
- created_by nullable FK users
- is_active
- timestamps
- deleted_at

### book_learning_materials
- book_id
- learning_material_id
- composite PK(book_id, learning_material_id)

### subject_learning_materials
- subject_id
- learning_material_id
- composite PK(subject_id, learning_material_id)

### study_group_materials
- study_group_id
- learning_material_id
- composite PK(study_group_id, learning_material_id)

## 12. Study Groups
### study_groups
- id
- name
- slug UNIQUE
- description
- cover_image nullable
- created_by FK users
- category_id nullable FK categories
- academic_level_id nullable FK academic_levels
- status
- visibility
- max_members nullable
- rules nullable
- timestamps
- deleted_at
- indexes for created_by/category/academic_level/status/visibility

### study_group_subjects
- study_group_id
- subject_id
- composite PK(study_group_id, subject_id)

### study_group_members
- id
- study_group_id
- user_id
- role
- status
- joined_at
- timestamps
- UNIQUE(study_group_id, user_id)
- index(user_id)

### study_group_join_requests
- id
- study_group_id
- user_id
- status
- message nullable
- reviewed_by nullable
- reviewed_at nullable
- timestamps
- indexes(group,status) and (user,status)

Only one pending request per user/group.

## 13. Study Sessions
### study_sessions
- id
- study_group_id
- created_by
- title
- description
- type
- starts_at
- ends_at
- timezone
- max_attendees nullable
- status
- timestamps
- deleted_at
- indexes for group/time, status/time, and type/status/time

Types: online, offline.

Statuses: draft, scheduled, cancelled, completed.

### study_session_online_details
- id
- session_id UNIQUE
- platform
- meeting_url
- meeting_id nullable
- access_code nullable
- timestamps

### study_session_locations
- id
- session_id UNIQUE
- name
- address
- city
- latitude nullable
- longitude nullable
- notes nullable
- timestamps

### study_session_attendees
- id
- session_id
- user_id
- status
- joined_at nullable
- checked_in_at nullable
- timestamps
- UNIQUE(session_id, user_id)

## 14. Discussions
### study_group_posts
- id
- study_group_id
- user_id
- title
- body
- status
- is_pinned
- is_locked
- timestamps
- deleted_at

### study_group_post_comments
- id
- post_id
- user_id
- parent_id nullable
- body
- status
- timestamps
- deleted_at

### study_group_post_reactions
- id
- post_id
- user_id
- type
- timestamps
- UNIQUE(post_id, user_id)

## 15. Gamification
### ranks
- id
- name UNIQUE
- slug UNIQUE
- description
- icon nullable
- is_active
- timestamps
- deleted_at

### rank_rules
- id
- rank_id
- rule_type
- operator
- value
- timestamps
- deleted_at

### point_transactions
- id
- user_id
- type
- points SIGNED INT
- reference_type
- reference_id
- description nullable
- created_at
- indexes(user_id,created_at) and (reference_type,reference_id)

Total points are derived with SUM(points).

## 16. Search
MySQL is the source of truth. Elasticsearch handles:
- Full-text search
- Fuzzy search
- Cross-entity discovery
- Filters/facets
- Autocomplete
- Search relevance

Search indexes are not business tables.

## 17. Indexing Principle
A composite index `(A,B)` supports queries beginning with A and A+B. If real queries require reverse lookup by B, add an index beginning with B.

Do not add indexes mechanically; every index has storage and write-performance cost.

## 18. Status
Database Architecture v1.0 is ready to translate into Laravel migrations, with normal review during implementation.
