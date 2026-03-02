const express = require('express');
const cors = require('cors');
const axios = require('axios');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = process.env.PORT || 3087;

app.use(cors());
app.use(express.json({ limit: '10mb' }));

// Disable caching for HTML files
app.use((req, res, next) => {
  if (req.path.endsWith('.html') || req.path === '/' || req.path === '/login' || req.path === '/report') {
    res.set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
  }
  next();
});

app.use(express.static(path.join(__dirname, 'public')));

// ========== CONFIGURATION ==========
const stores = {
  hr: { name: 'Croatia', flag: '🇭🇷', url: 'https://noriks.com/hr', ck: 'ck_d73881b20fd65125fb071414b8d54af7681549e3', cs: 'cs_e024298df41e4352d90e006d2ec42a5b341c1ce5' },
  cz: { name: 'Czech', flag: '🇨🇿', url: 'https://noriks.com/cz', ck: 'ck_396d624acec5f7a46dfcfa7d2a74b95c82b38962', cs: 'cs_2a69c7ad4a4d118a2b8abdf44abdd058c9be9115' },
  pl: { name: 'Poland', flag: '🇵🇱', url: 'https://noriks.com/pl', ck: 'ck_8fd83582ada887d0e586a04bf870d43634ca8f2c', cs: 'cs_f1bf98e46a3ae0623c5f2f9fcf7c2478240c5115' },
  gr: { name: 'Greece', flag: '🇬🇷', url: 'https://noriks.com/gr', ck: 'ck_2595568b83966151e08031e42388dd1c34307107', cs: 'cs_dbd091b4fc11091638f8ec4c838483be32cfb15b' },
  sk: { name: 'Slovakia', flag: '🇸🇰', url: 'https://noriks.com/sk', ck: 'ck_1abaeb006bb9039da0ad40f00ab674067ff1d978', cs: 'cs_32b33bc2716b07a738ff18eb377a767ef60edfe7' },
  it: { name: 'Italy', flag: '🇮🇹', url: 'https://noriks.com/it', ck: 'ck_84a1e1425710ff9eeed69b100ed9ac445efc39e2', cs: 'cs_81d25dcb0371773387da4d30482afc7ce83d1b3e' },
  hu: { name: 'Hungary', flag: '🇭🇺', url: 'https://noriks.com/hu', ck: 'ck_e591c2a0bf8c7a59ec5893e03adde3c760fbdaae', cs: 'cs_d84113ee7a446322d191be0725c0c92883c984c3' }
};

const storeCurrencies = { hr: 'EUR', cz: 'CZK', pl: 'PLN', sk: 'EUR', hu: 'HUF', gr: 'EUR', it: 'EUR' };
const storeCountryCodes = { hr: 'HR', cz: 'CZ', pl: 'PL', sk: 'SK', hu: 'HU', gr: 'GR', it: 'IT' };
const phoneCountryCodes = { hr: '385', cz: '420', pl: '48', gr: '30', sk: '421', it: '39', hu: '36', si: '386' };

const metakocka = {
  company_id: 6371,
  secret_key: 'ee759602-961d-4431-ac64-0725ae8d9665',
  api_url: 'https://main.metakocka.si/rest/eshop/send_message'
};

const SMS_ESHOP_SYNC_ID = '637100000075';

const DATA_DIR = path.join(__dirname, 'data');
const CACHE_DIR = path.join(DATA_DIR, 'cache');

// Ensure directories exist
if (!fs.existsSync(DATA_DIR)) fs.mkdirSync(DATA_DIR, { recursive: true });
if (!fs.existsSync(CACHE_DIR)) fs.mkdirSync(CACHE_DIR, { recursive: true });

// ========== FILE HELPERS ==========
function readJson(filePath, defaultVal = {}) {
  try {
    if (fs.existsSync(filePath)) return JSON.parse(fs.readFileSync(filePath, 'utf8'));
  } catch (e) { console.error(`Error reading ${filePath}:`, e.message); }
  return typeof defaultVal === 'function' ? defaultVal() : defaultVal;
}

function writeJson(filePath, data) {
  const dir = path.dirname(filePath);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(filePath, JSON.stringify(data, null, 2));
}

// ========== CACHE SYSTEM ==========
function getCache(key, maxAge = 300) {
  const file = path.join(CACHE_DIR, require('crypto').createHash('md5').update(key).digest('hex') + '.json');
  try {
    if (fs.existsSync(file)) {
      const stat = fs.statSync(file);
      if ((Date.now() - stat.mtimeMs) / 1000 < maxAge) {
        return JSON.parse(fs.readFileSync(file, 'utf8'));
      }
    }
  } catch (e) {}
  return null;
}

function setCache(key, data) {
  const file = path.join(CACHE_DIR, require('crypto').createHash('md5').update(key).digest('hex') + '.json');
  try { writeJson(file, data); } catch (e) {}
}

function clearAllCache() {
  try {
    if (fs.existsSync(CACHE_DIR)) {
      fs.readdirSync(CACHE_DIR).filter(f => f.endsWith('.json')).forEach(f => fs.unlinkSync(path.join(CACHE_DIR, f)));
    }
    const buyersCacheFile = path.join(DATA_DIR, 'buyers-cache.json');
    if (fs.existsSync(buyersCacheFile)) fs.unlinkSync(buyersCacheFile);
  } catch (e) {}
}

// ========== DATA FILE PATHS ==========
const callDataFile = path.join(DATA_DIR, 'call_data.json');
const smsQueueFile = path.join(DATA_DIR, 'sms_queue.json');
const smsSettingsFile = path.join(DATA_DIR, 'sms-settings.json');
const agentsFile = path.join(DATA_DIR, 'agents.json');
const callLogsFile = path.join(DATA_DIR, 'call-logs.json');
const paketomatStatusFile = path.join(DATA_DIR, 'paketomat-status.json');
const notificationSettingsFile = path.join(DATA_DIR, 'notification-settings.json');
const lastSeenFile = path.join(DATA_DIR, 'last-seen.json');
const automationsFile = path.join(DATA_DIR, 'sms-automations.json');
const queuedCartsFile = path.join(DATA_DIR, 'automation-queued-carts.json');
const buyersSettingsFile = path.join(DATA_DIR, 'buyers-settings.json');
const smsTemplatesFile = path.join(DATA_DIR, 'sms-templates.json');
const emailTemplatesFile = path.join(DATA_DIR, 'email-templates.json');

// ========== DATA LOADERS ==========
function loadCallData() { return readJson(callDataFile, {}); }
function saveCallData(data) { writeJson(callDataFile, data); }
function loadSmsQueue() { return readJson(smsQueueFile, []); }
function saveSmsQueue(data) { writeJson(smsQueueFile, data); }
function loadCallLogs() { const d = readJson(callLogsFile, { logs: [] }); return d.logs || []; }
function saveCallLogs(logs) { writeJson(callLogsFile, { logs }); }
function loadPaketomatStatus() { return readJson(paketomatStatusFile, {}); }
function savePaketomatStatus(data) { writeJson(paketomatStatusFile, data); }
function loadNotificationSettings() { return readJson(notificationSettingsFile, { desktopEnabled: true, soundEnabled: true, pollingInterval: 30000 }); }
function saveNotificationSettings(data) { writeJson(notificationSettingsFile, data); }
function loadLastSeen() { return readJson(lastSeenFile, { }); }
function saveLastSeen(data) { writeJson(lastSeenFile, data); }

function loadAgents() {
  const data = readJson(agentsFile, null);
  if (data && data.users) return data;
  return { users: [{ id: 'admin_1', username: 'noriks', password: 'noriks2024', role: 'admin', countries: ['all'], createdAt: new Date().toISOString(), active: true }] };
}
function saveAgents(data) { writeJson(agentsFile, data); }

function loadSmsSettings() {
  return {
    providers: Object.fromEntries(
      ['hr', 'cz', 'pl', 'gr', 'sk', 'it', 'hu', 'si'].map(code => [code, { eshop_sync_id: SMS_ESHOP_SYNC_ID, enabled: true, lastTest: null }])
    )
  };
}

// ========== PHONE FORMATTING ==========
function formatPhoneForSms(phone, storeCode) {
  const countryCode = phoneCountryCodes[storeCode] || '385';
  phone = (phone || '').trim();
  if (phone.startsWith('+')) phone = phone.substring(1);
  if (phone.startsWith('00')) phone = phone.substring(2);
  phone = phone.replace(/[^0-9]/g, '');

  let hasCountryCode = false;
  for (const code of Object.values(phoneCountryCodes)) {
    if (phone.startsWith(code)) { hasCountryCode = true; break; }
  }

  if (!hasCountryCode && phone.startsWith('0')) {
    phone = countryCode + phone.substring(1);
  } else if (!hasCountryCode) {
    phone = countryCode + phone;
  }
  return '+' + phone;
}

