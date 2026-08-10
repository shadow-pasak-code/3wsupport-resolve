# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## What this is

"3W Support" — an after-sales / repair-ticket management system for Three W Business and Solutions, built on **CodeIgniter 3** (PHP, MVC, no build step, no package.json). There is no test suite, linter, or CI configured — verification is manual (browse the running app / check `application/logs`).

The system serves three roles from one codebase: **admin** (staff), **technician**, and **partner** (external repair vendor), plus a public-facing **LINE OA chatbot** that customers use to file and track repair tickets.

## Running it locally

This is a classic XAMPP/Apache + MySQL PHP app — no CLI dev server, no build/watch process.

- Serve the repo root through Apache (e.g. XAMPP `htdocs`), with `mod_rewrite` enabled (`.htaccess` routes all non-file/dir requests through `index.php`).
- Import a schema from `db/` (e.g. `db/rtaf_3wsupport (1).sql`) into MySQL as `rtaf_3wsupport`.
- DB credentials: `application/config/database.php` (default `root` / no password / `rtaf_3wsupport`, `mysqli`, `utf8mb4`).
- Site base URL: `application/config/config.php` (`$config['base_url']`) — update to match your local path.
- Third-party integrations (LINE Messaging API tokens, OpenRouter/Gemini API key) live in `application/config/app_config.php`. **This file currently contains live secrets committed in plaintext** (LINE channel token/secret, OpenRouter API key) — treat it as sensitive, don't paste its contents elsewhere, and prefer moving secrets out of version control if you're asked to touch this file.
- There is a duplicate `config.php`/`database.php` pair at the repo root — these are **not** the files CodeIgniter loads (CI only reads `application/config/*`); ignore the root copies unless specifically asked about them.
- Ignore the stray root-level `test_gemini.php`, `test_partner.php`, and `phpinfo.php` — they're ad hoc debug scripts, not part of the app.

## Architecture

### Role-based controller hierarchy

`application/core/MY_Controller.php` defines the whole auth/rendering model in one small file — read it before touching any controller:

- `MY_Controller` (extends `CI_Controller`): loads `app_config` + `session`, pulls the logged-in user from `session->userdata('user')` into `$this->current_user`, and provides `render($view, $data, $layout)` which injects `current_user` and renders through a layout view.
- `Admin_Controller`, `Tech_Controller`, `Partner_Controller` each extend `MY_Controller`, gate access by `$this->current_user['role']` (redirecting to `login` if unset/mismatched), and override `render()` to prefix the view path with `admin/`, `technician/`, or `partner/` respectively and always use the single shared layout `admin/layout/main`.
- **All views for all three roles live under `application/views/admin/...`** — e.g. a technician controller's `render('tickets/index')` resolves to `application/views/admin/technician/tickets/index.php`. There's only one layout file, shared by every role; the sidebar in `application/views/admin/layout/main.php` switches nav items based on `$current_user['role']`.
- Controllers that must NOT be gated (e.g. `admin/Auth.php` — login/logout, and `api/Line_webhook.php`) extend `CI_Controller` directly, not one of the role controllers.

Pick the right base class by directory convention: `controllers/admin/*` → `Admin_Controller`, `controllers/technician/*` → `Tech_Controller`, `controllers/partner/*` → `Partner_Controller`.

### Users, roles, and role-specific profile tables

