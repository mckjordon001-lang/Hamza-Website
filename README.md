# Hamza Book Store: WordPress + WooCommerce bookshop for Pakistan

A complete WordPress e-commerce site for selling books online to customers in Pakistan.

- **Kitab theme** (`wp-content/themes/kitab`) is a custom WooCommerce theme with a warm, book-focused design. It works on mobile and supports Urdu Nastaliq text.
- **Kitab Book Details plugin** (`wp-content/plugins/kitab-book-details`) adds book fields to products: authors, Urdu title, ISBN, publisher, year, pages, language and format.
- **One-command setup** (`scripts/`) installs WordPress and WooCommerce, configures the store for Pakistan and imports 28 sample books.

## Features for Pakistani customers

| Area | Setup |
| --- | --- |
| Currency | PKR shown as **Rs 1,200** (no decimals) |
| Selling region | Pakistan only, provinces in the address form, timezone Asia/Karachi |
| Payments | **Cash on Delivery** (default) and **Bank transfer / JazzCash / Easypaisa** (manual confirmation) |
| Shipping | One nationwide zone: **Rs 250 flat**, **free over Rs 3,000** |
| Checkout | Mobile number required and checked against the `03XX XXXXXXX` format; postcode and company fields are optional or hidden; classic checkout for plugin compatibility |
| WhatsApp | An **Order on WhatsApp** button on every book with a pre-filled message |
| Urdu | Urdu titles in Noto Nastaliq on the cards, product pages and generated covers. RTL styles are included if you switch WordPress to Urdu |
| Trust | COD, delivery time, 7-day returns and WhatsApp support shown on every page |
| Pages | About, Contact, Shipping & Delivery, Returns & Refunds, FAQ |

### Bookstore features
- **Authors** taxonomy with author archive pages at `/book-author/umera-ahmed/`.
- **Book details** tab on each product (author, ISBN, publisher, pages, language, format).
- Search finds books by **title, author, ISBN or Urdu title**.
- **Generated covers**: books without an image get a styled cover showing the title, Urdu title and author. Upload a real cover image and it replaces the generated one.
- Homepage with a hero search, **Shop by category**, Staff picks (featured), Bestsellers, Urdu novels, New arrivals and On sale.
- A free-delivery progress bar in the cart ("Add Rs 450 more for FREE delivery").
- Google structured data marks products as `Book` with author, ISBN and language.

## Run it locally (Docker)

Requires Docker Desktop.

```bash
cp .env.example .env
```

```bash
docker compose up -d
```

```bash
docker compose run --rm wpcli sh /project/scripts/setup.sh
```

The setup prints the admin password. Then open:
- Store: http://localhost:8080
- Admin: http://localhost:8080/wp-admin

To start again from scratch, run `docker compose down -v`.

## Put it on real hosting (cPanel, Hostinger and similar)

1. Install WordPress from your host's control panel.
2. Upload `wp-content/themes/kitab` to `wp-content/themes/` and `wp-content/plugins/kitab-book-details` to `wp-content/plugins/`. You can use the File Manager or SFTP, or zip each folder and upload it under **Appearance → Themes → Add New** and **Plugins → Add New**.
3. Install and activate **WooCommerce**, then activate **Kitab Book Details** and the **Kitab** theme.
4. If your host gives you SSH and WP-CLI, run these from the WordPress folder (after uploading this repo's `scripts/` and `sample-data/`) to apply the Pakistan store settings and optionally the sample books:

   ```bash
   wp eval-file scripts/configure-store.php
   ```

   ```bash
   wp eval-file scripts/import-books.php sample-data/books.json
   ```

   Without SSH, apply the same settings by hand in **WooCommerce → Settings**: currency PKR with 0 decimals, selling and shipping to Pakistan only, payments and shipping as listed in the table above.
5. Get an SSL certificate (usually free with your host) and set **Settings → Permalinks** to *Post name*.

## Before you launch: things to change

- [ ] **Appearance → Customize → Bookstore settings**: phone, **WhatsApp number** (digits only, e.g. `923001234567`), email, address, announcement text.
- [ ] **Appearance → Customize → Site Identity**: upload your logo.
- [ ] **WooCommerce → Settings → Payments → Direct bank transfer**: enter your real bank, JazzCash and Easypaisa account details. The setup script leaves placeholders there.
- [ ] **WooCommerce → Settings → General**: your real store address.
- [ ] Update the Contact page and check the Shipping and Returns policies.
- [ ] Replace the sample books with your own stock and upload real cover photos. The sample prices, stock levels and descriptions are only demo data.
- [ ] Change the free-delivery amount in **WooCommerce → Settings → Shipping**. If you change it, also add `add_filter( 'kitab_free_shipping_threshold', fn() => 2500 );` (with your amount) in a small plugin so the cart progress bar matches.

## Recommended plugins for Pakistan

- **Online payments**: your bank's or provider's official WooCommerce gateway (e.g. JazzCash, Easypaisa, PayFast, Safepay or a card gateway from your bank).
- **Couriers**: plugins for TCS, Leopards, PostEx, Trax or M&P to book parcels and send tracking numbers.
- **SMS/WhatsApp order alerts**: send customers order confirmation and tracking updates.
- **Caching and security**: LiteSpeed Cache (if your host uses LiteSpeed) or WP Super Cache, and Wordfence.
- **Backups**: UpdraftPlus.

## Project layout

```
docker-compose.yml           Local WordPress + MariaDB + WP-CLI
scripts/setup.sh             One-time installer (run in the wpcli container)
scripts/configure-store.php  Pakistan store settings, pages, menus, widgets
scripts/import-books.php     Imports sample-data/books.json (skips existing SKUs)
sample-data/books.json       9 categories, 28 sample books
wp-content/themes/kitab/     The theme
wp-content/plugins/kitab-book-details/  Book fields, authors and search
```