function validatePhoneForSms(phone, storeCode) {
  if (!phone || !phone.trim()) return { valid: false, error: 'Telefonska številka je prazna' };
  const cleaned = phone.replace(/[^0-9]/g, '');
  if (cleaned.length < 7) return { valid: false, error: 'Telefonska številka je prekratka (min. 7 številk)' };
  if (cleaned.length > 15) return { valid: false, error: 'Telefonska številka je predolga (max. 15 številk)' };
  const formatted = formatPhoneForSms(phone, storeCode);
  const countryCode = phoneCountryCodes[storeCode] || '385';
  const digitsOnly = formatted.replace(/[^0-9]/g, '');
  const nationalPart = digitsOnly.substring(countryCode.length);
  if (nationalPart.length < 6) return { valid: false, error: 'Nacionalni del številke je prekratek' };
  if (nationalPart.length > 12) return { valid: false, error: 'Nacionalni del številke je predolg' };
  return { valid: true, formatted };
}

// ========== WooCommerce API ==========
async function wcApiRequest(storeCode, endpoint, params = {}, method = 'GET', body = null) {
  const config = stores[storeCode];
  if (!config) return { error: 'Invalid store' };
  try {
    const url = `${config.url}/wp-json/wc/v3/${endpoint}`;
    const options = {
      method,
      url,
      auth: { username: config.ck, password: config.cs },
      timeout: 30000,
      ...(method === 'GET' ? { params } : {}),
      ...(method === 'POST' ? { data: body, headers: { 'Content-Type': 'application/json' } } : {})
    };
    const response = await axios(options);
    return response.data || [];
  } catch (error) {
    const msg = error.response?.data?.message || error.message;
    return { error: msg, code: error.response?.status };
  }
}

// ========== FETCH RECENT ORDER CONTACTS ==========
async function getRecentOrderContacts(storeCode) {
  const cacheKey = `recent_order_contacts_${storeCode}`;
  const cached = getCache(cacheKey, 300);
  if (cached) return cached;

  const contacts = { emails: [], phones: [] };
  const orders = await wcApiRequest(storeCode, 'orders', {
    status: 'processing,completed', per_page: 100,
    after: new Date(Date.now() - 7 * 86400000).toISOString()
  });
  if (Array.isArray(orders)) {
    for (const order of orders) {
      if (order.billing?.email) contacts.emails.push(order.billing.email.toLowerCase());
      if (order.billing?.phone) {
        const phone = order.billing.phone.replace(/[^0-9]/g, '');
        if (phone.length >= 7) {
          contacts.phones.push(phone);
          if (phone.length > 9) contacts.phones.push(phone.slice(-9));
        }
      }
    }
  }
  setCache(cacheKey, contacts);
  return contacts;
}

// ========== FETCH ABANDONED CARTS ==========
async function fetchAbandonedCarts() {
  const cached = getCache('abandoned_carts_filtered', 300);
  if (cached) {
    const callData = loadCallData();
    for (const cart of cached) {
      const saved = callData[cart.id] || {};
      cart.callStatus = saved.callStatus || 'not_called';
      cart.notes = saved.notes || '';
    }
    return cached;
  }

  const callData = loadCallData();
  const allCarts = [];

  // Fetch order contacts for conversion tracking
  const orderContacts = {};
  const contactPromises = Object.keys(stores).map(async code => {
    orderContacts[code] = await getRecentOrderContacts(code);
  });
  await Promise.all(contactPromises);

  // Fetch abandoned carts from all stores in parallel
  const cartPromises = Object.entries(stores).map(async ([storeCode, config]) => {
    try {
      const response = await axios.get(`https://noriks.com/${storeCode}/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355`, { timeout: 20000 });
      const carts = response.data;
      if (!Array.isArray(carts)) return;

      const storeContacts = orderContacts[storeCode] || { emails: [], phones: [] };

      for (const cart of carts) {
        if (!cart || typeof cart !== 'object') continue;
        const cartTime = cart.time ? new Date(cart.time).getTime() : 0;
        const cartAgeHours = cartTime ? (Date.now() - cartTime) / 3600000 : 0;
        if (cartAgeHours < 1) continue;

        const cartEmail = (cart.email || '').toLowerCase();
        const cartPhone = ((cart.other_fields || {}).wcf_phone_number || '').replace(/[^0-9]/g, '');
        const cartPhoneLast9 = cartPhone.length > 9 ? cartPhone.slice(-9) : cartPhone;

        let isConverted = false;
        if (cartEmail && storeContacts.emails.includes(cartEmail)) isConverted = true;
        if (!isConverted && cartPhone && (storeContacts.phones.includes(cartPhone) || storeContacts.phones.includes(cartPhoneLast9))) isConverted = true;

        const cartId = `${storeCode}_${cart.id || 'unknown'}`;
        const savedData = callData[cartId] || {};

        if (savedData.callStatus === 'converted' && savedData.orderId) continue;

        const cartContents = [];
        const cartData = cart.cart_contents || {};
        if (typeof cartData === 'object') {
          for (const item of Object.values(cartData)) {
            if (!item || typeof item !== 'object') continue;
            const lines = item._orto_lines || [];
            const productId = item.product_id || null;
            const variationId = item.variation_id || null;
            let name = lines.length > 0 ? lines.join(', ') : `Product #${productId || 'unknown'}`;
            cartContents.push({
              name, quantity: parseInt(item.quantity) || 1,
              price: parseFloat(item.line_total) || 0,
              productId, variationId
            });
          }
        }

        const fields = cart.other_fields || {};
        allCarts.push({
          id: cartId, storeCode, storeName: config.name, storeFlag: config.flag,
          cartDbId: cart.id || null,
          customerName: `${fields.wcf_first_name || ''} ${fields.wcf_last_name || ''}`.trim() || 'Unknown',
          firstName: fields.wcf_first_name || '', lastName: fields.wcf_last_name || '',
          email: cart.email || '', phone: fields.wcf_phone_number || '',
          address: fields.wcf_billing_address_1 || '', city: (fields.wcf_location || '').replace(/^,\s*/, ''),
          postcode: fields.wcf_billing_postcode || '', location: (fields.wcf_location || '').replace(/^,\s*/, ''),
          cartContents, cartValue: parseFloat(cart.cart_total) || 0,
          currency: storeCurrencies[storeCode] || 'EUR',
          abandonedAt: cart.time || '', status: cart.order_status || '',
          callStatus: savedData.callStatus || 'not_called', notes: savedData.notes || '',
          lastUpdated: savedData.lastUpdated || null, orderId: savedData.orderId || null,
          converted: isConverted
        });
      }
    } catch (err) {
      console.error(`Error fetching carts from ${storeCode}:`, err.message);
    }
  });

  await Promise.all(cartPromises);
  allCarts.sort((a, b) => new Date(b.abandonedAt || '1970-01-01') - new Date(a.abandonedAt || '1970-01-01'));
  setCache('abandoned_carts_filtered', allCarts);
  return allCarts;
}

// ========== FETCH ONE-TIME BUYERS ==========
async function fetchOneTimeBuyers(storeFilter = null) {
  const minDaysFromPurchase = (() => {
    try {
      const s = readJson(buyersSettingsFile, { minDaysFromPurchase: 10 });
      return parseInt(s.minDaysFromPurchase) || 10;
    } catch { return 10; }
  })();

  const cacheKey = `one_time_buyers_${storeFilter || 'all'}_${minDaysFromPurchase}`;
  const cached = getCache(cacheKey, 300);
  if (cached) {
    const callData = loadCallData();
    for (const buyer of cached) {
      const saved = callData[buyer.id] || {};
      buyer.callStatus = saved.callStatus || 'not_called';
      buyer.notes = saved.notes || '';
    }
    return cached;
  }

  const callData = loadCallData();
  const allBuyers = [];
  const storesToFetch = storeFilter ? { [storeFilter]: stores[storeFilter] } : stores;

  for (const [storeCode, config] of Object.entries(storesToFetch)) {
    if (!config) continue;
    const allOrders = [];
    for (let page = 1; page <= 999; page++) {
      try {
        const orders = await wcApiRequest(storeCode, 'orders', {
          per_page: 100, status: 'processing,completed', orderby: 'date', order: 'desc', page
        });
        if (!Array.isArray(orders) || orders.length === 0 || orders.error) break;
        allOrders.push(...orders);
        if (orders.length < 100) break;
      } catch { break; }
    }

    // Group by email
    const customerOrders = {};
    for (const order of allOrders) {
      const email = (order.billing?.email || '').toLowerCase();
      if (!email) continue;
      if (!customerOrders[email]) customerOrders[email] = { orders: [], billing: order.billing, firstOrder: order };
      customerOrders[email].orders.push(order);
    }

    for (const [email, data] of Object.entries(customerOrders)) {
      if (data.orders.length !== 1) continue;
      const order = data.firstOrder;
      const orderDate = order.date_created ? new Date(order.date_created).getTime() : 0;
      const daysSince = orderDate ? (Date.now() - orderDate) / 86400000 : 0;
      if (daysSince < minDaysFromPurchase) continue;

      const customerId = `${storeCode}_buyer_${require('crypto').createHash('md5').update(email).digest('hex')}`;
      const savedData = callData[customerId] || {};
      if (savedData.callStatus === 'converted') continue;

      const orderItems = (order.line_items || []).map(item => ({
        name: item.name || 'Unknown product', quantity: parseInt(item.quantity) || 1, total: parseFloat(item.total) || 0
      }));

      const billing = data.billing || {};
      allBuyers.push({
        id: customerId, storeCode, storeName: config.name, storeFlag: config.flag,
        orderId: order.id || null,
        customerName: `${billing.first_name || ''} ${billing.last_name || ''}`.trim() || 'Unknown',
        email, phone: billing.phone || '',
        location: `${billing.city || ''}, ${billing.country || ''}`.replace(/^,\s*|,\s*$/g, ''),
        totalSpent: parseFloat(order.total) || 0, currency: order.currency || storeCurrencies[storeCode] || 'EUR',
        registeredAt: order.date_created || '', orderStatus: order.status || '',
        callStatus: savedData.callStatus || 'not_called', notes: savedData.notes || '',
        converted: false, orderItems
      });
    }
  }

  allBuyers.sort((a, b) => new Date(b.registeredAt || '1970-01-01') - new Date(a.registeredAt || '1970-01-01'));
  setCache(cacheKey, allBuyers);
  return allBuyers;
}

