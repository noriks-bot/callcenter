# Noriks Call Center

Professional call center dashboard for Noriks e-commerce stores.

## Features

### 🛒 Abandoned Cart Tab
- Pull data from ALL Noriks WooCommerce stores (HR, CZ, PL, GR, SK, IT, HU)
- Customer name, phone, email, cart contents, value, abandonment timestamp
- Country flag/code per contact
- Call status tracking:
  - Not called
  - Called - No answer
  - Callback scheduled
  - Converted
  - Not interested
- Notes field per contact
- Filter/sort by status, date, cart value, country

### 🚫 Suppressed Profiles Tab (Klaviyo)
- Pull suppressed profiles from Klaviyo API
- Name, email, phone, suppression reason, date
- Same call status tracking + notes

### 🎨 UI
- Professional call center design
- Dark/light theme toggle
- Modern, clean dashboard
- Responsive design

## Setup

1. Install dependencies:
```bash
npm install
```

2. Set environment variables (optional):
```bash
export KLAVIYO_API_KEY=your_klaviyo_private_key
export PORT=3087
```

3. Start the server:
```bash
npm start
```

## Deployment

The app runs on EC2 at miki.noriks.com/callcenter/ behind nginx proxy.

### Nginx Configuration

Add to nginx config:
```nginx
location /callcenter/ {
    proxy_pass http://localhost:3087/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

## Tech Stack

- Node.js / Express
- WooCommerce REST API
- Klaviyo API
- Vanilla JavaScript frontend
