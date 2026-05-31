# Sonic Pro — Local WordPress Development

Docker-based local development environment for the Sonic Pro WordPress site.

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (running)
- [VS Code](https://code.visualstudio.com/) (recommended)
- [DBeaver](https://dbeaver.io/) (optional, for database management)
- Git

## Quick Start

1. **Clone / open** the repository.

2. **Create `.env`** from the example:
   ```bash
   cp .env.example .env
   ```
   Edit values as needed (defaults work out of the box).

3. **Start the environment:**
   ```bash
   docker compose up -d
   ```

4. **Open WordPress** in your browser:
   - http://localhost:8080

5. **Complete the WordPress installation wizard** (site title, admin user, etc.).  
   The database is already pre-configured via Docker environment variables.
   If the database was imported from a backup, you may skip this step.

## Docker Commands

| Command | Description |
|---|---|
| `docker compose up -d` | Start all containers (detached) |
| `docker compose down` | Stop all containers |
| `docker compose down -v` | Stop and remove volumes (wipes DB) |
| `docker compose restart` | Restart all containers |
| `docker compose logs -f wordpress` | Tail WordPress logs |
| `docker compose logs -f db` | Tail MySQL/DB logs |
| `docker compose exec wordpress bash` | Open a shell in the WordPress container |
| `docker compose exec db mysql -u root -p` | Open MySQL CLI |
| `docker compose config` | Validate configuration |

## DBeaver Connection Settings

Use these settings to connect DBeaver to the local MySQL instance:

| Setting | Value |
|---|---|
| **Host** | `127.0.0.1` |
| **Port** | `3306` |
| **Database** | `sonicpro` |
| **Username** | `sonicpro` |
| **Password** | `sonicpro_secret` |
| **Driver** | MySQL 8 |

For root access: username `root`, password `root_secret`.

## Database Import (Backup Restore)

If you need to restore a `.sql` backup:

1. Place the `.sql` file in `./database/` (it's auto-imported on first `docker compose up`).
2. Or import manually via DBeaver: right-click the database → **Tools** → **Execute Script**.

> **Note:** The `./database/` directory is mounted to `/docker-entrypoint-initdb.d` in the MySQL container. `.sql` files are executed automatically on first container initialization (only when the volume is empty).

## Project Structure

```
project-root/
├── wordpress/              # WordPress files (mounted into container)
│   ├── wp-admin/
│   ├── wp-content/
│   │   ├── plugins/
│   │   ├── themes/
│   │   └── uploads/
│   ├── wp-includes/
│   └── wp-config.php
├── database/               # SQL dumps (auto-imported on first run)
│   └── database.sql
├── docker-compose.yml      # Docker service definitions
├── .env                    # Local environment variables (gitignored)
├── .env.example            # Environment variable template
├── .gitignore              # Git ignore rules
└── README.md               # This file
```

## Git Workflow

- **`main`** — Production-ready code. Protected branch.
- **`develop`** — Active development branch (default).

### Workflow

```bash
# Start a new feature
git checkout develop
git pull origin develop
git checkout -b feature/your-feature-name

# ... make changes, commit ...

# Merge back
git checkout develop
git merge feature/your-feature-name

# When ready for production
git checkout main
git merge develop
```

## Troubleshooting

| Issue | Solution |
|---|---|
| Port 8080 already in use | Change `WORDPRESS_PORT` in `.env` |
| Port 3306 already in use | Change `MYSQL_PORT` in `.env` |
| Permission errors in `wordpress/` | The container runs as `www-data`. Run `chmod -R 755 wordpress/` |
| Database not importing | Ensure the volume is fresh (`docker compose down -v` then `docker compose up -d`) |
| White screen / 500 error | Check logs: `docker compose logs -f wordpress` |