// ========== FETCH PENDING ORDERS ==========
async function fetchPendingOrders() {
  const cached = getCache('pending_orders', 300);
  if (cached) {
    const callData = loadCallData();
    for (const order of cached) {
      const saved = callData[order.id] || {};
      order.callStatus = saved.callStatus || 'not_called';
      order.notes = saved.notes || '';
    }
    return cached;
  }

  const callData = loadCallData();
  const allOrders = [];

  const promises = Object.entries(stores).map(async ([storeCode, config]) => {
    try {
      const orders = await wcApiRequest(storeCode, 'orders', {
        status: 'pending,cancelled,failed,on-hold', per_page: 50, orderby: 'date', order: 'desc'
      });
      if (!Array.isArray(orders)) return;
      for (const order of orders) {
        const orderId = `${storeCode}_order_${order.id || 'unknown'}`;
        const savedData = callData[orderId] || {};
        const billing = order.billing || {};
        allOrders.push({
          id: orderId, storeCode, storeName: config.name, storeFlag: config.flag,
          orderId: order.id || null,
          customerName: `${billing.first_name || ''} ${billing.last_name || ''}`.trim() || 'Unknown',
          email: billing.email || '', phone: billing.phone || '',
          location: `${billing.city || ''}, ${billing.country || ''}`.replace(/^,\s*|,\s*$/g, ''),
          orderStatus: order.status || '', orderTotal: parseFloat(order.total) || 0,
          currency: order.currency || 'EUR', createdAt: order.date_created || '',
          items: (order.line_items || []).map(i => ({ name: i.name, quantity: i.quantity, price: i.total })),
          callStatus: savedData.callStatus || 'not_called', notes: savedData.notes || ''
        });
      }
    } catch (err) { console.error(`Error fetching pending orders from ${storeCode}:`, err.message); }
  });

  await Promise.all(promises);
  allOrders.sort((a, b) => new Date(b.createdAt || '1970-01-01') - new Date(a.createdAt || '1970-01-01'));
  setCache('pending_orders', allOrders);
  return allOrders;
}

// ========== CREATE ORDER FROM CART ==========
async function createOrderFromCart(input) {
  const cartId = input.cartId || '';
  const agentName = input.agent || 'Call Center';
  const customerData = input.customer || {};
  const items = input.items || [];
  const freeShipping = input.freeShipping || false;

  const carts = await fetchAbandonedCarts();
  const cart = carts.find(c => c.id === cartId);
  if (!cart) return { error: 'Cart not found' };

  const storeCode = cart.storeCode;
  const config = stores[storeCode];
  if (!config) return { error: 'Invalid store' };

  let lineItems = [];
  if (items.length > 0) {
    lineItems = items.map(item => {
      const li = { product_id: parseInt(item.productId), quantity: parseInt(item.quantity) || 1 };
      if (item.variationId) li.variation_id = parseInt(item.variationId);
      if (item.price !== undefined) {
        li.subtotal = String(item.price * (item.quantity || 1));
        li.total = String(item.price * (item.quantity || 1));
      }
      return li;
    });
  } else {
    lineItems = (cart.cartContents || []).map(item => {
      const li = { product_id: item.productId, quantity: item.quantity };
      if (item.variationId) li.variation_id = item.variationId;
      return li;
    });
  }

  if (lineItems.length === 0) return { error: 'No items in order' };

  const firstName = customerData.firstName || cart.firstName || '';
  const lastName = customerData.lastName || cart.lastName || '';
  const email = customerData.email || cart.email || '';
  const phone = customerData.phone || cart.phone || '';
  const address = customerData.address || cart.address || '';
  const city = customerData.city || cart.city || '';
  const postcode = customerData.postcode || cart.postcode || '';
  const countryCode = storeCountryCodes[storeCode] || storeCode.toUpperCase();

  const orderData = {
    payment_method: 'cod', payment_method_title: 'Cash on Delivery',
    set_paid: false, status: 'processing',
    billing: { first_name: firstName, last_name: lastName, email, phone, address_1: address, city, postcode, country: countryCode },
    shipping: { first_name: firstName, last_name: lastName, address_1: address, city, postcode, country: countryCode },
    line_items: lineItems,
    meta_data: [
      { key: '_call_center', value: 'yes' }, { key: '_call_center_agent', value: agentName },
      { key: '_call_center_date', value: new Date().toISOString() },
      { key: '_abandoned_cart_id', value: String(cart.cartDbId) },
      { key: '_free_shipping', value: freeShipping ? 'yes' : 'no' }
    ],
    customer_note: `Order created via Call Center by ${agentName}`
  };

  if (freeShipping) {
    orderData.shipping_lines = [{ method_id: 'free_shipping', method_title: 'Free Shipping (Call Center)', total: '0.00' }];
  }

  const result = await wcApiRequest(storeCode, 'orders', {}, 'POST', orderData);
  if (result.error) return result;
  if (!result.id) return { error: 'Failed to create order' };

  const callData = loadCallData();
  callData[cartId] = {
    callStatus: 'converted',
    notes: (callData[cartId]?.notes || '') + `\nOrder #${result.id} created by ${agentName}`,
    lastUpdated: new Date().toISOString(), orderId: result.id
  };
  saveCallData(callData);
  clearAllCache();

  return {
    success: true, orderId: result.id, orderNumber: result.number || result.id,
    orderTotal: result.total, orderStatus: result.status,
    storeUrl: `${config.url}/wp-admin/post.php?post=${result.id}&action=edit`
  };
}

// ========== SMS FUNCTIONS ==========
function addSmsToQueue(data) {
  const phone = data.phone || '';
  const storeCode = data.storeCode || 'hr';
  const message = data.message || '';

  const validation = validatePhoneForSms(phone, storeCode);
  if (!validation.valid) return { success: false, error: validation.error };
  if (!message.trim()) return { success: false, error: 'Sporočilo je prazno' };

  const queue = loadSmsQueue();
  const smsEntry = {
    id: `${Date.now()}_${Math.floor(Math.random() * 9000 + 1000)}`,
    date: new Date().toISOString(), recipient: validation.formatted, recipientOriginal: phone,
    customerName: data.customerName || '', storeCode, message,
    status: 'queued', cartId: data.cartId || null, addedBy: data.addedBy || 'system'
  };
  queue.push(smsEntry);
  saveSmsQueue(queue);
  return { success: true, id: smsEntry.id, status: 'queued', formattedPhone: validation.formatted };
}

async function sendQueuedSms(smsId, overridePhone = null) {
  const queue = loadSmsQueue();
  const smsIndex = queue.findIndex(s => s.id === smsId);
  if (smsIndex === -1) return { success: false, error: 'SMS not found in queue' };

  const sms = queue[smsIndex];
  if (sms.status !== 'queued') return { success: false, error: `SMS already processed (status: ${sms.status})` };

  const storeCode = sms.storeCode;
  const rawPhone = overridePhone || sms.recipient;

  const validation = validatePhoneForSms(rawPhone, storeCode);
  if (!validation.valid) {
    queue[smsIndex].status = 'failed';
    queue[smsIndex].error = validation.error;
    queue[smsIndex].sentAt = new Date().toISOString();
    saveSmsQueue(queue);
    return { success: false, error: validation.error };
  }

  const payload = {
    secret_key: metakocka.secret_key,
    company_id: String(metakocka.company_id),
    message_list: [{
      type: 'sms', eshop_sync_id: SMS_ESHOP_SYNC_ID,
      sender: 'Narocilo', to_number: validation.formatted, message: sms.message
    }]
  };

  try {
    const response = await axios.post(metakocka.api_url, payload, { timeout: 30000, headers: { 'Content-Type': 'application/json' } });
    const data = response.data;

    if (data.opr_code && data.opr_code !== '0') {
      queue[smsIndex].status = 'failed';
      queue[smsIndex].error = data.opr_desc || `MetaKocka error (code: ${data.opr_code})`;
      queue[smsIndex].sentAt = new Date().toISOString();
      queue[smsIndex].metakockaResponse = data;
      saveSmsQueue(queue);
      return { success: false, error: queue[smsIndex].error, metakockaResponse: data };
    }

    if (data.message_list?.[0]?.status === 'error') {
      queue[smsIndex].status = 'failed';
      queue[smsIndex].error = data.message_list[0].error_desc || 'Message delivery failed';
      queue[smsIndex].sentAt = new Date().toISOString();
      queue[smsIndex].metakockaResponse = data;
      saveSmsQueue(queue);
      return { success: false, error: queue[smsIndex].error, metakockaResponse: data };
    }

    queue[smsIndex].status = 'sent';
    queue[smsIndex].sentAt = new Date().toISOString();
    queue[smsIndex].metakockaResponse = data;
    saveSmsQueue(queue);
    return { success: true, message: `SMS uspešno poslan na ${sms.recipient}`, smsId, recipient: sms.recipient, metakockaResponse: data };
  } catch (error) {
    queue[smsIndex].status = 'failed';
    queue[smsIndex].error = `Connection error: ${error.message}`;
    queue[smsIndex].sentAt = new Date().toISOString();
    saveSmsQueue(queue);
    return { success: false, error: queue[smsIndex].error };
  }
}

