# PTMSaaS — Client Review Delivery Report

**Date:** 1 September 2026  
**Subject:** Status of items raised in the Platform Review  
**Prepared for:** Client review follow-up

This report maps each item from the Platform Review to what has been delivered. The review asked for a real My Tasks view, project Calendar / Timeline / Workload, proactive inbox and email, global search, queued background work, tenant and employee tests, and self-service member onboarding.

---

## Summary

| Review item | Status |
|---|---|
| My Tasks — Today / Upcoming / Overdue + personal tasks | Delivered |
| Project Calendar (tasks by due date) | Delivered |
| Project Timeline / Gantt with dependencies | Delivered |
| Project Workload (who is overloaded) | Delivered |
| Inbox + email for assignments, comments, mentions | Delivered |
| Task followers and @mentions | Delivered |
| Real-time inbox (live toast + unread count) | Delivered |
| Global search (tasks, projects, people, comments) | Delivered |
| Member self-onboarding via invite link | Delivered |
| Queued notifications, CSV import/export, and attachments | Delivered |
| Tests: tenant isolation + unassigned employee cannot mutate | Delivered |

All items from the Platform Review are delivered.

---

## 1. My Tasks

**Asked for:** A real My Tasks view in the company admin portal with Today / Upcoming / Overdue grouping, plus personal tasks that do not belong to a project.

**Delivered**

- Sidebar **My Tasks** for company admins and employees
- Groups: **Overdue**, **Today**, **Upcoming**, **Completed**
- Personal tasks (no project) can be created and managed
- Click a row to open the task drawer (project and personal)
- Change due date inline — the task moves between Overdue / Today / Upcoming
- Quick-add under each group (Overdue / Today / Upcoming)
- Empty groups stay visible so the three buckets are always clear

---

## 2. Project views — Calendar, Timeline, Workload

**Asked for:** Calendar (tasks by due date), a real Timeline / Gantt with dependencies, and Workload. List and Board already existed. The old 6-month timeline was a display widget only.

**Delivered** — on each project page, next to List and Board:

| Tab | What it does |
|---|---|
| **Calendar** | Tasks placed on their due dates; month navigation |
| **Timeline** | Editable Gantt-style schedule. Drag a bar to move dates, drag ends to resize. “Blocked by” links draw dependency lines |
| **Workload** | Per-person load so overloaded assignees are visible |

The dashboard 6-month widget remains as a summary. The project Timeline tab is the editable schedule.

---

## 3. Inbox, mentions, followers, email, and live updates

**Asked for:** Notifications that reach users without a manual refresh. @mentions and task follower notifications. Laravel notifications with queues and real-time delivery (broadcasting / WebSockets). Inbox plus email for assignments, comments, and mentions.

**Delivered**

- In-app **Inbox** (bell + full inbox page) for assignments, comments, mentions, follower updates, attachments, and CSV job results
- **@mentions** in comments — type `@` to pick a teammate (including names with spaces). Mentions are highlighted in the composer and in posted comments
- **Task followers** — follow/unfollow; followers are notified on relevant activity
- **Email** is sent for the same events (invite mail and workspace notifications run on the queue)
- **Live inbox** via Pusher: toast and unread badge update while the page is open, without waiting for a refresh. A 30-second poll remains as backup

Platform operators configure SMTP and Pusher from Superadmin (same pattern as other platform settings). Secrets are stored encrypted.

---

## 4. Global search

**Asked for:** Find a task, project, person, or comment across the workspace.

**Delivered**

- Header search and **Ctrl+K**
- Company-scoped results: tasks, projects, people, comments, plus portfolios, goals, and teams
- Available on company admin and employee portals
- Personal tasks are labelled as Personal

---

## 5. Member self-onboarding (invite link)

**Asked for:** Stop setting passwords in the admin form. Email invitation or self-set password link for real SaaS onboarding.

**Delivered**

- Admin invites by name, email, and role — no password is typed by the admin
- Invite email is queued and sent with an accept link
- Invitee opens the link, sets their own password, and joins the workspace
- Members page shows pending invites with copy link, resend, and revoke

---

## 6. Background queue

**Asked for:** `QUEUE_CONNECTION=database` was set but unused. Notifications, CSV import, and attachment processing ran inside the request.

**Delivered**

| Work | Now |
|---|---|
| Workspace notification emails | Queued |
| Member invite emails | Queued |
| Project CSV import | Queued — inbox when finished |
| Project CSV export | Queued — inbox with download link |
| File attachments | Queued — inbox when the file is ready |

CSV import/export and attachments no longer block the browser. The user is told the job is queued and gets an inbox item when it completes.

---

## 7. Tests

**Asked for:** Tests only covered Breeze auth. Highest-value tests first: company A cannot read company B’s task; an employee cannot mutate a task they are not assigned to.

**Delivered**

- **Tenant isolation** — company A cannot open company B’s task on A’s slug, B’s slug, or via the employee portal
- **Employee authorization** — an unassigned employee cannot change status, inline-edit, or comment; an assigned employee can change status

Existing Breeze authentication tests are unchanged.

---

## How to verify

1. **My Tasks** — open My Tasks, add a personal task under Today, change the due date, confirm it moves groups.
2. **Project views** — open a project → Calendar / Timeline / Workload. On Timeline, drag a bar and add a “blocked by” dependency.
3. **Mentions & inbox** — comment with `@name` on a task. The mentioned user should get an inbox item, email, and a live toast if they are online.
4. **Search** — Ctrl+K and search a task title, person, or comment text.
5. **Invite** — Members → Send invite → open the link in a private window and complete signup.
6. **CSV** — export or import a project CSV; wait for the inbox item instead of an immediate file download.
7. **Attachment** — upload a file on a task; the page returns immediately and the file appears after the queue processes it (Inbox: “File ready”).

---

## Out of scope / next (optional)

These were not on the prioritized roadmap:

- My Tasks Calendar / Board views (Asana-style extras)
- Deeper full-text / typo-tolerant search

---

*End of report.*
