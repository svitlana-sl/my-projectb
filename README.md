# 📚 My Project

> A modern Laravel 12 project with Jetstream (Livewire), Vite, DDEV, and SQLite.

---

## 🚀 Features

- Laravel 12 Framework
- Laravel Jetstream + Livewire stack
- Authentication (Login, Register, Password Reset)
- Two-Factor Authentication (2FA)
- Sanctum API Authentication
- Admin panel (coming soon 🚧)
- SQLite database for easy local development
- DDEV integration for local Docker-based environment
- Vite for frontend asset bundling

---

## 🛠️ Installation

Clone the repository:

```bash
git clone https://github.com/svitlana-sl/my-projectb.git
cd my-projectb
```

Install PHP dependencies:

```bash
composer install
```

Install NPM dependencies and build assets:

```bash
npm install
npm run build
```

Create `.env` file:

```bash
cp .env.example .env
```

Generate application key:

```bash
ddev artisan key:generate
```

Run migrations:

```bash
ddev artisan migrate
```

Start local development server (with DDEV):

```bash
ddev start
ddev launch
```

---

## 📂 Project Structure

```
├── app/              # Application code (Controllers, Models, etc.)
├── database/         # Migrations, seeders, factories
├── public/           # Public-facing assets (index.php)
├── resources/        # Blade templates, JS, CSS
├── routes/           # Web and API route definitions
├── storage/          # Compiled files, logs
├── tests/            # Unit and Feature tests
└── vite.config.js    # Vite configuration
```

---

## 📋 API Documentation

The API will be documented using Swagger (OpenAPI).

Endpoints:

- `GET /api/products` — List all products

Full documentation will be available at:

```bash
https://my-project.ddev.site:33001/api/documentation
```

(Coming soon 🚧)

---

## 🧑‍💻 Author

Developed with ❤️ by [Svitlana](https://github.com/svitlana-sl).

---

## 🏁 TODO

- [x] Initial project setup
- [x] Jetstream authentication
- [ ] Admin panel CRUD (Products)
- [ ] API for frontend (Next.js / React)
- [ ] Frontend project (external)
- [ ] Swagger API documentation
- [ ] Admin dashboard UI polishing

---

## 🔒 License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

---
