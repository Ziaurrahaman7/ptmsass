# PTMSASS — Role-Based Access Control (RBAC) Documentation

**Project:** ptmsass — Project & Task Management SaaS  
**Stack:** Laravel 11, PHP, MySQL, Tailwind CSS  
**Last Updated:** 2026

---

## 📌 Table of Contents

1. [Roles Overview](#roles-overview)
2. [Superadmin](#superadmin)
3. [Company Admin](#company-admin)
4. [Employee](#employee)
5. [Client](#client)
6. [Security Rules](#security-rules)
7. [Database Schema](#database-schema)
8. [Missing / Planned Features](#missing--planned-features)

---

## Roles Overview

| Role            | Value in DB       | Description                                              |
|-----------------|-------------------|----------------------------------------------------------|
| Superadmin      | `superadmin`      | Platform owner — সব company manage করে                  |
| Company Admin   | `company_admin`   | Company owner — নিজের company সব কিছু manage করে        |
| Employee        | `employee`        | Team member — assigned tasks দেখে ও কাজ করে             |
| Client          | `client`          | External client — শুধু assigned projects দেখতে পারে     |

---

## Superadmin

**URL Prefix:** `/superadmin/`  
**Middleware:** `auth`, `superadmin`  
**Login Redirect:** `/superadmin/dashboard`

### Access

| Feature                        | Access |
|-------------------------------|--------|
| Platform Dashboard             | ✅ Full |
| সব Company list দেখা           | ✅ Full |
| Company Create                 | ✅ |
| Company Edit                   | ✅ |
| Company Delete                 | ✅ |
| Company Active/Suspend toggle  | ✅ |
| নিজের Profile Edit             | ✅ |
| অন্য company র data access     | ❌ নেই |
| Projects/Tasks manage          | ❌ নেই |

### Routes

```
GET    /superadmin/dashboard
GET    /superadmin/companies
GET    /superadmin/companies/create
POST   /superadmin/companies
GET    /superadmin/companies/{id}/edit
PUT    /superadmin/companies/{id}
DELETE /superadmin/companies/{id}
PATCH  /superadmin/companies/{id}/toggle
```

---

## Company Admin

**URL Prefix:** `/{slug}/admin/`  
**Middleware:** `auth`, `company_admin`, `company_slug`  
**Login Redirect:** `/{slug}/admin/dashboard`

### Access

| Feature                          | Access |
|----------------------------------|--------|
| Dashboard (full analytics)       | ✅ Full |
| Projects — Create/Edit/Delete    | ✅ Full |
| Tasks — Create/Edit/Delete       | ✅ Full |
| Task Assign (single & multiple)  | ✅ Full |
| Task Status Update               | ✅ Full |
| Task Comments — Add/Delete       | ✅ Full |
| Task Attachments — Upload/Delete | ✅ Full |
| Subtasks — Create/Delete         | ✅ Full |
| Kanban Board (drag & drop)       | ✅ Full |
| Members — Add/Deactivate         | ✅ Full |
| Teams — Create/Edit/Delete       | ✅ Full |
| Notifications — View/Mark Read   | ✅ Full |
| Custom Fields                    | ✅ Full |
| Sections                         | ✅ Full |
| Goals                            | ✅ Full |
| Insights / Reports               | ✅ Full |
| Portfolio                        | ✅ Full |
| অন্য company র data              | ❌ নেই |

### Routes

```
GET    /{slug}/admin/dashboard
GET    /{slug}/admin/tasks
POST   /{slug}/admin/tasks
GET    /{slug}/admin/tasks/{task}
PUT    /{slug}/admin/tasks/{task}
DELETE /{slug}/admin/tasks/{task}
PATCH  /{slug}/admin/tasks/{task}/status
POST   /{slug}/admin/tasks/{task}/comments
DELETE /{slug}/admin/tasks/comments/{comment}
POST   /{slug}/admin/tasks/{task}/attachments
DELETE /{slug}/admin/tasks/attachments/{attachment}
GET    /{slug}/admin/projects
POST   /{slug}/admin/projects
GET    /{slug}/admin/projects/{project}
PUT    /{slug}/admin/projects/{project}
DELETE /{slug}/admin/projects/{project}
POST   /{slug}/admin/projects/{project}/tasks
GET    /{slug}/admin/members
POST   /{slug}/admin/members
PATCH  /{slug}/admin/members/{user}/toggle
POST   /{slug}/admin/teams
GET    /{slug}/admin/teams/{team}
PUT    /{slug}/admin/teams/{team}
DELETE /{slug}/admin/teams/{team}
GET    /{slug}/admin/notifications
GET    /{slug}/admin/notifications/unread
PATCH  /{slug}/admin/notifications/{id}/read
POST   /{slug}/admin/notifications/mark-all-read
```

### Extra Security Checks

- Company `suspended` বা `inactive` হলে → auto logout
- নিজের `is_active = false` হলে → auto logout
- সব task/project এ `company_id` check করা হয় → অন্য company র data access করলে `403`

---

## Employee

**URL Prefix:** `/{slug}/`  
**Middleware:** `auth`, `employee`, `company_slug`  
**Login Redirect:** `/{slug}/dashboard`

### Access

| Feature                              | Access |
|--------------------------------------|--------|
| Dashboard (নিজের tasks summary)      | ✅ |
| Projects — View only (assigned)      | ✅ |
| Tasks — View (assigned to me)        | ✅ |
| Task Status Update (নিজের tasks)     | ✅ |
| Task Comments — Add/Delete own       | ✅ |
| Task Attachments — Upload/Delete own | ✅ |
| Subtasks — View only                 | ✅ |
| Notifications — View/Mark Read       | ✅ |
| Projects — Create/Edit/Delete        | ❌ নেই |
| Tasks — Create/Edit/Delete           | ❌ নেই |
| Members Management                   | ❌ নেই |
| Teams Management                     | ❌ নেই |
| Full Dashboard Analytics             | ❌ নেই |

### Routes

```
GET    /{slug}/dashboard
GET    /{slug}/projects/{project}
GET    /{slug}/tasks
GET    /{slug}/tasks/{task}
PATCH  /{slug}/tasks/{task}/status
POST   /{slug}/tasks/{task}/comments
DELETE /{slug}/tasks/comments/{comment}
POST   /{slug}/tasks/{task}/attachments
DELETE /{slug}/tasks/attachments/{attachment}
GET    /{slug}/notifications
GET    /{slug}/notifications/unread
PATCH  /{slug}/notifications/{id}/read
POST   /{slug}/notifications/mark-all-read
```

### Task Visibility Logic

Employee একটি task দেখতে পারবে যদি:
- `tasks.assigned_to = auth()->id()` **অথবা**
- `task_assignees` pivot table এ `user_id = auth()->id()` থাকে



### Extra Security Checks

- `is_active = false` হলে → auto logout
- Company `suspended` হলে → auto logout
- অন্য company র task access করলে → `403`
- নিজের comment/attachment ছাড়া delete করতে পারবে না

---

## Client

**URL Prefix:** `/{slug}/client/`  
**Middleware:** `auth`, `client`, `company_slug`  
**Login Redirect:** `/{slug}/client/dashboard`

### Access

| Feature                          | Access |
|----------------------------------|--------|
| Dashboard (assigned projects)    | ✅ |
| Projects — View only (assigned)  | ✅ |
| Tasks — View only                | ✅ |
| Comments/Attachments             | ❌ নেই (planned) |
| Members দেখা                     | ❌ নেই |
| Tasks Create/Edit/Delete         | ❌ নেই |
| Notifications                    | ❌ নেই (planned) |

### Routes

```
GET    /{slug}/client/dashboard
GET    /{slug}/client/projects/{project}
```

### Project Assignment

Client কে project assign করা হয় `project_clients` pivot table এর মাধ্যমে:



### Extra Security Checks

- `is_active = false` হলে → auto logout
- Company `suspended` হলে → auto logout
- শুধু `project_clients` এ assigned project দেখতে পারবে

---

## Security Rules

সব role এ প্রযোজ্য নিরাপত্তা নিয়ম:

| Rule | Implementation |
|------|---------------|
| Company isolation | প্রতিটি query তে `company_id = auth()->user()->company_id` check |
| Account deactivation | `is_active = false` হলে middleware এ logout |
| Company suspension | `company.status = suspended` হলে সব user logout |
| Task ownership | `abort_if($task->company_id !== auth()->user()->company_id, 403)` |
| Comment ownership | `abort_if($comment->user_id !== auth()->id(), 403)` |
| Attachment ownership | `abort_if($attachment->uploaded_by !== auth()->id(), 403)` |
| CSRF Protection | সব POST/PUT/PATCH/DELETE request এ `@csrf` |
| Route Model Binding | Laravel automatic model binding ব্যবহার |

---

## Database Schema

### users table (role column)



### Key Pivot Tables

```sql
-- Multi-assign tasks
task_assignees
  - task_id (FK → tasks.id)
  - user_id (FK → users.id)
  - UNIQUE(task_id, user_id)

-- Client project access
project_clients
  - project_id (FK → projects.id)
  - user_id    (FK → users.id)

-- Team members
team_user
  - team_id (FK → teams.id)
  - user_id (FK → users.id)
  - UNIQUE(team_id, user_id)
```

### Subtask Support

```sql
tasks.parent_task_id FK → tasks.id (nullable)
-- NULL = parent task
-- NOT NULL = subtask
```

---

## Missing / Planned Features

| Feature | Role | Status |
|---------|------|--------|
| Client routes fully registered | Client | ⚠️ Partially done |
| Client notifications | Client | ❌ Not started |
| Client comment on tasks | Client | ❌ Not started |
| Employee task creation | Employee | ❌ By design (no access) |
| Superadmin impersonate company | Superadmin | ❌ Not started |
| Role-based dashboard widgets | All | 🔄 In progress |
| Deadline reminder notifications | All | ❌ Not started |
| Reports section | Company Admin | ❌ Not started |

---

## Quick Reference

```
Superadmin   → /superadmin/dashboard
Company Admin → /{slug}/admin/dashboard
Employee     → /{slug}/dashboard
Client       → /{slug}/client/dashboard
```

```
Role Check Methods (User Model):
  $user->isSuperAdmin()   → role === 'superadmin'
  $user->isCompanyAdmin() → role === 'company_admin'
  $user->isEmployee()     → role === 'employee'
  $user->isClient()       → role === 'client'
```