async function sendDirectSms(data) {
  const phone = data.phone || '';
  const storeCode = data.storeCode || 'hr';
  const message = data.message || '';

  const validation = validatePhoneForSms(phone, storeCode);
  if (!validation.valid) return { success: false, error: validation.error };
  if (!message.trim()) return { success: false, error: 'Sporočilo je prazno' };

  const payload = {
    secret_key: metakocka.secret_key,
    company_id: String(metakocka.company_id),
    message_list: [{
      type: 'sms', eshop_sync_id: SMS_ESHOP_SYNC_ID,
      sender: 'Narocilo', to_number: validation.formatted, message
    }]
  };

  try {
    const response = await axios.post(metakocka.api_url, payload, { timeout: 30000, headers: { 'Content-Type': 'application/json' } });
    const responseData = response.data;

    if (responseData.opr_code && responseData.opr_code !== '0') {
      return { success: false, error: responseData.opr_desc || 'MetaKocka error', metakockaResponse: responseData };
    }
    if (responseData.message_list) {
      for (const msg of responseData.message_list) {
        if (msg.status === 'error') return { success: false, error: msg.error_desc || 'Napaka pri pošiljanju', metakockaResponse: responseData };
      }
    }

    // Log to SMS history
    const queue = loadSmsQueue();
    queue.push({
      id: `direct_${Date.now()}_${Math.floor(Math.random() * 9000 + 1000)}`,
      date: new Date().toISOString(), recipient: validation.formatted, recipientOriginal: phone,
      customerName: 'Manual Test', storeCode, message, status: 'sent',
      sentAt: new Date().toISOString(), addedBy: 'manual', metakockaResponse: responseData
    });
    saveSmsQueue(queue);

    return { success: true, message: `SMS uspešno poslan na ${validation.formatted}`, recipient: validation.formatted, metakockaResponse: responseData };
  } catch (error) {
    return { success: false, error: `Connection error: ${error.message}` };
  }
}

// ========== CALL LOGS ==========
function addCallLog(data) {
  const logs = loadCallLogs();
  const logEntry = {
    id: `call_${Date.now()}_${Math.floor(Math.random() * 9000 + 1000)}`,
    customerId: data.customerId || '', storeCode: data.storeCode || '',
    status: data.status || 'not_called', notes: data.notes || '',
    duration: data.duration || null, callbackAt: data.callbackAt || null,
    agentId: data.agentId || 'unknown', createdAt: new Date().toISOString()
  };
  logs.push(logEntry);
  saveCallLogs(logs);

  // Update customer call status
  const callData = loadCallData();
  callData[data.customerId] = {
    callStatus: data.status, notes: data.notes || '',
    lastUpdated: new Date().toISOString(), orderId: callData[data.customerId]?.orderId || null
  };
  saveCallData(callData);

  return { success: true, id: logEntry.id, log: logEntry };
}

function getFollowUps(agentId = null, includeAll = false) {
  const logs = loadCallLogs();
  const today = new Date().toISOString().slice(0, 10);
  const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);

  const followups = logs.filter(log => {
    if (!log.callbackAt) return false;
    if (log.status !== 'callback' && log.status !== 'completed') return false;
    if (agentId && !includeAll && log.agentId !== agentId) return false;
    const callbackDate = new Date(log.callbackAt).toISOString().slice(0, 10);
    if (log.status === 'completed') {
      const completedDate = new Date(log.completedAt || log.callbackAt).toISOString().slice(0, 10);
      return completedDate >= new Date(Date.now() - 7 * 86400000).toISOString().slice(0, 10);
    }
    return callbackDate >= today;
  });

  followups.sort((a, b) => new Date(a.callbackAt) - new Date(b.callbackAt));

  return followups.map(f => {
    const callbackTime = new Date(f.callbackAt).getTime();
    const callbackDate = new Date(f.callbackAt).toISOString().slice(0, 10);
    return {
      ...f,
      isDue: callbackTime <= Date.now(),
      isToday: callbackDate === today,
      isTomorrow: callbackDate === tomorrow
    };
  });
}

function getCallStats(filters = {}) {
  let logs = loadCallLogs();
  if (filters.dateFrom) logs = logs.filter(l => l.createdAt >= filters.dateFrom);
  if (filters.dateTo) logs = logs.filter(l => l.createdAt <= filters.dateTo + 'T23:59:59');
  if (filters.storeCode) logs = logs.filter(l => l.storeCode === filters.storeCode);
  if (filters.agentId) logs = logs.filter(l => l.agentId === filters.agentId);

  const statusCounts = {};
  const agentStats = {};
  const hourlyStats = new Array(24).fill(0);
  const dailyStats = {};

  for (let i = 29; i >= 0; i--) {
    const date = new Date(Date.now() - i * 86400000).toISOString().slice(0, 10);
    dailyStats[date] = 0;
  }

  for (const log of logs) {
    statusCounts[log.status] = (statusCounts[log.status] || 0) + 1;
    if (!agentStats[log.agentId]) agentStats[log.agentId] = { calls: 0, converted: 0 };
    agentStats[log.agentId].calls++;
    if (log.status === 'converted') agentStats[log.agentId].converted++;
    hourlyStats[new Date(log.createdAt).getHours()]++;
    const date = new Date(log.createdAt).toISOString().slice(0, 10);
    if (dailyStats[date] !== undefined) dailyStats[date]++;
  }

  return {
    totalCalls: logs.length, statusCounts, agentStats, hourlyStats, dailyStats,
    conversionRate: logs.length > 0 ? Math.round((statusCounts.converted || 0) / logs.length * 1000) / 10 : 0
  };
}

// ========== SMS AUTOMATION ==========
async function runSmsAutomations() {
  const automations = readJson(automationsFile, []);
  const enabled = Array.isArray(automations) ? automations.filter(a => a.enabled) : [];
  if (enabled.length === 0) return { success: true, message: 'No enabled automations', queued: 0 };

  const queuedCarts = readJson(queuedCartsFile, {});
  const templatesData = readJson(smsTemplatesFile, {});
  const templates = templatesData.templates || templatesData;
  let totalQueued = 0;
  const results = {};

  for (const automation of enabled) {
    const autoId = automation.id;
    const store = automation.store;
    const templateId = automation.template;
    const delayHours = parseInt(automation.delay_hours) || 2;
    const maxDays = automation.max_days || 7;

    if (!queuedCarts[autoId]) queuedCarts[autoId] = [];

    if (automation.type === 'abandoned_cart') {
      const carts = await fetchAbandonedCarts();
      const storeCarts = carts.filter(c => c.storeCode === store);
      let queuedThisRun = 0;

      for (const cart of storeCarts) {
        const cartId = cart.cartDbId || cart.id;
        if (!cartId || queuedCarts[autoId].includes(cartId)) continue;
        if (cart.orderId) continue;

        const abandonedAt = cart.abandonedAt ? new Date(cart.abandonedAt).getTime() : 0;
        if (!abandonedAt) continue;
        if (Date.now() < abandonedAt + delayHours * 3600000) continue;
        if ((Date.now() - abandonedAt) > maxDays * 86400000) continue;
        if (!cart.phone) continue;

        // Get template
        let templateType = templateId;
        if (templateId.endsWith('_' + store)) templateType = templateId.slice(0, -(store.length + 1));

        let message = '';
        if (templates[templateType]?.[store]) message = templates[templateType][store].message || '';
        else if (templates[automation.type]?.[store]) message = templates[automation.type][store].message || '';
        if (!message) continue;

        const firstName = cart.firstName || '';
        const productName = cart.cartContents?.[0]?.name || 'proizvod';
        const checkoutLink = `https://noriks.com/${store}/checkout/?source=callboss`;
        const checkoutLinkCoupon = `https://noriks.com/${store}/checkout/?coupon=SMS20&source=callboss`;
        const shopLink = `https://noriks.com/${store}/?source=callboss`;

        message = message.replace(/{ime}/g, firstName || 'Kupac')
          .replace(/{produkt}/g, productName).replace(/{link}/g, checkoutLink)
          .replace(/{link_coupon}/g, checkoutLinkCoupon).replace(/{shop_link}/g, shopLink)
          .replace(/{cena}/g, (cart.cartValue || 0).toFixed(2));

        const result = addSmsToQueue({ phone: cart.phone, storeCode: store, message, customerName: cart.customerName || '', cartId, addedBy: `automation:${autoId}` });
        if (result.success) { queuedCarts[autoId].push(cartId); queuedThisRun++; totalQueued++; }
      }

      results[autoId] = { name: automation.name, queued: queuedThisRun };
    }
  }

  // Update queued_count
  const allAutomations = readJson(automationsFile, []);
  if (Array.isArray(allAutomations)) {
    for (const a of allAutomations) {
      if (queuedCarts[a.id]) a.queued_count = queuedCarts[a.id].length;
    }
    writeJson(automationsFile, allAutomations);
  }
  writeJson(queuedCartsFile, queuedCarts);

  return { success: true, totalQueued, results };
}

