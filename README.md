# AlgoByte — Competitive Programming Platform

> A LeetCode-style competitive programming platform where users create, solve, and discuss coding challenges — powered by Vue.js, Laravel Breeze, and Supabase (PostgreSQL), with code execution handled through the Piston engine.

---

## Screenshots

### Login
<img width="1568" height="737" alt="image" src="https://github.com/user-attachments/assets/f4cbf099-3c62-473c-b54a-0165bdb92dbc" />

*Sleek dark-themed login form with username/email identity field, password visibility toggle, remember me option, and a link to register.*

---

### Register
<img width="1568" height="757" alt="image" src="https://github.com/user-attachments/assets/194853e4-f03c-44cf-b916-694ae079f226" />

*Account creation form collecting username, email, and password with confirmation. New members are welcomed into the AlgoByte community.*

---

### Forgot Password & Reset Flow
<img width="1568" height="707" alt="image" src="https://github.com/user-attachments/assets/97ef9cd3-b179-4c2f-b92c-5ef3b6201d22" />

<img width="636" height="761" alt="image" src="https://github.com/user-attachments/assets/d80cde8c-2130-4325-94fe-35321ac45840" />

*Split-screen layout featuring the platform tagline on the left and a password reset email form on the right.*

<img width="865" height="717" alt="image" src="https://github.com/user-attachments/assets/d7510180-2c02-4964-98c9-87500604dcd1" />

*Token-based identity verification step where users enter a 6-character code received by email, then set a new password.*

---

### Home Page
<img width="1568" height="675" alt="image" src="https://github.com/user-attachments/assets/9e24dedf-2c94-411f-8ae1-eee2b6d16e8a" />

*Hero section with the "Practice algorithms inside a sharper AlgoByte workspace" headline, a live code preview, and real-time system alert notifications in the top-right panel.*

---

### Account Settings
<img width="1568" height="784" alt="image" src="https://github.com/user-attachments/assets/6c5c9773-940c-4977-8133-91dfd679087e" />

*Profile management page where users update their avatar, profile banner, username, and email. Includes a password update section below.*

---

### Problem Catalog (Solve Problems)
<img width="1568" height="780" alt="image" src="https://github.com/user-attachments/assets/7f47fb2a-4d9e-4952-a665-87e3373514a7" />

*Shared problem set page with title search and difficulty filters (All / Easy / Medium / Hard). Problem cards show difficulty badge, description preview, and public/private toggle controls for problem authors.*

---

### Problem Creation Dashboard
<img width="1568" height="620" alt="image" src="https://github.com/user-attachments/assets/0403c41c-2bb9-45b3-aa59-569fa204f9a0" />

*Author workspace showing platform-wide stats (total users, admins, problems, submissions) and a personal problem list with visibility status, difficulty, default language, and Modify/Delete actions.*

---

### Problem Creation Form
<img width="1568" height="765" alt="image" src="https://github.com/user-attachments/assets/f90f612f-4049-409d-a487-c192ee3eeb41" />

*Two-panel problem authoring interface: the left side collects the title, difficulty, description, reference solution, and language; the right side shows a live "Problem Snapshot" preview updating in real time.*

---

### Problem Solving — Problem Details Tab
<img width="1568" height="782" alt="image" src="https://github.com/user-attachments/assets/d4988ffa-e4fc-4716-976a-8d70a9246f57" />

*Split-view problem page with the problem statement, prompt, and example test cases on the left, and a full TypeScript code editor on the right. Features font-size controls, language selector, Save Submission, Share Solution, and Validate buttons. Test cases are shown at the bottom of the editor panel.*

---

### Problem Solving — Shared Solutions Tab
<img width="1546" height="757" alt="image" src="https://github.com/user-attachments/assets/777c5258-579a-498a-95f2-d8af704f67fc" />

*Community solutions tab displaying author-posted solutions with code, explanation, and a like counter.*

---

### Problem Solving — Your Submissions Tab
<img width="1559" height="750" alt="image" src="https://github.com/user-attachments/assets/379dcebf-c57e-4950-b5ac-7d8b62c2f06e" />

*Personal submissions history for the current problem showing the language used, submission timestamp, and verdict (Accepted / Failed).*

---

