# Qahwa Craft – IS 332 Group 5i6

Online store for Saudi specialty coffee. Built with HTML, CSS, JavaScript, PHP and MySQL.

## How to put it online (Render + TiDB Cloud, free)

The website runs on Render (using the `Dockerfile`) and the data is stored in TiDB Cloud Starter,
a free MySQL-compatible database.

1. **Database:** sign up at tidbcloud.com → create a **Starter** instance → click **Connect** →
   choose **General**, then click **Generate Password**. Copy HOST, PORT, USERNAME and PASSWORD.
2. **GitHub:** create a new repository and upload all project files, including `Dockerfile` and the `includes` and `assets` folders.
3. **Render:** New → **Web Service** → connect the GitHub repository → Language **Docker** → Instance type **Free**.
4. Add these **Environment Variables** in Render:

| Key | Value |
| --- | --- |
| `DB_HOST` | HOST from TiDB Cloud |
| `DB_PORT` | `4000` |
| `DB_USER` | USERNAME from TiDB Cloud (looks like `xxxx.root`) |
| `DB_PASS` | PASSWORD from TiDB Cloud |
| `DB_NAME` | `qahwacraft_db` |

5. Click **Deploy Web Service**. When it says *Live*, open the `.onrender.com` link.
   The database, tables, products and accounts are created on the first visit.

A free Render service sleeps after 15 minutes without visitors, so the first page after a pause can take about a minute.

## Accounts

| Account | Email | Password |
| --- | --- | --- |
| Lecturer (Sammer Aldbass) | sammer.aldbass@qahwacraft.sa | Sammer@2026 |
| Demo customer | demo@qahwacraft.sa | Demo@1234 |
| Admin | admin@qahwacraft.sa | Admin@2026 |

Coupons: `SAMMER20` (20%), `WELCOME15` (15%), `COFFEEDAY` (10%). Payments: mada (verification code `123456`) or Apple Pay (press Confirm).

## Files

| File | What it is |
| --- | --- |
| `content.html` | **All the page text** (home, about, FAQ, contact, policies, footer). Edit the words here. |
| `index.php` | Home page |
| `shop.php`, `product.php` | Product list with filters, and product details |
| `subscribe.php` | Subscription plans |
| `cart.php`, `checkout.php` | Cart, delivery details and payment |
| `order.php`, `track.php` | Order confirmation and order tracking |
| `login.php`, `register.php`, `logout.php`, `account.php` | Customer accounts |
| `contact.php` | Contact form (saved in the database) |
| `page.php` | Shows the text pages from content.html (about, FAQ, policies) |
| `admin.php` | Admin panel: orders, products, messages, customers |
| `includes/config.php` | Settings: database, coupons, couriers, accounts |
| `Dockerfile` | Tells Render how to run the site (PHP 8.3 + Apache) |
| `includes/db.php` | Database connection and tables |
| `includes/functions.php` | Shared functions (cart, security, product cards) |
| `includes/header.php`, `includes/footer.php` | Top and bottom of every page |
| `assets/style.css`, `assets/script.js`, `assets/logo.svg` | Design, small scripts, logo |
