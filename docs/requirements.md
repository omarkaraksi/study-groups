# Study Groups — Requirements v1.0

## 1. Overview
Study Groups is a social and educational hub for high-school, university, and postgraduate students. It helps users discover educational resources, connect with learners, create/join study groups, participate in discussions, and organize online/offline study sessions.

The platform may link to external educational/book sources instead of hosting every resource itself.

## 2. Users and Roles
- Guest
- Registered User
- Group Owner
- Administrator

Authorization is dynamic and permission-based. Users may have multiple roles.

## 3. Authentication
- Registration
- Login/logout
- Password reset/update
- Email verification

## 4. Profiles
Users may have:
- Username
- Name
- Bio
- Avatar
- Academic level
- Institution
- Field of study
- City/area
- Interests
- Subjects

Users can follow other users.

## 5. Educational Content
Supported entities:
- Courses
- Books
- Learning Materials
- Categories
- Subjects

External sources may include Goodreads, Google Books, Open Library, publishers, research databases, and other educational platforms.

### Courses
Courses can:
- Exist independently of groups
- Belong to multiple categories
- Have learning materials
- Link to multiple study groups
- Have difficulty level
- Reference an external URL

Course offers/referrals are database-prepared but postponed from MVP business logic.

### Books
Books support:
- Authors
- Editions
- Languages
- Categories
- Subjects
- External sources
- User reviews/comments
- External ratings
- Learning materials

### Learning Materials
Types may include:
- Article
- Video
- PDF
- Document
- Website
- Tutorial
- Exercise

## 6. Study Groups
Users can:
- Create/update groups
- Join public groups
- Request private-group membership
- Leave groups
- Participate in discussions
- Moderate according to permissions

Group fields:
- Name
- Slug
- Description
- Cover
- Owner
- Category
- Academic level
- Subjects
- Visibility
- Maximum members
- Rules
- Status

Visibility:
- Public
- Private

Status:
- Draft
- Preview
- Active
- Suspended
- Archived

## 7. Membership
Membership roles:
- Owner
- Moderator
- Member

Statuses:
- Active
- Banned
- Left

One membership relationship exists per user/group.

## 8. Join Requests
Private groups use join requests:
- Pending
- Approved
- Rejected
- Cancelled

Only one pending request per user/group is allowed; history is retained.

## 9. Discussions
Groups support:
- Posts
- Comments
- Nested comments
- Reactions
- Pinning
- Locking
- Moderation/hiding

## 10. Study Sessions
Session types:
- Online
- Offline

Statuses:
- Draft
- Scheduled
- Cancelled
- Completed

Sessions support:
- Start/end time
- Timezone
- Maximum attendees
- Attendees
- Attendance status

Online sessions contain meeting details; offline sessions contain location details.

Study sessions are searchable.

## 11. Search
Searchable entities:
- Users
- Books
- Courses
- Learning Materials
- Study Groups
- Study Sessions
- Categories
- Subjects

Search filters may include:
- Query
- Type
- Category
- Subject
- Academic level
- Difficulty
- Location
- Online/offline
- Status
- Date/time
- Group

MySQL is the source of truth. Elasticsearch is the search/index layer.

## 12. Following
Following is one-directional. Users cannot follow themselves. Relationships can be removed without deleting users.

## 13. Notifications
Notifications may be triggered by:
- Follow
- Join request
- Approval
- Comments/reactions
- Study sessions
- Reminders
- Review comments
- Rank changes

## 14. Reporting and Moderation
Users can report:
- Users
- Posts
- Comments
- Groups
- Reviews
- Educational resources

Statuses:
- Pending
- Reviewing
- Resolved
- Rejected

## 15. Gamification
Database prepared for:
- Ranks
- Rank rules
- Point transactions

Full gamification is not MVP.

## 16. Admin
Admin manages:
- Users
- Roles/permissions
- Groups
- Sessions
- Courses
- Books
- Materials
- Categories/subjects
- Reports/moderation
- Notifications
- Required system settings

Admin UI: Laravel Blade + Tabler.

## 17. API
REST API version:
`/api/v1`

Use Laravel API Resources and pagination.

## 18. Security
- Server-side authorization
- Policies/Gates
- Input validation
- Authentication/email verification
- Secure password hashing
- Resource-level access control
- Transactions for critical workflows
- Never trust client authorization data

## 19. MVP Out of Scope
- Private messaging
- Advanced real-time chat
- Video conferencing
- Mobile apps
- AI recommendations/tutors
- Full gamification
- Payments
- Paid courses
- Course authoring
- Advanced analytics
- Marketplace
- Advertising

## 20. Development Principle
The project is both a learning project and a portfolio project.

> Simple by default; complexity only when justified by a real requirement.

Patterns and abstractions must solve an identifiable problem.
