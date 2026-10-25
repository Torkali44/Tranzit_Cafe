# Tranzit Café (ترانزيت كافيه)

Tranzit Café is a comprehensive Point of Sale (POS) and Cafe Management System built with Laravel, Tailwind CSS, and Alpine.js. It features a modern, responsive, and intuitive Arabic (RTL) user interface designed to streamline cafe operations, from order taking to advanced reporting.

## Key Features

### 1. Point of Sale (POS)
- **Interactive POS Interface:** Fast and responsive screen for cashiers to take orders.
- **Order Types:** Support for Dine-in (linked to specific tables) and Takeaway orders.
- **Product Add-ons:** Easily add customizable options and extras to items.
- **Order Management:** Save orders to tables, complete payments, or cancel orders.

### 2. Table Management & Waiting List
- **Table Status:** Real-time tracking of table states (Available, Occupied, Awaiting Payment).
- **Capacity Control:** Smart checks to ensure party sizes fit the selected table capacity.
- **Waiting List:** Efficiently manage walk-in customers waiting for available tables, with quick seating actions.

### 3. Shift Management
- **Cashier Shifts:** Cashiers open shifts with an initial cash amount and close them at the end of their work.
- **Admin Review:** Administrators can review closed shifts, track expected vs. actual cash, and monitor variances (shortages/overages).

### 4. Admin Dashboard & Reporting
- **Catalog Management:** Full control over product categories, items, and pricing.
- **Settings:** Customizable cafe name and dynamic service charge percentages.
- **Comprehensive Reports:** Detailed analytics including daily sales, hourly peak times, best-selling items, cashier performance, and table utilization.
- **Excel Export:** Export full financial and operational reports to Excel for accounting and record-keeping.

### 5. Role-Based Access Control
- **Admin:** Full access to the dashboard, reports, settings, and shift reviews.
- **Cashier:** Restricted access focused purely on the POS, table management, and their own shifts.

### 6. Professional Landing Page
- A beautifully designed, responsive landing page showcasing the cafe's offerings, quick stats, and direct access to the system based on user roles.

## Tech Stack
- **Backend:** Laravel (PHP)
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Database:** SQLite / MySQL
- **Export:** Excel generation for reports
