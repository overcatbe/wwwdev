# wwwdev

**Work on your PHP sites locally, keep them in step with the live server, safely.**

I'm in the business of posting my older ready made stuff here in the hope somebody finds them useful.
wwwdev runs local copies of your sites, only while you need them: PHP's built-in web server, one
port per site, and a private MariaDB that only listens on a socket. No root, no Docker, nothing
system-wide, nothing at boot. Everything lives in one folder you own.

And it does the part most local setups leave to you: the way back and forth to the live server.

- **Code goes up** (`push`): it shows what would change and asks first. Config, uploads and logs
  are never uploaded.
- **Content comes down** (`sync`, `pull`): the database and uploads come from the server, where the
  real content is written.
- **Nothing is lost:** `sync` lists code that's newer here and asks what to do with it (push it,
  keep it, discard it). Whatever a sync replaces is kept: old database dumps in `_db/history/`,
  old files in `_history/<site>/<time>/`. Nothing there is ever removed by itself.
- **Local-only files** (a setup script, notes) listed in `<site>/.gitignore` never leave your
  machine.

## Requirements

zsh, PHP (with `pdo_mysql`), MariaDB (server and client), rsync and ssh. lftp for sites on FTP.

## Install

Put `wwwdev` and `router.php` in the same folder in your `PATH`, for example `~/bin`:

```
cp wwwdev router.php ~/bin/
chmod +x ~/bin/wwwdev
```

## Quick start

```
wwwdev init
```

This makes `~/www` with a site list (`sites.conf`) to fill in and sets up the private database.
Then add one line per site to `~/www/sites.conf`:

```
# name           port  database  source                       docroot
example.com      8001  example   myserver:/var/www/example.com  public
```

- **name**: the site's folder under `~/www` (use the domain)
- **port**: it runs on `http://localhost:<port>`
- **database**: the local database's name, or `-` if the site has none
- **source**: where it lives: `host:/path` over SSH (host as in `~/.ssh/config`),
  `ftp://host/path` over FTP (login from `~/.netrc`), or `-` (files copied by hand)
- **docroot**: the web root inside the site's folder (`public`, or `.` for the folder itself)

Then:

```
wwwdev pull example.com      # files and database from the server, the first time
wwwdev start example.com     # → http://localhost:8001
```

## Commands

| | |
|---|---|
| `wwwdev init` | first time: the folder, a site list, the private database |
| `wwwdev start [site…]` | start the given sites, or all; the database starts only if needed |
| `wwwdev stop [site…]` | stop the given sites, or everything |
| `wwwdev restart [site…]` | stop, then start |
| `wwwdev status` | what is running |
| `wwwdev list` | the configured sites |
| `wwwdev logs <site>` | follow a site's server and PHP error log |
| `wwwdev sync <site>` | content from the server; code that's only here is listed first, you choose |
| `wwwdev pull <site>` | files, uploads and database from the server (overwrites the local copy) |
| `wwwdev loaddb <site>` | load `_db/<site>.sql.gz` into the local database |
| `wwwdev push <site>` | code to the server: previews the changes and asks first |

## The database

wwwdev reads a site's database login from `config/database.php` inside its web root (docroot), which defines `DB_HOST`,
`DB_NAME`, `DB_USER` and `DB_PASS` with `define()`. Locally it creates the same database and user,
so that file stays identical on both sides. On the server, the same file hands its login to
`mariadb-dump` for `pull` and `sync`; nothing of it is shown or stored locally.

A site with another layout: create its local database user by hand, or put a dump at
`_db/<site>.sql.gz` and run `wwwdev loaddb <site>`.

## Settings

All optional, as environment variables:

| | |
|---|---|
| `WWWDEV_HOME` | where everything lives (default `~/www`) |
| `WWWDEV_MARIADBD` | the MariaDB server program (default: found by itself) |
| `WWWDEV_REMOTE_PHP` | the `php` command on your server for database dumps (default `php`) |

## Good to know

- PHP's built-in server doesn't read `.htaccess`. `router.php` stands in for the usual front
  controller rewrite: existing files are served as they are, everything else goes to `index.php`.
- It answers one request after another: right for one person working on a site, not for load tests.
- The database's socket lives under `WWWDEV_HOME`; its full path must stay under 108 characters.

## License

GPL-3.0-or-later. See [LICENSE](LICENSE).