// ========== PAKETOMATI ==========
const PAKETOMAT_STATUSES = [
  "Can be picked up from GLS parcel locker", "Can be picked up from ParcelShop",
  "Placed in the (collection) parcel machine", "Parcel stored in temporary parcel machine",
  "Packet has been delivered to its destination branch and is waiting for pickup",
  "It's waiting to be collected at the Parcel Service Point", "Awaiting collection",
  "Accepted at an InPost branch", "Rerouted to parcel machine",
  "predana u paketomat", "Pošiljka predana u paketomat", "predana na pickup", "čeka preuzimanje",
  "Čaka na prevzem", "Waiting for pickup", "Ready for pickup", "Ready for collection",
  "Available for pickup", "Dostavljen na pošto", "Dostavljen v paketnik",
  "Dostavljen na prevzemno mesto", "Delivered to parcel locker", "Delivered to pickup point",
  "waiting at pickup", "at collection point"
];

async function buildPaketomatiCache() {
  const mkSearchUrl = 'https://main.metakocka.si/rest/eshop/v1/search';
  const mkGetDocUrl = 'https://main.metakocka.si/rest/eshop/v1/get_document';
  const secretKey = metakocka.secret_key;
  const companyId = String(metakocka.company_id);

  const allShippedOrders = [];
  for (let offset = 0; offset < 500; offset += 100) {
    try {
      const resp = await axios.post(mkSearchUrl, {
        secret_key: secretKey, company_id: companyId, doc_type: 'sales_order',
        result_type: 'doc', limit: 100, offset, order_direction: 'desc'
      }, { timeout: 30000 });
      const orders = resp.data?.result || [];
      if (orders.length === 0) break;
      allShippedOrders.push(...orders.filter(o => o.status_desc === 'shipped'));
      if (orders.length < 100) break;
    } catch { break; }
  }

  const statusData = loadPaketomatStatus();
  const paketomatOrders = [];

  // Process in batches
  for (let i = 0; i < allShippedOrders.length; i += 20) {
    const batch = allShippedOrders.slice(i, i + 20);
    const docPromises = batch.map(async (order) => {
      const mkId = order.mk_id;
      if (!mkId) return null;
      try {
        const resp = await axios.post(mkGetDocUrl, {
          secret_key: secretKey, company_id: companyId, doc_type: 'sales_order',
          doc_id: mkId, return_delivery_service_events: 'true', show_tracking_url: 'true'
        }, { timeout: 15000 });
        return { order, docData: resp.data };
      } catch { return null; }
    });

    const results = await Promise.all(docPromises);
    for (const result of results) {
      if (!result) continue;
      const { order, docData } = result;
      let events = docData.delivery_service_events || [];
      if (events.event_status) events = [events];
      if (!Array.isArray(events) || events.length === 0) continue;

      const lastEvent = events[0] || {};
      const lastEventStatus = lastEvent.event_status || '';
      const isPaketomat = PAKETOMAT_STATUSES.some(s => lastEventStatus.toLowerCase().includes(s.toLowerCase()));
      if (!isPaketomat) continue;

      const fullOrder = { ...order, ...docData };
      const orderId = `mk_${fullOrder.count_code || fullOrder.mk_id || Date.now()}`;
      const partner = fullOrder.partner || {};
      const partnerContact = partner.partner_contact || {};

      let trackingCode = '', trackingLink = '';
      for (const col of fullOrder.extra_column || []) {
        const n = (col.name || '').toLowerCase();
        if (n === 'tracking_number') trackingCode = col.value || '';
        if (['tracking_link', 'tracking_url', 'sledilna_povezava'].includes(n)) trackingLink = col.value || '';
      }

      // Determine store code
      const eshopName = (fullOrder.eshop_name || '').toLowerCase();
      const country = partner.country || '';
      let storeCode = 'hr';
      const countryMap = { Slovakia: 'sk', 'Czech Republic': 'cz', Poland: 'pl', Croatia: 'hr', Hungary: 'hu', Greece: 'gr', Italy: 'it', Slovenia: 'si' };
      const match = eshopName.match(/(sk|cz|pl|hr|hu|gr|it|si)/i);
      if (match) storeCode = match[1].toLowerCase();
      else { for (const [name, code] of Object.entries(countryMap)) { if (country.toLowerCase().includes(name.toLowerCase())) { storeCode = code; break; } } }

      const items = (fullOrder.product_list || []).filter(p => {
        const n = (p.name || '').toLowerCase();
        return !['pošta', 'doručení', 'shipping', 'delivery', 'dostava', 'szállítás'].some(k => n.includes(k));
      }).map(p => ({ name: p.name, quantity: parseInt(p.amount) || 1, price: parseFloat(p.price_with_tax || p.price) || 0 }));

      paketomatOrders.push({
        id: orderId, orderNumber: fullOrder.count_code || '',
        customer: { name: partner.customer || partner.name || '', email: partnerContact.email || partner.email || '', phone: partnerContact.gsm || partnerContact.phone || partner.phone || '' },
        address: { street: partner.street || '', city: partner.place || partner.city || '', postcode: partner.post_number || '', country: partner.country || '' },
        deliveryService: fullOrder.delivery_type || '', trackingCode, trackingLink,
        paketomatLocation: lastEventStatus, lastDeliveryEvent: lastEventStatus,
        orderTotal: parseFloat(fullOrder.sum_all) || 0, currency: fullOrder.currency_code || 'EUR',
        createdAt: fullOrder.doc_date || '', shippedAt: fullOrder.shipped_date || '',
        status: statusData[orderId]?.status || 'not_called', notes: statusData[orderId]?.notes || '',
        storeCode, items
      });
    }
  }

  const cacheData = { generated_at: new Date().toISOString(), orders: paketomatOrders };
  writeJson(path.join(DATA_DIR, 'paketomati-cache.json'), cacheData);
  return cacheData;
}

function fetchPaketomatOrders(filter = 'all') {
  const cacheFile = path.join(DATA_DIR, 'paketomati-cache.json');
  if (fs.existsSync(cacheFile)) {
    const cacheData = readJson(cacheFile, { orders: [] });
    let orders = cacheData.orders || [];
    const statusData = loadPaketomatStatus();
    for (const order of orders) {
      const saved = statusData[order.id];
      if (saved) { order.status = saved.status || order.status; order.notes = saved.notes || order.notes; }
    }
    if (filter !== 'all' && filter !== 'debug') {
      orders = orders.filter(o => (o.status || 'not_called') === filter);
    }
    return orders;
  }
  return [];
}

// ========== POLL FOR NEW ITEMS ==========
function pollForNewItems(userId) {
  const lastSeen = loadLastSeen();
  const userLastSeen = lastSeen[userId] || { carts: [], paketomati: [] };
  // We don't fetch live data here to avoid blocking — just return based on cache
  return { newCarts: [], newPaketomati: [], totalCarts: 0, totalPaketomati: 0 };
}

// ========== API ROUTES ==========

// Abandoned carts
app.get('/api/abandoned-carts', async (req, res) => {
  try {
    let carts = await fetchAbandonedCarts();
    if (req.query.store) carts = carts.filter(c => c.storeCode === req.query.store);
    res.json(carts);
  } catch (e) { res.status(500).json({ error: e.message }); }
});

// Customer 360
app.get('/api/customer-360', async (req, res) => {
  const email = (req.query.email || '').toLowerCase().trim();
  if (!email) return res.json({ error: 'Email required' });

  const allOrders = [];
  let totalSpent = 0;
  for (const [storeCode, config] of Object.entries(stores)) {
    const orders = await wcApiRequest(storeCode, 'orders', { search: email, per_page: 50, status: 'processing,completed,on-hold' });
    if (Array.isArray(orders)) {
      for (const order of orders) {
        if ((order.billing?.email || '').toLowerCase() !== email) continue;
        allOrders.push({
          id: order.id, storeCode, storeFlag: config.flag, storeName: config.name,
          status: order.status, total: parseFloat(order.total), currency: order.currency || 'EUR',
          date: order.date_created,
          items: (order.line_items || []).map(i => ({ name: i.name, quantity: i.quantity, total: i.total }))
        });
        totalSpent += parseFloat(order.total);
      }
    }
  }
  allOrders.sort((a, b) => new Date(b.date) - new Date(a.date));
  res.json({ success: true, email, orders: allOrders, orderCount: allOrders.length, totalSpent });
});

