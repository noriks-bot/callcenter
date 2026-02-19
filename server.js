const express = require('express');
const cors = require('cors');
const axios = require('axios');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3087;

app.use(cors());
app.use(express.json());

// Disable caching for HTML files
app.use((req, res, next) => {
  if (req.path.endsWith('.html') || req.path === '/' || req.path === '/login' || req.path === '/report') {
    res.set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
  }
  next();
});

app.use(express.static(path.join(__dirname, 'public')));

// WooCommerce API credentials for all stores
const stores = {
  hr: {
    name: 'Croatia',
    flag: '🇭🇷',
    url: 'https://noriks.com/hr',
    ck: 'ck_d73881b20fd65125fb071414b8d54af7681549e3',
    cs: 'cs_e024298df41e4352d90e006d2ec42a5b341c1ce5'
  },
  cz: {
    name: 'Czech',
    flag: '🇨🇿',
    url: 'https://noriks.com/cz',
    ck: 'ck_396d624acec5f7a46dfcfa7d2a74b95c82b38962',
    cs: 'cs_2a69c7ad4a4d118a2b8abdf44abdd058c9be9115'
  },
  pl: {
    name: 'Poland',
    flag: '🇵🇱',
    url: 'https://noriks.com/pl',
    ck: 'ck_8fd83582ada887d0e586a04bf870d43634ca8f2c',
    cs: 'cs_f1bf98e46a3ae0623c5f2f9fcf7c2478240c5115'
  },
  gr: {
    name: 'Greece',
    flag: '🇬🇷',
    url: 'https://noriks.com/gr',
    ck: 'ck_2595568b83966151e08031e42388dd1c34307107',
    cs: 'cs_dbd091b4fc11091638f8ec4c838483be32cfb15b'
  },
  sk: {
    name: 'Slovakia',
    flag: '🇸🇰',
    url: 'https://noriks.com/sk',
    ck: 'ck_1abaeb006bb9039da0ad40f00ab674067ff1d978',
    cs: 'cs_32b33bc2716b07a738ff18eb377a767ef60edfe7'
  },
  it: {
    name: 'Italy',
    flag: '🇮🇹',
    url: 'https://noriks.com/it',
    ck: 'ck_84a1e1425710ff9eeed69b100ed9ac445efc39e2',
    cs: 'cs_81d25dcb0371773387da4d30482afc7ce83d1b3e'
  },
  hu: {
    name: 'Hungary',
    flag: '🇭🇺',
    url: 'https://noriks.com/hu',
    ck: 'ck_e591c2a0bf8c7a59ec5893e03adde3c760fbdaae',
    cs: 'cs_d84113ee7a446322d191be0725c0c92883c984c3'
  }
};

// In-memory storage for call status and notes (in production, use a database)
const callData = new Map();

// Helper function to make WooCommerce API request
async function wcRequest(store, endpoint, params = {}) {
  const config = stores[store];
  if (!config) throw new Error(`Unknown store: ${store}`);
  
  try {
    const response = await axios.get(`${config.url}/wp-json/wc/v3/${endpoint}`, {
      auth: {
        username: config.ck,
        password: config.cs
      },
      params,
      timeout: 15000
    });
    return response.data;
  } catch (error) {
    console.error(`WC API error for ${store}:`, error.message);
    return [];
  }
}

// Stores with abandoned cart plugin endpoint
const abandonedCartStores = {
  hr: 'https://noriks.com/hr/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355',
  cz: 'https://noriks.com/cz/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355',
  pl: 'https://noriks.com/pl/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355',
  sk: 'https://noriks.com/sk/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355',
  hu: 'https://noriks.com/hu/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355',
  gr: 'https://noriks.com/gr/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355',
  it: 'https://noriks.com/it/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355'
};

// Currency per store
const storeCurrencies = {
  hr: 'EUR',
  cz: 'CZK',
  pl: 'PLN',
  sk: 'EUR',
  hu: 'HUF',
  gr: 'EUR',
  it: 'EUR'
};

