const express = require('express');
const cors = require('cors');
const axios = require('axios');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3087;

app.use(cors());
app.use(express.json());
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

// Get abandoned carts from all stores
// Note: WooCommerce doesn't have a native abandoned cart endpoint
// We'll fetch pending/on-hold orders as a proxy for abandoned checkouts
app.get('/api/abandoned-carts', async (req, res) => {
  try {
    const allCarts = [];
    
    for (const [storeCode, config] of Object.entries(stores)) {
      try {
        // Get pending and failed orders (closest to abandoned carts in standard WC)
        const orders = await wcRequest(storeCode, 'orders', {
          status: 'pending,on-hold,failed,checkout-draft',
          per_page: 50,
          orderby: 'date',
          order: 'desc'
        });
        
        for (const order of orders) {
          const cartId = `${storeCode}_${order.id}`;
          const savedData = callData.get(cartId) || {};
          
          allCarts.push({
            id: cartId,
            storeCode,
            storeName: config.name,
            storeFlag: config.flag,
            orderId: order.id,
            customerName: `${order.billing.first_name} ${order.billing.last_name}`.trim() || 'Unknown',
            email: order.billing.email || '',
            phone: order.billing.phone || '',
            cartContents: order.line_items.map(item => ({
              name: item.name,
              quantity: item.quantity,
              price: parseFloat(item.total)
            })),
            cartValue: parseFloat(order.total),
            currency: order.currency,
            abandonedAt: order.date_created,
            status: order.status,
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
// Note: You'll need to add your Klaviyo private API key
const KLAVIYO_API_KEY = process.env.KLAVIYO_API_KEY || '';

app.get('/api/suppressed-profiles', async (req, res) => {
  if (!KLAVIYO_API_KEY) {
    // Return mock data if no API key
    return res.json([
      {
        id: 'mock_1',
        email: 'test@example.com',
        firstName: 'Test',
        lastName: 'User',
        phone: '+1234567890',
        suppressionReason: 'unsubscribed',
        suppressedAt: new Date().toISOString(),
        callStatus: 'not_called',
        notes: ''
      }
    ]);
  }
  
  try {
    const response = await axios.get('https://a.klaviyo.com/api/profiles/', {
      headers: {
        'Authorization': `Klaviyo-API-Key ${KLAVIYO_API_KEY}`,
        'revision': '2024-02-15'
      },
      params: {
        'filter': 'equals(subscriptions.email.marketing.suppression.reason,"user_suppressed")'
      }
    });
    
    const profiles = response.data.data.map(profile => {
      const profileId = `klaviyo_${profile.id}`;
      const savedData = callData.get(profileId) || {};
      
      return {
        id: profileId,
        email: profile.attributes.email || '',
        firstName: profile.attributes.first_name || '',
        lastName: profile.attributes.last_name || '',
        phone: profile.attributes.phone_number || '',
        suppressionReason: profile.attributes.subscriptions?.email?.marketing?.suppression?.reason || 'unknown',
        suppressedAt: profile.attributes.subscriptions?.email?.marketing?.suppression?.timestamp || '',
        callStatus: savedData.callStatus || 'not_called',
        notes: savedData.notes || ''
      };
    });
    
    res.json(profiles);
  } catch (error) {
    console.error('Klaviyo API error:', error.message);
    res.status(500).json({ error: 'Failed to fetch suppressed profiles' });
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

// Serve the main app
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

app.listen(PORT, () => {
  console.log(`🎧 Noriks Call Center running on port ${PORT}`);
});