// One-time buyers
app.get('/api/one-time-buyers', async (req, res) => {
  try { res.json(await fetchOneTimeBuyers(req.query.store || null)); }
  catch (e) { res.status(500).json({ error: e.message }); }
});

// Buyers cache (instant)
app.get('/api/buyers-cache', (req, res) => {
  const cacheFile = path.join(DATA_DIR, 'buyers-cache.json');
  if (fs.existsSync(cacheFile)) {
    const cacheData = readJson(cacheFile, {});
    let buyers = cacheData.buyers || [];
    const callData = loadCallData();
    for (const b of buyers) { const s = callData[b.id] || {}; b.callStatus = s.callStatus || 'not_called'; b.notes = s.notes || ''; }
    if (req.query.store) buyers = buyers.filter(b => b.storeCode === req.query.store);
    res.json({ success: true, buyers, cached: true, cache_age_seconds: Math.floor((Date.now() - (cacheData.generated_at || 0) * 1000) / 1000), generated_at: cacheData.generated_at });
  } else {
    res.json({ success: true, buyers: [], cached: false, message: 'Cache not available' });
  }
});

// Refresh buyers cache
app.get('/api/refresh-buyers-cache', async (req, res) => {
  try {
    const start = Date.now();
    const buyers = await fetchOneTimeBuyers(req.query.store || null);
    const elapsed = ((Date.now() - start) / 1000).toFixed(2);
    writeJson(path.join(DATA_DIR, 'buyers-cache.json'), {
      generated_at: Math.floor(Date.now() / 1000), generated_date: new Date().toISOString(),
      count: buyers.length, fetch_time_seconds: parseFloat(elapsed), buyers
    });
    res.json({ success: true, count: buyers.length, fetch_time_seconds: parseFloat(elapsed) });
  } catch (e) { res.status(500).json({ error: e.message }); }
});

// Pending orders
app.get('/api/pending-orders', async (req, res) => {
  try {
    let orders = await fetchPendingOrders();
    if (req.query.store) orders = orders.filter(o => o.storeCode === req.query.store);
    res.json(orders);
  } catch (e) { res.status(500).json({ error: e.message }); }
});

// Stores
app.get('/api/stores', (req, res) => {
  res.json(Object.entries(stores).map(([code, c]) => ({ code, name: c.name, flag: c.flag })));
});

// Create order
app.post('/api/create-order', async (req, res) => {
  if (!req.body.cartId) return res.status(400).json({ error: 'Missing cartId' });
  const result = await createOrderFromCart(req.body);
  if (result.error) return res.status(400).json(result);
  res.json(result);
});

// Update status
app.post('/api/update-status', (req, res) => {
  const { id, callStatus, notes } = req.body;
  if (!id) return res.status(400).json({ error: 'Missing ID' });
  const callData = loadCallData();
  callData[id] = {
    callStatus: callStatus || 'not_called', notes: notes !== undefined ? notes : '',
    lastUpdated: new Date().toISOString(), orderId: callData[id]?.orderId || null
  };
  saveCallData(callData);
  res.json({ success: true });
});

// SMS Queue
app.get('/api/sms-queue', (req, res) => {
  let queue = loadSmsQueue();
  if (req.query.status) queue = queue.filter(s => s.status === req.query.status);
  if (req.query.storeCode) queue = queue.filter(s => s.storeCode === req.query.storeCode);
  queue.sort((a, b) => new Date(b.date) - new Date(a.date));
  res.json(queue);
});

app.post('/api/sms-add', (req, res) => { res.json(addSmsToQueue(req.body)); });

app.post('/api/sms-remove', (req, res) => {
  const smsId = req.body.id || '';
  let queue = loadSmsQueue();
  const removed = queue.find(s => s.id === smsId);
  queue = queue.filter(s => s.id !== smsId);
  saveSmsQueue(queue);

  if (removed?.cartId) {
    const qc = readJson(queuedCartsFile, {});
    for (const autoId of Object.keys(qc)) {
      qc[autoId] = (qc[autoId] || []).filter(c => c !== removed.cartId && c !== removed.cartId.replace(/^[a-z]+_/, ''));
    }
    writeJson(queuedCartsFile, qc);
  }
  res.json({ success: true });
});

// SMS Settings
app.get('/api/sms-settings', (req, res) => { res.json(loadSmsSettings()); });
app.post('/api/sms-settings', (req, res) => { writeJson(smsSettingsFile, req.body); res.json({ success: true }); });

// Buyers settings
app.get('/api/buyers-settings', (req, res) => {
  const settings = readJson(buyersSettingsFile, { minDaysFromPurchase: 10 });
  res.json({ success: true, settings });
});

app.post('/api/buyers-settings-save', (req, res) => {
  const settings = req.body.settings || { minDaysFromPurchase: 10 };
  writeJson(buyersSettingsFile, settings);
  res.json({ success: true, saved: settings });
});

// SMS Test Connection
app.post('/api/sms-test-connection', async (req, res) => {
  const storeCode = req.body.storeCode;
  if (!storeCode || !stores[storeCode]) return res.status(400).json({ error: 'Invalid store code' });
  try {
    const resp = await axios.post(metakocka.api_url, {
      secret_key: metakocka.secret_key, company_id: metakocka.company_id,
      eshop_sync_id: SMS_ESHOP_SYNC_ID, test_connection: true
    }, { timeout: 15000 });
    res.json({ success: true, message: 'Connection OK' });
  } catch (e) {
    res.json({ success: false, error: `Connection error: ${e.message}` });
  }
});

// SMS Send
app.post('/api/sms-send', async (req, res) => {
  const smsId = req.body.id || '';
  const overridePhone = req.body.phone || null;
  if (!smsId) return res.status(400).json({ error: 'Missing SMS ID' });
  res.json(await sendQueuedSms(smsId, overridePhone));
});

// SMS Send Direct
app.post('/api/sms-send-direct', async (req, res) => { res.json(await sendDirectSms(req.body)); });

// Search Products (local cache)
app.get('/api/search-products', async (req, res) => {
  const storeCode = req.query.store;
  const query = (req.query.q || '').toLowerCase().trim();
  if (!storeCode || !stores[storeCode]) return res.status(400).json({ error: 'Invalid store' });
  if (query.length < 2) return res.json([]);

  const cacheFile = path.join(DATA_DIR, `products-cache-${storeCode}.json`);
  if (fs.existsSync(cacheFile)) {
    const data = readJson(cacheFile, {});
    const products = data.products || [];
    const results = products.filter(p =>
      (p.name || '').toLowerCase().includes(query) || (p.sku || '').toLowerCase().includes(query)
    ).slice(0, 20);
    results.sort((a, b) => {
      const asku = (a.sku || '').toLowerCase().includes(query);
      const bsku = (b.sku || '').toLowerCase().includes(query);
      return asku === bsku ? 0 : asku ? -1 : 1;
    });
    return res.json(results);
  }

  // Fallback: search via WooCommerce API
  const [byName, bySku] = await Promise.all([
    wcApiRequest(storeCode, 'products', { search: query, per_page: 20, status: 'publish' }),
    wcApiRequest(storeCode, 'products', { sku: query, per_page: 10, status: 'publish' })
  ]);
  const seen = new Set();
  const results = [];
  for (const batch of [bySku, byName]) {
    if (Array.isArray(batch)) {
      for (const p of batch) {
        if (seen.has(p.id)) continue;
        seen.add(p.id);
        const pd = {
          id: p.id, name: p.name, sku: p.sku || '', price: parseFloat(p.price) || 0,
          regularPrice: parseFloat(p.regular_price) || 0, image: p.images?.[0]?.src || null,
          type: p.type || 'simple', variations: []
        };
        if (p.type === 'variable' && p.variations?.length) {
          const vars = await wcApiRequest(storeCode, `products/${p.id}/variations`, { per_page: 100 });
          if (Array.isArray(vars)) {
            pd.variations = vars.map(v => ({
              id: v.id, name: (v.attributes || []).map(a => a.option).filter(Boolean).join(' / ') || `Var #${v.id}`,
              price: parseFloat(v.price) || pd.price, sku: v.sku || '',
              inStock: (v.stock_status || 'instock') === 'instock'
            }));
          }
        }
        results.push(pd);
      }
    }
  }
  res.json(results);
});

// Refresh products cache for all stores
app.get('/api/refresh-products-cache-all', async (req, res) => {
  const results = {};
  for (const [code, config] of Object.entries(stores)) {
    const start = Date.now();
    const allProducts = [];
    for (let page = 1; page <= 20; page++) {
      const products = await wcApiRequest(code, 'products', { per_page: 100, page, status: 'publish' });
      if (!Array.isArray(products) || products.length === 0) break;
      for (const p of products) {
        const pd = { id: p.id, name: p.name, sku: p.sku || '', price: parseFloat(p.price) || 0, regularPrice: parseFloat(p.regular_price) || 0, image: p.images?.[0]?.src || null, type: p.type || 'simple', variations: [] };
        if (p.type === 'variable' && p.variations?.length) {
          const vars = await wcApiRequest(code, `products/${p.id}/variations`, { per_page: 100 });
          if (Array.isArray(vars)) pd.variations = vars.map(v => ({ id: v.id, name: (v.attributes || []).map(a => a.option).filter(Boolean).join(' / ') || `Var #${v.id}`, price: parseFloat(v.price) || pd.price, sku: v.sku || '', inStock: (v.stock_status || 'instock') === 'instock' }));
        }
        allProducts.push(pd);
      }
      if (products.length < 100) break;
    }
    writeJson(path.join(DATA_DIR, `products-cache-${code}.json`), { generated_at: Math.floor(Date.now() / 1000), store: code, count: allProducts.length, products: allProducts });
    results[code] = { count: allProducts.length, time: ((Date.now() - start) / 1000).toFixed(2) };
  }
  res.json({ success: true, stores: results });
});

