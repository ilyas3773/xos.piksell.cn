# xos.piksell.cn Card Backend

ThinkPHP 8 based card distribution backend with:

- storefront API
- user registration and JWT login
- energy / sign-in / invite system
- product, card secret, and order management
- announcement / FAQ / site config admin modules
- `uni-app x` client under `is/`

## Stack

- PHP 8+
- ThinkPHP 8
- Think ORM
- Firebase PHP-JWT
- static HTML/JS admin pages in `public/`
- `uni-app x` client

## Quick Install

The project now supports a first-run installer:

1. Deploy the site normally and point the web root to `public/`
2. Open `https://your-domain/elyas`
3. Fill in:
   - database host / port / name / username / password
   - admin username / nickname / password
   - basic JWT and site info
4. Submit the form

The installer will:

- create the database if permissions allow
- import the required SQL schema automatically
- create the admin account
- write the project `.env`
- create an install lock file

After installation, `/elyas` will no longer run the installer again.

## Manual Install Fallback

If you prefer manual setup:

1. Copy `.example.env` to `.env`
2. Update the database and JWT settings
3. Import:
   - `database/schema.sql`
   - `database/admin_module_init.sql`
   - `database/user_member_extend.sql`
4. Point the web root to `public/`

For older imported databases, you may also need:

- `database/update_admin_password.sql`
- `database/card_order_user_extend.sql`

## Important Routes

- `GET|POST /elyas`
- `POST /admin/auth/login`
- `GET /admin/auth/profile`
- `GET /admin/dashboard/stats`
- `GET /api/shop/home`
- `GET /api/shop/products`
- `POST /api/shop/orders`
- `POST /api/user/login`
- `POST /api/user/register`

## Notes

- the active project domain is `xos.piksell.cn`
- some historical `xos.piksell.cn` traces may still exist in old assets or folders
- use the current `.env` and current domain as the source of truth