// Get abandoned carts from stores with the plugin
app.get('/api/abandoned-carts', async (req, res) => {
  try {
    const allCarts = [];
    
    for (const [storeCode, endpoint] of Object.entries(abandonedCartStores)) {
      const config = stores[storeCode];
      if (!config) continue;
      
      try {
        const response = await axios.get(endpoint, { timeout: 15000 });
        const carts = response.data;
        
        for (const cart of carts) {
          const cartId = `${storeCode}_${cart.id}`;
          const savedData = callData.get(cartId) || {};
          
          // Parse cart contents
          const cartContents = [];
          if (cart.cart_contents && typeof cart.cart_contents === 'object') {
            for (const item of Object.values(cart.cart_contents)) {
              const lines = item._orto_lines || [];
              cartContents.push({
                name: lines.length > 0 ? lines.join(', ') : `Product #${item.product_id}`,
                quantity: item.quantity || 1,
                price: parseFloat(item.line_total) || 0,
                productId: item.product_id
              });
            }
          }
          
          // Extract customer info from other_fields
          const fields = cart.other_fields || {};
          const firstName = fields.wcf_first_name || '';
          const lastName = fields.wcf_last_name || '';
          const phone = fields.wcf_phone_number || '';
          const location = fields.wcf_location || '';
          
          allCarts.push({
            id: cartId,
            storeCode,
            storeName: config.name,
            storeFlag: config.flag,
            cartDbId: cart.id,
            customerName: `${firstName} ${lastName}`.trim() || 'Unknown',
            email: cart.email || '',
            phone: phone,
            location: location.replace(/^,\s*/, ''),
            cartContents,
            cartValue: parseFloat(cart.cart_total) || 0,
            currency: storeCurrencies[storeCode] || 'EUR',
            abandonedAt: cart.time,
            status: cart.order_status,
            callStatus: savedData.callStatus || 'not_called',
            notes: savedData.notes || '',
            lastUpdated: savedData.lastUpdated || null
          });
        }
      } catch (err) {
        console.error(`Error fetching from ${storeCode}:`, err.message);
      }
    }
    
    // Sort by date (newest first)
    allCarts.sort((a, b) => new Date(b.abandonedAt) - new Date(a.abandonedAt));
    
    res.json(allCarts);
  } catch (error) {
    console.error('Error fetching abandoned carts:', error);
    res.status(500).json({ error: 'Failed to fetch abandoned carts' });
  }
});

// Update call status and notes
app.post('/api/update-status', (req, res) => {
  const { id, callStatus, notes } = req.body;
  
  if (!id) {
    return res.status(400).json({ error: 'Missing cart ID' });
  }
  
  const existing = callData.get(id) || {};
  callData.set(id, {
    ...existing,
    callStatus: callStatus || existing.callStatus,
    notes: notes !== undefined ? notes : existing.notes,
    lastUpdated: new Date().toISOString()
  });
  
  res.json({ success: true, data: callData.get(id) });
});

// Klaviyo suppressed profiles endpoint
const KLAVIYO_API_KEY = process.env.KLAVIYO_API_KEY || 'pk_961349939ac712880db8078dd802f74082';

// Cache for suppressed profiles (refresh every 30 min)
let suppressedCache = { data: [], timestamp: 0 };
const CACHE_TTL = 30 * 60 * 1000;

// Order cache by email
const orderCache = new Map();

// Fetch last order for email from a SINGLE store (sequential to avoid rate limits)
async function fetchLastOrderFromStore(email, code, config) {
  try {
    const response = await axios.get(`${config.url}/wp-json/wc/v3/orders`, {
      auth: { username: config.ck, password: config.cs },
      params: { search: email, per_page: 1, orderby: 'date', order: 'desc' },
      timeout: 15000
    });
    
    if (response.data && response.data.length > 0) {
      const order = response.data[0];
      return {
        storeCode: code,
        storeFlag: config.flag,
        storeName: config.name,
        orderId: order.id,
        date: order.date_created,
        total: order.total,
        currency: order.currency,
        status: order.status,
        items: order.line_items.map(i => ({
          name: i.name,
          quantity: i.quantity,
          price: i.total
        }))
      };
    }
  } catch (err) {
    // Skip on error
  }
  return null;
}

// Background order fetching - HR store only (Klaviyo is HR account)
let orderFetchInProgress = false;
async function fetchOrdersBackground(profiles) {
  if (orderFetchInProgress) return;
  orderFetchInProgress = true;
  
  console.log(`Starting background order fetch for ${profiles.length} profiles (HR only)...`);
  
  const hrStore = stores.hr;
  let found = 0;
  
  for (const profile of profiles) {
    if (orderCache.has(profile.email)) continue;
    if (!profile.email) continue;
    
    // Only check HR store (Klaviyo account is Croatian)
    const order = await fetchLastOrderFromStore(profile.email, 'hr', hrStore);
    if (order) {
      orderCache.set(profile.email, order);
      profile.lastOrder = order;
      found++;
    }
    
    // Small delay to avoid rate limiting
    await new Promise(r => setTimeout(r, 150));
    
    if (found % 10 === 0 && found > 0) {
      console.log(`Found ${found} orders so far...`);
    }
  }
  
  // Re-sort profiles after orders fetched (by suppression date, newest first)
  profiles.sort((a, b) => {
    const dateA = a.suppressedAt ? new Date(a.suppressedAt) : new Date(0);
    const dateB = b.suppressedAt ? new Date(b.suppressedAt) : new Date(0);
    return dateB - dateA;
  });
  
  // Update cache
  suppressedCache = { data: profiles, timestamp: Date.now() };
  
  console.log(`Background order fetch complete! Found ${found} orders`);
  orderFetchInProgress = false;
}