// Refresh products cache for single store
app.get('/api/refresh-products-cache', async (req, res) => {
  const storeCode = req.query.store;
  if (!storeCode || !stores[storeCode]) return res.status(400).json({ error: 'Invalid store' });
  const start = Date.now();
  const allProducts = [];
  for (let page = 1; page <= 20; page++) {
    const products = await wcApiRequest(storeCode, 'products', { per_page: 100, page, status: 'publish' });
    if (!Array.isArray(products) || products.length === 0) break;
    for (const p of products) {
      const pd = { id: p.id, name: p.name, sku: p.sku || '', price: parseFloat(p.price) || 0, regularPrice: parseFloat(p.regular_price) || 0, image: p.images?.[0]?.src || null, type: p.type || 'simple', variations: [] };
      if (p.type === 'variable' && p.variations?.length) {
        const vars = await wcApiRequest(storeCode, `products/${p.id}/variations`, { per_page: 100 });
        if (Array.isArray(vars)) pd.variations = vars.map(v => ({ id: v.id, name: (v.attributes || []).map(a => a.option).filter(Boolean).join(' / ') || `Var #${v.id}`, price: parseFloat(v.price) || pd.price, sku: v.sku || '', inStock: (v.stock_status || 'instock') === 'instock' }));
      }
      allProducts.push(pd);
    }
    if (products.length < 100) break;
  }
  writeJson(path.join(DATA_DIR, `products-cache-${storeCode}.json`), { generated_at: Math.floor(Date.now() / 1000), store: storeCode, count: allProducts.length, products: allProducts });
  res.json({ success: true, store: storeCode, count: allProducts.length, fetch_time_seconds: ((Date.now() - start) / 1000).toFixed(2) });
});

// SMS Templates
app.get('/api/sms-templates', (req, res) => {
  const store = req.query.store;
  const data = readJson(smsTemplatesFile, {});
  const templates = data.templates || data;
  if (store) {
    const result = [];
    for (const [typeKey, stores] of Object.entries(templates)) {
      if (stores[store]) result.push({ id: `${typeKey}_${store}`, type: typeKey, name: stores[store].name, message: stores[store].message });
    }
    return res.json(result);
  }
  res.json(templates);
});

app.get('/api/all-sms-templates', (req, res) => {
  const data = readJson(smsTemplatesFile, {});
  const templates = data.templates || data;
  const result = [];
  for (const [typeKey, storeData] of Object.entries(templates)) {
    const messages = {};
    let name = '';
    let category = 'custom';
    if (typeKey.includes('abandoned')) category = 'abandoned';
    else if (typeKey.includes('winback')) category = 'winback';
    for (const [sc, sd] of Object.entries(storeData)) {
      if (sc === '_meta') { if (sd.category) category = sd.category; if (sd.name) name = sd.name; continue; }
      if (typeof sd === 'object') { messages[sc] = sd.message || ''; if (!name && sd.name) name = sd.name; }
    }
    result.push({ id: typeKey, name: name || typeKey.replace(/_/g, ' '), category, messages });
  }
  res.json({ templates: result });
});

app.post('/api/save-sms-template', (req, res) => {
  const { id, name, category = 'custom', messages = {} } = req.body;
  if (!id || !name) return res.json({ error: 'ID and name required' });
  const data = readJson(smsTemplatesFile, {});
  if (!data.templates) data.templates = data;
  const templateData = { _meta: { name, category } };
  for (const [sc, msg] of Object.entries(messages)) { if (msg) templateData[sc] = { name, message: msg }; }
  data.templates[id] = templateData;
  writeJson(smsTemplatesFile, data);
  res.json({ success: true });
});

app.post('/api/delete-sms-template', (req, res) => {
  const id = req.body.id;
  if (!id) return res.json({ error: 'ID required' });
  const data = readJson(smsTemplatesFile, {});
  if (!data.templates) data.templates = data;
  if (data.templates[id]) { delete data.templates[id]; writeJson(smsTemplatesFile, data); }
  res.json({ success: true });
});

// SMS Automations
app.get('/api/sms-automations', (req, res) => { res.json(readJson(automationsFile, [])); });

app.post('/api/save-sms-automation', (req, res) => {
  const input = req.body;
  let automations = readJson(automationsFile, []);
  if (!Array.isArray(automations)) automations = [];
  if (!input.id) {
    input.id = 'auto_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
    input.created_at = new Date().toISOString();
    input.sent_count = 0;
    automations.push(input);
  } else {
    const idx = automations.findIndex(a => a.id === input.id);
    if (idx >= 0) {
      input.sent_count = automations[idx].sent_count || 0;
      input.created_at = automations[idx].created_at || new Date().toISOString();
      automations[idx] = input;
    } else automations.push(input);
  }
  writeJson(automationsFile, automations);
  res.json({ success: true, id: input.id });
});

app.post('/api/delete-sms-automation', (req, res) => {
  const id = req.body.id;
  if (!id) return res.json({ error: 'ID required' });
  let automations = readJson(automationsFile, []);
  automations = automations.filter(a => a.id !== id);
  writeJson(automationsFile, automations);
  res.json({ success: true });
});

app.post('/api/reset-automation-queue', (req, res) => {
  const autoId = req.body.automation_id;
  if (!autoId) return res.json({ error: 'automation_id required' });
  const qc = readJson(queuedCartsFile, {});
  const prev = (qc[autoId] || []).length;
  qc[autoId] = [];
  writeJson(queuedCartsFile, qc);
  let automations = readJson(automationsFile, []);
  for (const a of automations) { if (a.id === autoId) { a.queued_count = 0; break; } }
  writeJson(automationsFile, automations);
  res.json({ success: true, reset_count: prev });
});

app.get('/api/run-sms-automations', async (req, res) => { res.json(await runSmsAutomations()); });

// Email templates
app.get('/api/email-templates', (req, res) => { res.json(readJson(emailTemplatesFile, { templates: [] })); });
app.post('/api/email-templates-save', (req, res) => { writeJson(emailTemplatesFile, req.body); res.json({ success: true }); });

// Clear cache
app.get('/api/clear-cache', (req, res) => { clearAllCache(); res.json({ success: true }); });

// Warm cache
app.get('/api/warm-cache', async (req, res) => {
  await fetchAbandonedCarts();
  await fetchPendingOrders();
  await fetchOneTimeBuyers();
  res.json({ success: true, warmed: ['abandoned-carts', 'pending-orders', 'one-time-buyers'] });
});

app.get('/api/warm-buyers', async (req, res) => {
  const buyers = await fetchOneTimeBuyers();
  res.json({ success: true, count: buyers.length, time: new Date().toISOString() });
});

// Cron SMS automation
app.get('/api/cron-sms-automation', async (req, res) => {
  const result = await runSmsAutomations();
  res.json({ success: result.success, totalQueued: result.totalQueued, time: new Date().toISOString(), results: result.results });
});

// Cache status
app.get('/api/cache-status', (req, res) => {
  const crypto = require('crypto');
  const keys = { abandoned_carts_filtered: 300, pending_orders: 300, 'one_time_buyers_all_10': 1800 };
  const status = {};
  for (const [key, maxAge] of Object.entries(keys)) {
    const file = path.join(CACHE_DIR, crypto.createHash('md5').update(key).digest('hex') + '.json');
    if (fs.existsSync(file)) {
      const age = (Date.now() - fs.statSync(file).mtimeMs) / 1000;
      status[key] = { cached: true, age_seconds: Math.floor(age), valid: age < maxAge, expires_in: Math.max(0, Math.floor(maxAge - age)) };
    } else status[key] = { cached: false };
  }
  res.json(status);
});

// Login
app.post('/api/login', (req, res) => {
  const { username, password } = req.body;
  const agents = loadAgents();
  const user = agents.users.find(u => u.username === username && u.active !== false);
  if (user && user.password === password) {
    res.json({ success: true, user: { id: user.id, username: user.username, role: user.role, countries: user.countries } });
  } else {
    res.status(401).json({ success: false, error: 'Invalid credentials' });
  }
});

// Agents
app.get('/api/agents-list', (req, res) => {
  const agents = loadAgents();
  const safeUsers = agents.users.map(u => ({ id: u.id, username: u.username, role: u.role, countries: u.countries, createdAt: u.createdAt, active: u.active ?? true }));
  res.json({ users: safeUsers });
});