### Problem Solving — Discussions Tab
<img width="1537" height="760" alt="image" src="https://github.com/user-attachments/assets/f5a02335-141a-4593-97b1-9d9ad9f233b2" />

*Per-problem discussion board where users post threads with a title and content. Discussions support upvotes and a Start Discussion button.*

---

### User Profile — History
<img width="1568" height="397" alt="image" src="https://github.com/user-attachments/assets/7d0da468-6fa2-4b15-8bfd-993675b3f687" />

*Profile history page with three tabs: Solved (problems the user has accepted), Authored (problems they created), and Attempts (all submissions). Each entry shows problem ID, difficulty, author, and solve date.*

---

### Admin Dashboard
<img width="1568" height="768" alt="image" src="https://github.com/user-attachments/assets/1f42ecd5-e89f-45b4-8ddd-e36adc518357" />

*System management panel showing platform-wide KPIs (total users, total problems, pending reports, total reports) and a Recent Reports table with reporter, reason, severity, status (Pending / Approved / Rejected), and submission date.*

---

### Admin — Review Reports
<img width="1568" height="354" alt="image" src="https://github.com/user-attachments/assets/b874c3ba-c34a-417a-97bc-0cd370087999" />

*Problem review queue where admins can approve or dismiss (keep) flagged problems. Each row shows the report ID, linked problem, reporter, reason, severity, and submission date.*

---

## Features

### Authentication & Accounts
- Secure register, login, and logout via Laravel Breeze
- Forgot password flow with email-based token verification and 6-character reset code
- Profile customization: avatar, banner image, username, and email
- Public profile view and personal history tracking

### Problem Solving
- Browse the public problem catalog with title search and Easy / Medium / Hard difficulty filters
- Split-view problem page: problem statement + live TypeScript code editor side by side
- Adjustable editor font size and language selector
- Validate solutions against saved test cases powered by the **Piston code execution engine**
- View all your past submissions per problem with their verdict

### Problem Creation
- Any user can author coding problems with a title, difficulty, description, reference solution, and explanation
- Live problem snapshot preview updates as you type
- Add custom test cases with input/output pairs (default and custom cases supported)
- Set problem visibility to **Public** or **Private**
- Manage your problems from the Problem Creation Dashboard (modify, delete, toggle visibility)

### Community
- **Shared Solutions** — Post and browse accepted solutions with code and explanations; upvote community solutions
- **Discussions** — Start and upvote per-problem discussion threads
- **Report System** — Flag problems with a reason and severity level directly from the catalog

### Notifications
- Real-time system alerts (report created, problem deleted, report approved/rejected) displayed as a dismissible notification panel

### Admin Panel
- Platform KPI dashboard: user count, problem count, pending/total reports
- Full report review queue: approve to remove flagged problems or dismiss to keep them
- Admin mode banner displayed when operating with elevated privileges

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | Vue.js + Bootstrap |
| Backend | Laravel (Breeze) |
| Database | PostgreSQL (via Supabase) |
| Auth | Laravel Breeze |
| Code Execution | Piston Engine inside a docker container |

---

## Database Schema

The platform is built on 12 core tables:

| Table | Description |
|---|---|
| `members` | Users with profile info, stats (streak, problems solved), editor preferences, and admin flag |
| `problems` | Coding challenges with title, description, difficulty, visibility, reference solution, and language |
| `test_cases` | Input/output pairs linked to problems, with a default flag |
| `submissions` | User code submissions with language, verdict, stdout/stderr, execution time, and runtime info |
| `shared_solutions` | Community-posted solutions linked to a problem and optionally to an accepted submission |
| `discussions` | Per-problem discussion threads with upvote counts |
| `comments` | Comments on shared solutions or discussions |
| `like_activities` | Upvote/like records across solutions, discussions, and comments |
| `reports` | Problem reports with reason, severity, status (pending/approved/rejected), and reviewer |
| `notifications` | In-app user notifications with type, message, and optional link |
| `password_reset_tokens` | Tokens for the email-based password reset flow |


---

## Authors

- **Ilyes Belkahia**
- **Eya Mehrez**
- **Mohammed Aziz Bennour**
- **Mohammed Aziz Sta**
- **Amir Saidane**
- **Salim Zalfani**

---

## 📄 License

This project is for educational purposes.