app.get('/api/suppressed-profiles', async (req, res) => {
  // Return cached if fresh
  if (Date.now() - suppressedCache.timestamp < CACHE_TTL && suppressedCache.data.length > 0) {
    return res.json(suppressedCache.data);
  }

  try {
    const allProfiles = [];
    const seenIds = new Set();
    let cursor = null;
    let pageCount = 0;
    
    // Fetch ALL profiles that are NOT subscribed (suppressed = anyone not actively subscribed)
    // This should capture: unsubscribed, bounced, complained, never subscribed, etc.
    do {
      const params = {
        'filter': 'not(equals(subscriptions.email.marketing.consent,"SUBSCRIBED"))',
        'page[size]': 100
      };
      if (cursor) {
        params['page[cursor]'] = cursor;
      }
      
      try {
        const response = await axios.get('https://a.klaviyo.com/api/profiles/', {
          headers: {
            'Authorization': `Klaviyo-API-Key ${KLAVIYO_API_KEY}`,
            'revision': '2024-02-15'
          },
          params,
          timeout: 30000
        });
        
        for (const profile of response.data.data) {
          if (!seenIds.has(profile.id)) {
            seenIds.add(profile.id);
            allProfiles.push(profile);
          }
        }
        
        cursor = response.data.links?.next ? new URL(response.data.links.next).searchParams.get('page[cursor]') : null;
        pageCount++;
        
        if (pageCount % 5 === 0) {
          console.log(`Fetched ${allProfiles.length} profiles so far...`);
        }
      } catch (err) {
        console.log(`Filter failed: ${err.message}`);
        break;
      }
    } while (cursor && pageCount < 100);
    
    console.log(`Total NOT SUBSCRIBED profiles: ${allProfiles.length}`);
    
    // If NOT filter doesn't work, fall back to suppression reasons
    if (allProfiles.length < 100) {
      console.log('Falling back to suppression reason filters...');
      const suppressionReasons = ['user_suppressed', 'hard_bounce', 'spam_complaint', 'invalid_email'];
      
      for (const reason of suppressionReasons) {
        let reasonCursor = null;
        let reasonPageCount = 0;
        
        do {
          const params = {
            'filter': `equals(subscriptions.email.marketing.suppression.reason,"${reason}")`,
            'page[size]': 100
          };
          if (reasonCursor) {
            params['page[cursor]'] = reasonCursor;
          }
          
          try {
            const response = await axios.get('https://a.klaviyo.com/api/profiles/', {
              headers: {
                'Authorization': `Klaviyo-API-Key ${KLAVIYO_API_KEY}`,
                'revision': '2024-02-15'
              },
              params,
              timeout: 30000
            });
            
            let added = 0;
            for (const profile of response.data.data) {
              if (!seenIds.has(profile.id)) {
                seenIds.add(profile.id);
                allProfiles.push(profile);
                added++;
              }
            }
            if (added > 0) console.log(`Reason ${reason}: +${added} new profiles`);
            
            reasonCursor = response.data.links?.next ? new URL(response.data.links.next).searchParams.get('page[cursor]') : null;
            reasonPageCount++;
          } catch (err) {
            break;
          }
        } while (reasonCursor && reasonPageCount < 20);
      }
    }
    
    console.log(`Fetched ${allProfiles.length} suppressed profiles from Klaviyo`);
    
    // Map profiles quickly (without orders)
    const profiles = allProfiles.map(profile => {
      const profileId = `klaviyo_${profile.id}`;
      const savedData = callData.get(profileId) || {};
      const email = profile.attributes.email || '';
      
      return {
        id: profileId,
        email,
        firstName: profile.attributes.first_name || '',
        lastName: profile.attributes.last_name || '',
        phone: profile.attributes.phone_number || '',
        suppressionReason: profile.attributes.subscriptions?.email?.marketing?.suppression?.reason || 'unsubscribed',
        suppressedAt: profile.attributes.subscriptions?.email?.marketing?.suppression?.timestamp || profile.attributes.updated || '',
        lastOrder: orderCache.get(email) || null,
        callStatus: savedData.callStatus || 'not_called',
        notes: savedData.notes || ''
      };
    });
    
    // Sort by suppression date (newest suppressed first)
    profiles.sort((a, b) => {
      const dateA = a.suppressedAt ? new Date(a.suppressedAt) : new Date(0);
      const dateB = b.suppressedAt ? new Date(b.suppressedAt) : new Date(0);
      return dateB - dateA;  // Newest first
    });
    
    const withOrders = profiles.filter(p => p.lastOrder).length;
    console.log(`Returning ${profiles.length} profiles (${withOrders} with cached orders)`);
    
    // Cache results
    suppressedCache = { data: profiles, timestamp: Date.now() };
    
    // Start background fetch for orders (don't await)
    fetchOrdersBackground(profiles);
    
    res.json(profiles);
  } catch (error) {
    console.error('Klaviyo API error:', error.message);
    res.status(500).json({ error: 'Failed to fetch suppressed profiles' });
  }
});