app.post('/api/agents-add', (req, res) => {
  const { username, password } = req.body;
  if (!username || !password) return res.status(400).json({ error: 'Username and password required' });
  const agents = loadAgents();
  if (agents.users.find(u => u.username === username)) return res.status(400).json({ error: 'Username already exists' });
  const newUser = { id: `agent_${Date.now()}`, username, password, role: req.body.role || 'agent', countries: req.body.countries || ['hr'], createdAt: new Date().toISOString(), active: true };
  agents.users.push(newUser);
  saveAgents(agents);
  res.json({ success: true, id: newUser.id });
});

app.post('/api/agents-update', (req, res) => {
  if (!req.body.id) return res.status(400).json({ error: 'Agent ID required' });
  const agents = loadAgents();
  const user = agents.users.find(u => u.id === req.body.id);
  if (!user) return res.status(404).json({ error: 'Agent not found' });
  if (req.body.username) user.username = req.body.username;
  if (req.body.password) user.password = req.body.password;
  if (req.body.role !== undefined) user.role = req.body.role;
  if (req.body.countries !== undefined) user.countries = req.body.countries;
  if (req.body.active !== undefined) user.active = req.body.active;
  saveAgents(agents);
  res.json({ success: true });
});

app.post('/api/agents-delete', (req, res) => {
  if (!req.body.id) return res.status(400).json({ error: 'Agent ID required' });
  const agents = loadAgents();
  const adminCount = agents.users.filter(u => u.role === 'admin').length;
  const target = agents.users.find(u => u.id === req.body.id);
  if (target?.role === 'admin' && adminCount <= 1) return res.status(400).json({ error: 'Cannot delete last admin' });
  agents.users = agents.users.filter(u => u.id !== req.body.id);
  saveAgents(agents);
  res.json({ success: true });
});

// Call Logs
app.get('/api/call-logs', (req, res) => {
  let logs = loadCallLogs();
  if (req.query.storeCode) logs = logs.filter(l => l.storeCode === req.query.storeCode);
  if (req.query.agentId) logs = logs.filter(l => l.agentId === req.query.agentId);
  logs.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
  res.json(logs);
});

app.post('/api/call-logs-add', (req, res) => {
  if (!req.body.customerId) return res.status(400).json({ error: 'Customer ID required' });
  res.json(addCallLog(req.body));
});

app.get('/api/call-logs-customer', (req, res) => {
  const customerId = req.query.customerId;
  if (!customerId) return res.status(400).json({ error: 'Customer ID required' });
  const logs = loadCallLogs().filter(l => l.customerId === customerId);
  logs.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
  res.json(logs);
});

app.get('/api/my-followups', (req, res) => {
  const agentId = req.query.agentId || null;
  const includeAll = req.query.all === 'true';
  res.json(getFollowUps(agentId, includeAll));
});

app.get('/api/debug-call-logs', (req, res) => {
  const logs = loadCallLogs();
  res.json({ count: logs.length, logs: logs.slice(-10) });
});

app.post('/api/complete-followup', (req, res) => {
  const id = req.body.id;
  if (!id) return res.json({ success: false, error: 'ID required' });
  const logs = loadCallLogs();
  const log = logs.find(l => l.id === id);
  if (log) { log.status = 'completed'; log.completed = true; log.completedAt = new Date().toISOString(); saveCallLogs(logs); res.json({ success: true }); }
  else res.json({ success: false, error: 'Follow-up not found' });
});

app.post('/api/delete-followup', (req, res) => {
  const id = req.body.id;
  if (!id) return res.json({ success: false, error: 'ID required' });
  let logs = loadCallLogs();
  const before = logs.length;
  logs = logs.filter(l => l.id !== id);
  if (logs.length < before) { saveCallLogs(logs); res.json({ success: true }); }
  else res.json({ success: false, error: 'Follow-up not found' });
});

app.get('/api/call-stats', (req, res) => {
  res.json(getCallStats({ storeCode: req.query.storeCode, agentId: req.query.agentId, dateFrom: req.query.dateFrom, dateTo: req.query.dateTo }));
});

// Paketomati
app.get('/api/paketomati', (req, res) => { res.json(fetchPaketomatOrders(req.query.filter || 'all')); });

app.get('/api/paketomati-debug', async (req, res) => {
  try {
    const resp = await axios.post('https://main.metakocka.si/rest/eshop/v1/search', {
      secret_key: metakocka.secret_key, company_id: String(metakocka.company_id),
      doc_type: 'sales_order', result_type: 'doc', limit: 100, order_direction: 'desc'
    }, { timeout: 30000 });
    const orders = resp.data?.result || [];
    const byStatus = {};
    orders.forEach(o => { const s = o.status_desc || 'unknown'; byStatus[s] = (byStatus[s] || 0) + 1; });
    res.json({ total_orders: orders.length, by_status: byStatus, shipped_count: orders.filter(o => o.status_desc === 'shipped').length });
  } catch (e) { res.status(500).json({ error: e.message }); }
});

app.get('/api/refresh-paketomati-cache', async (req, res) => {
  try {
    const result = await buildPaketomatiCache();
    res.json({ success: true, paketomatCount: (result.orders || []).length });
  } catch (e) { res.status(500).json({ error: e.message }); }
});

app.get('/api/mk-order-dump', async (req, res) => {
  let mkId = req.query.mk_id || '';
  const orderNum = req.query.order || '';
  if (!mkId && orderNum) {
    try {
      const resp = await axios.post('https://main.metakocka.si/rest/eshop/v1/search', {
        secret_key: metakocka.secret_key, company_id: String(metakocka.company_id),
        doc_type: 'sales_order', result_type: 'doc', limit: 50, order_direction: 'desc'
      }, { timeout: 30000 });
      const found = (resp.data?.result || []).find(o => (o.count_code || '').includes(orderNum));
      if (found) mkId = found.mk_id;
    } catch {}
  }
  if (!mkId) return res.json({ error: 'No mk_id found' });
  try {
    const resp = await axios.post('https://main.metakocka.si/rest/eshop/v1/get_document', {
      secret_key: metakocka.secret_key, company_id: String(metakocka.company_id),
      doc_type: 'sales_order', doc_id: mkId, return_delivery_service_events: 'true', show_tracking_url: 'true'
    }, { timeout: 15000 });
    res.json({ mk_id: mkId, order_number: resp.data?.count_code || 'unknown', FULL_ORDER_DATA: resp.data });
  } catch (e) { res.status(500).json({ error: e.message }); }
});

app.get('/api/paketomati-raw', async (req, res) => {
  try {
    const resp = await axios.post('https://main.metakocka.si/rest/eshop/v1/search', {
      secret_key: metakocka.secret_key, company_id: String(metakocka.company_id),
      doc_type: 'sales_order', result_type: 'doc', limit: 100, order_direction: 'desc'
    }, { timeout: 30000 });
    res.json({ orders: (resp.data?.result || []).slice(0, 10).map(o => ({ count_code: o.count_code, mk_id: o.mk_id, status: o.status_desc })) });
  } catch (e) { res.status(500).json({ error: e.message }); }
});

app.post('/api/paketomati-update', (req, res) => {
  const { id, status = 'not_called', notes = '' } = req.body;
  if (!id) return res.status(400).json({ error: 'Missing order ID' });
  const statusData = loadPaketomatStatus();
  statusData[id] = { status, notes, lastUpdated: new Date().toISOString() };
  savePaketomatStatus(statusData);
  res.json({ success: true });
});

// Notification settings
app.get('/api/notification-settings', (req, res) => { res.json(loadNotificationSettings()); });
app.post('/api/notification-settings', (req, res) => { saveNotificationSettings(req.body); res.json({ success: true }); });

// Poll new
app.get('/api/poll-new', (req, res) => { res.json(pollForNewItems(req.query.userId || 'default')); });

// Mark seen
app.post('/api/mark-seen', (req, res) => {
  const { userId = 'default', cartIds = [], paketomatIds = [] } = req.body;
  const lastSeen = loadLastSeen();
  if (!lastSeen[userId]) lastSeen[userId] = { carts: [], paketomati: [] };
  if (cartIds.length) lastSeen[userId].carts = [...new Set([...lastSeen[userId].carts, ...cartIds])].slice(-500);
  if (paketomatIds.length) lastSeen[userId].paketomati = [...new Set([...lastSeen[userId].paketomati, ...paketomatIds])].slice(-500);
  lastSeen[userId].lastCheck = new Date().toISOString();
  saveLastSeen(lastSeen);
  res.json({ success: true });
});

// Health
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', version: '7.0', timestamp: new Date().toISOString(),
    features: { enhanced_order_creation: true, sms_queue: true, sms_sending: true, metakocka_integration: true, call_logging: true, follow_ups: true, analytics: true, paketomati: true, realtime_notifications: true }
  });
});

// Page routes
app.get('/login', (req, res) => res.sendFile('login.html', { root: path.join(__dirname, 'public') }));
app.get('/report', (req, res) => res.sendFile('report.html', { root: path.join(__dirname, 'public') }));
app.get('/', (req, res) => res.sendFile('index.html', { root: path.join(__dirname, 'public') }));

app.listen(PORT, () => {
  console.log(`🎧 Noriks Call Center running on port ${PORT}`);
});
