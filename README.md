# Saf ERP

AmarSaf’s ERP for **sales, inventory, manufacturing, delivery, and accounting**. Built for agent-based distribution and bottling in Bangladesh (VAT and withholding included).

**Repository:** `amarsaf_erp`  
**Live:** [https://erp.amarsaf.com](https://erp.amarsaf.com)

English and Bengali UI. Laravel 10, MySQL, Vite, Tailwind.

---

## What it covers

| Area | Modules |
|---|---|
| Master data | Products, materials, packaging, agents, suppliers, warehouses |
| Buy | Purchase orders, goods receipts, supplier bills |
| Make | BOMs, production runs, QC |
| Stock | Inventory, batches, transfers, adjustments |
| Sell | Sales orders, delivery, POD, invoicing |
| Money | Journals, receipts, chart of accounts, VAT |
| People | Employees, leave, payroll |
| Insight | P&L, aging, cash flow, inventory valuation, agent performance |
| Apps | REST API (Sanctum) for agent and field-staff apps |

How modules connect: [docs/00-how-the-system-works.md](docs/00-how-the-system-works.md)

Staff manuals: [docs/client-user-guide.md](docs/client-user-guide.md)

---

## Requirements

- PHP 8.1+
- Composer 2
- MySQL 8
- Node.js 18+ and npm

On Windows, Laragon is the usual local stack.

---

## Local setup

```bash
git clone https://github.com/bitraveneth/amarsaf_erp.git
cd amarsaf_erp
cp .env.example .env
```

Edit `.env`:

```env
APP_NAME="Saf ERP"
APP_URL=http://saf_erp.test
DB_DATABASE=saferp
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```bash
composer install
php artisan key:generate
npm ci
npm run build
php artisan migrate --seed
php artisan storage:link
```

Full Windows notes: [docs/local-setup-guide.md](docs/local-setup-guide.md)  
Server / env notes: [docs/configuration-guide.md](docs/configuration-guide.md)

### Demo data vs lighter seed

| Goal | `.env` | Command |
|---|---|---|
| Full sample company (orders, production, payroll over time) | `SEED_SALES_DEMO=true` | `php artisan migrate --seed` |
| Masters + modules, no 1-year sales timeline | `SEED_SALES_DEMO=false` | `php artisan migrate --seed` |
| Schema only (no seed data) | — | `php artisan migrate` |

A true empty company still needs roles and a first user after `migrate`. Use seed, then delete demo records, or we can add a dedicated empty-install seeder later.

Default super-admin after seed (change this in production):

- Email: `super@saferpv.local`
- Password: `password`

---

## Planned hosts

| Host | Purpose |
|---|---|
| `erp.amarsaf.com` | Current live company data |
| `demo.amarsaf.com` | Separate empty (or demo) install — own folder and own database |

Do not point both hosts at the same Laravel folder or the same MySQL database.

---

## Frontend

```bash
npm run dev      # watch
npm run build    # production assets
```

Design rules: [docs/erp-design-guide.md](docs/erp-design-guide.md)

---

## Tests

```bash
php artisan test
```

---

## API

Base path: `/api`  
Auth: `POST /api/login` then `Authorization: Bearer {token}`

Agent and employee app routes live in `routes/api.php`.

---

## Documentation index

| Doc | Audience |
|---|---|
| [docs/00-how-the-system-works.md](docs/00-how-the-system-works.md) | Everyone — start here |
| [docs/client-user-guide.md](docs/client-user-guide.md) | Staff training |
| [docs/local-setup-guide.md](docs/local-setup-guide.md) | Developers |
| [docs/configuration-guide.md](docs/configuration-guide.md) | Server setup |
| [docs/live-demo-script.md](docs/live-demo-script.md) | Client demo day |

---

## Licence

AmarSaf software. All rights reserved.