Auth is username/password (`password_hash`/`password_verify`) against a single `users` table (`User_model::login`). `users.role` is one of `admin` / `technician` / `partner`, and `users.ref_id` points at the corresponding row in `technicians` or `partners` (there's no `ref_id` link needed for admin). Ticket joins frequently do `LEFT JOIN users u ON u.ref_id = t.technician_id AND u.role = 'technician'` — keep that pattern in mind when writing new queries that need a technician's/partner's display name.

### Tickets: the core domain object

`application/models/Ticket_model.php` defines the full ticket status lifecycle as class constants (`STATUS_PENDING` → `APPROVED` → `ASSIGNED` → `IN_PROGRESS` / `WAIT_QUOTE` → `WAIT_CONFIRM` → `QUOTE_ACCEPTED`/`QUOTE_REJECTED` → `ESCALATED` → `COMPLETED` → `CLOSED`). Always reference these constants (`Ticket_model::STATUS_*`) rather than hardcoding status strings in new code — existing controllers are inconsistent about this (some use the constant, some use a raw string like `'wait_confirm'`), don't add to that.

Key branching logic to know before changing ticket flows (see `application/controllers/admin/Tickets.php::assign()`):
- Whether a device is **in warranty** (`devices.warranty_end >= today`) determines whether an assigned technician/partner can just do the repair, or must first go through a quotation (`wait_confirm`) that the customer confirms/rejects over LINE.
- Every status transition should be paired with a row in `ticket_logs` (see the private `_log()` helper in `Tickets.php`) — this is the audit trail shown in ticket detail/history views.
- Quotations have their own table (`quotations`, one row per ticket, JSON-encoded `items`) separate from the summary fields (`quote_amount`, `quote_detail`, `quote_file`) denormalized onto `tickets` itself for LINE messages and list views.

### LINE OA chatbot (`application/controllers/api/Line_webhook.php`)

A single webhook endpoint (`webhook/line` → `api/line_webhook/handle`) implements a stateful text-based conversation flow entirely in PHP, no LINE LIFF/rich UI beyond Flex Messages:
- Verifies `X-Line-Signature` via HMAC-SHA256 against `line_channel_secret` (`_verify_signature`) before processing anything.
- Unlinked customers authenticate by texting a device serial number, then their registered phone number; short-lived state is tracked in dedicated tables (`line_verify`, `line_waiting_sn`, `line_repair_pending`) keyed by `line_uid`, checked/cleared at the top of `_process_message()` in a specific priority order — read that method's structure before adding a new stateful step, since state tables are checked sequentially and each can fall through to the next.
- Keyword routing (แจ้งซ่อม/ตรวจสอบประกัน/สถานะ/ติดต่อแอดมิน) falls back to an LLM chatbot (`_handle_chatbot`) that calls OpenRouter (`openrouter_api_key`/`openrouter_model` in `app_config.php`) with the active FAQ table injected as system context.
- Outbound messages go through `application/libraries/Line_notify.php` (`push()` for text, `push_flex()` for Flex Message cards, e.g. the quotation approve/reject card built in `Tickets::_build_quote_flex()`).
- All strings and error/log messages in this controller are Thai — keep new user-facing LINE copy in Thai and in the same tone.

### Routing

`application/config/routes.php` hand-declares nearly every URL (CI3 default segment routing is not relied on for the main app) — when adding a controller method that needs a URL, add the explicit `$route[...]` entry rather than assuming default `controller/method/id` routing will reach it. `login`/`logout` map to `admin/auth/*` and are shared across all roles. Numeric ID segments use `(:num)`.

### Front end

No JS build pipeline. Views use server-rendered PHP with **Tailwind CSS via CDN** (`<script src="https://cdn.tailwindcss.com">` in `application/views/admin/layout/main.php`, Thai `Sarabun` web font) for all screens added under the current admin/tech/partner UI. The `vendors/` and `assets/` directories hold an older jQuery/Bootstrap admin-theme (DataTables, Chart.js, bootstrap-datetimepicker, etc.) — check whether a given view actually includes those assets before assuming they're live; new UI work in this app is Tailwind-first.

### Known cruft (don't be confused by it)

- `application/controllers/files/` contains near-duplicate copies of some admin controllers (`Devices.php`, `Faq.php`, `Partners.php`, `Technicians.php`) plus a stray `Device_model.php` sitting in the controllers directory. Nothing in `routes.php` targets `files/*`, so these are legacy/orphaned — the canonical versions are under `application/controllers/admin/`. Don't edit the `files/` copies expecting them to affect the live app.
- `application/views/backend/`, `application/views/frontend/`, `application/views/template/`, and `application/views/line/` are legacy/unused view scaffolding from an earlier iteration — the live app only renders through `application/views/admin/...` plus a few top-level views (`login.php`, `quotation/*`).

## Conventions observed in the codebase

- Controllers query the DB directly via `$this->db->...` (query builder) for read paths/joins even where a model exists; models mostly own writes and lifecycle helpers (`create`, `update_status`, etc.). Follow this split rather than moving all queries into models or vice versa.
- All user-facing text (flash messages, LINE messages, UI labels) is Thai. Match this in new code.
- Timestamps are plain `date('Y-m-d H:i:s')` strings set by PHP, not DB defaults — set `created_at`/`updated_at` explicitly when inserting/updating.
- Timezone is force-set to `Asia/Bangkok` in the front controller (`index.php`).
