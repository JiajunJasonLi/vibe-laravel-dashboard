# AGENTS.md

## Project stack

- Backend: Laravel, PHP 8.3
- Frontend: Vue 3, Vite, Tailwind CSS v4
- Environment: WSL2 Ubuntu, Docker
- Version control: GitHub

## Working rules

- Use small commits.
- Prefer feature branches instead of committing directly to main.
- Keep Laravel controllers thin.
- Put business logic in service classes.
- Use Form Request classes for validation.
- Add or update tests when backend behavior changes.
- Run `php artisan test` before committing backend changes.
- Run `npm run build` before committing frontend changes.
- Do not commit `.env`, `node_modules`, `vendor`, or generated build artifacts unless intentionally required.
