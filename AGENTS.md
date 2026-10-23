# Agent Instructions

## Working boundaries
- Never commit directly to `main` or push to `main`, including force pushes. Work on a separate `codex/<task-name>` branch and submit changes through a Pull Request to `main` for the user's review.
- Do not merge PRs, enable auto-merge, or bypass the user's review. The user performs the merge after reviewing the changes.
- Changes to `main` may automatically trigger a production redeployment. Do not use manual deployments or deploy hooks to bypass the PR process.
- These instructions apply to the entire backend repository.
- During exploration and documentation initialization, do not run the application, DDEV/Docker, tests, builds, dependency installation, Artisan commands, migrations, or seeders until the user requests that work.
- Reading files, inspecting Git diffs/status, and editing documentation within the requested scope are allowed.
- Do not change `.env`, secrets, databases, file storage, or deployment settings without a relevant task. Never disclose secrets in responses.

## Project overview
- Pet Care Platform: users, pet owners, sitters, pet profiles, services, service requests, ratings, and favorites.
- Stack originally recorded from `composer.json`: PHP ^8.2, Laravel ^12.0, Filament ^3.3, Livewire ^3.0, Jetstream ^5.3, Sanctum ^4.0. Always check the current manifest and lock file for the actual PHP requirement.
- The README mentions Laravel 11; use manifests and lock files to verify versions instead of relying only on the README.
- Integrations include S3-compatible DigitalOcean Spaces, Intervention Image processing, and Swagger through l5-swagger.
- The separate frontend lives in `../my-project-frontend` and has its own `AGENTS.md`.

## Code map
- `routes/api.php`: API routes and `auth:sanctum` protection; `routes/web.php`: web routes.
- `app/Http/Controllers/Api/`: API controllers. Controllers with the same names also exist directly in `app/Http/Controllers/`; check the route import before editing.
- `app/Models/`: Eloquent models and relationships; `database/migrations/`, `factories/`, and `seeders/`: schema and initial data.
- `app/Filament/Resources/`: admin resources; `app/Actions/`: Fortify/Jetstream actions.
- `app/Traits/HasFileUpload.php`: shared upload logic. `app/Console/Commands/` also contains commands that modify files or permissions.
- `resources/`: backend Blade, CSS, and JavaScript; this is separate from the Next.js frontend.
- `tests/Feature/`, `tests/Unit/`, and `phpunit.xml`: existing tests; `.ddev/`: local environment configuration.

## Tools and access (checked 2026-10-23)
- GitHub: `https://github.com/svitlana-sl/my-projectb`, default branch `main`. The connector confirmed read, write, and admin permissions; the access check itself did not perform a push.
- DigitalOcean: the account connector works. `doctl` 2.0.0 is available for App Platform; listing applications succeeded.
- Backend application: `orca-app`, ID `b58985eb-0d2b-4fdb-b6b4-b6f334a9c230`, URL `https://orca-app-63wd2.ondigitalocean.app`; status at the time of inspection was `ACTIVE`.
- The user supplied an API token in `../my-project-frontend/.env`, variable `digital_ocean_token`. Pass it to doctl only through `DIGITALOCEAN_ACCESS_TOKEN` in a subprocess environment. Never print it or include it in command arguments, documentation, or Git. Do not execute `.env` as a shell script.
- Persistent doctl OAuth authentication was not confirmed; the successful check used the file-based token. App Platform write permissions, SSH, database access, and Spaces file access were not verified.
- DigitalOcean Spaces requires a separate S3 key and bucket permission check; App Platform access does not prove file access.
- Vercel frontend: `best_paws`, project ID `prj_bmHzufHqbrrFvvAud7HpVLWYbmZL`, team ID `team_5wXx8AJmTC54V0YDum3Vmbnu`. Reading the project through Vercel CLI works; the chat connector returned 403 for this team, so use the CLI with an explicit scope.
- Configuring access is not authorization to deploy, migrate, or modify production. Recheck access before relevant work without creating test resources.
- During doctl installation, Homebrew automatically removed Node 24.1.0 and mongosh 2.5.2. Local runtime readiness was not verified as part of that installation.

## Rules for future changes
- Follow `.editorconfig` and surrounding code style; PHP uses four spaces.
- Preserve authentication, authorization, and validation. Do not trust ownership identifiers solely from client requests.
- When changing APIs, align HTTP methods, fields, response formats, and statuses with the frontend and Swagger definitions.
- For uploads, check existing multipart handling, `_method`, paths, thumbnails, and old-file cleanup.
- Do not edit `vendor/`, `node_modules/`, generated files, or historical migrations for new schema changes; create new migrations when the task requires them.
- Do not run storage cleanup or permission-repair commands as routine verification.
- Once execution is authorized, choose checks appropriate to the change: targeted PHPUnit tests, Pint for PHP, and Vite builds for assets. Until authorized, only describe the required checks.
- Communicate with the user in Ukrainian. Clearly distinguish file inspection from checks actually executed.