// Get pending/failed orders from all stores
app.get('/api/pending-orders', async (req, res) => {
  try {
    const allOrders = [];
    
    // Statuses to fetch: pending, cancelled, failed, on-hold
    const statuses = ['pending', 'cancelled', 'failed', 'on-hold'];
    
    for (const [storeCode, config] of Object.entries(stores)) {
      try {
        const response = await axios.get(`${config.url}/wp-json/wc/v3/orders`, {
          auth: { username: config.ck, password: config.cs },
          params: { 
            status: statuses.join(','),
            per_page: 50,
            orderby: 'date',
            order: 'desc'
          },
          timeout: 15000
        });
        
        for (const order of response.data) {
          const orderId = `${storeCode}_order_${order.id}`;
          const savedData = callData.get(orderId) || {};
          
          allOrders.push({
            id: orderId,
            storeCode,
            storeName: config.name,
            storeFlag: config.flag,
            orderId: order.id,
            customerName: `${order.billing.first_name || ''} ${order.billing.last_name || ''}`.trim() || 'Unknown',
            email: order.billing.email || '',
            phone: order.billing.phone || '',
            location: `${order.billing.city || ''}, ${order.billing.country || ''}`.replace(/^,\s*/, '').replace(/,\s*$/, ''),
            orderStatus: order.status,
            orderTotal: parseFloat(order.total) || 0,
            currency: order.currency || 'EUR',
            createdAt: order.date_created,
            items: order.line_items.map(item => ({
              name: item.name,
              quantity: item.quantity,
              price: item.total
            })),
            callStatus: savedData.callStatus || 'not_called',
            notes: savedData.notes || '',
            lastUpdated: savedData.lastUpdated || null
          });
        }
      } catch (err) {
        console.error(`Error fetching pending orders from ${storeCode}:`, err.message);
      }
    }
    
    // Sort by date (newest first)
    allOrders.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
    
    res.json(allOrders);
  } catch (error) {
    console.error('Error fetching pending orders:', error);
    res.status(500).json({ error: 'Failed to fetch pending orders' });
  }
});

// Get all stores info
app.get('/api/stores', (req, res) => {
  res.json(Object.entries(stores).map(([code, config]) => ({
    code,
    name: config.name,
    flag: config.flag
  })));
});

// Users for authentication
const users = {
  noriks: { password: 'noriks', role: 'admin', countries: ['all'] },
  hr: { password: 'hr', role: 'agent', countries: ['hr'] }
};

// Login endpoint
app.post('/api/login', (req, res) => {
  const { username, password } = req.body;
  const user = users[username];
  
  if (user && user.password === password) {
    res.json({
      success: true,
      user: {
        username,
        role: user.role,
        countries: user.countries
      }
    });
  } else {
    res.status(401).json({ success: false, error: 'Invalid credentials' });
  }
});

// Serve login page
app.get('/login', (req, res) => {
  res.sendFile('login.html', { root: path.join(__dirname, 'public') });
});

// Serve report page
app.get('/report', (req, res) => {
  res.sendFile('report.html', { root: path.join(__dirname, 'public') });
});

// Serve index for root
app.get('/', (req, res) => {
  res.sendFile('index.html', { root: path.join(__dirname, 'public') });
});

app.listen(PORT, () => {
  console.log(`🎧 Noriks Call Center running on port ${PORT}`);
});
