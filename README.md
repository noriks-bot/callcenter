# Noriks Call Center (PHP Version)

Call center application for managing abandoned carts, suppressed profiles, and pending orders.

## Features

- **Abandoned Carts** - View and manage abandoned shopping carts from all stores
- **Suppressed Profiles** - Klaviyo unsubscribed/bounced email profiles
- **Pending Orders** - Failed, cancelled, on-hold orders

## Installation (cPanel)

1. Upload all files to your desired directory (e.g., `/public_html/callcenter/`)
2. Create the `data/` directory with write permissions (755)
3. Make sure PHP cURL extension is enabled

## Login Credentials

- Admin: `noriks` / `noriks` (access to all stores)
- HR Agent: `hr` / `hr` (access to HR store only)

## API Endpoints

All endpoints via `api.php`:

- `api.php?action=abandoned-carts` - Get all abandoned carts
- `api.php?action=suppressed-profiles` - Get Klaviyo suppressed profiles
- `api.php?action=pending-orders` - Get pending/failed orders
- `api.php?action=stores` - Get store list
- `api.php?action=update-status` (POST) - Update call status/notes
- `api.php?action=login` (POST) - Authenticate user

## Files

- `index.html` - Main dashboard
- `login.html` - Login page
- `report.html` - Reports page
- `styles.css` - Stylesheet
- `api.php` - PHP API backend
- `data/` - JSON storage for call status (auto-created)

## Requirements

- PHP 7.4+
- cURL extension
- Write permissions for `data/` directory
