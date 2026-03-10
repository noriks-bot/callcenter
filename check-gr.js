const axios = require("axios");
const sk = "ee759602-961d-4431-ac64-0725ae8d9665";
const ci = "6371";
const searchUrl = "https://main.metakocka.si/rest/eshop/v1/search";
const getDocUrl = "https://main.metakocka.si/rest/eshop/v1/get_document";

async function main() {
  const grOrders = [];
  for (let offset = 0; offset < 6000; offset += 100) {
    try {
      const r = await axios.post(searchUrl, {
        secret_key: sk, company_id: ci, doc_type: "sales_order",
        result_type: "doc", limit: 100, offset, order_direction: "desc"
      }, { timeout: 30000 });
      const orders = r.data?.result || [];
      if (!orders.length) break;
      for (const o of orders) {
        if (o.status_desc === "shipped" && /noriks/i.test(o.eshop_name || "") && /gr/i.test(o.eshop_name || "")) {
          grOrders.push(o);
        }
      }
      if (orders.length < 100) break;
      if (grOrders.length >= 15) break;
      if (offset % 1000 === 0) process.stderr.write(offset + ".. ");
    } catch { continue; }
  }
  
  console.log("Found", grOrders.length, "GR shipped orders");
  
  for (const o of grOrders.slice(0, 8)) {
    try {
      const r = await axios.post(getDocUrl, {
        secret_key: sk, company_id: ci, doc_type: "sales_order",
        doc_id: o.mk_id, return_delivery_service_events: "true", show_tracking_url: "true"
      }, { timeout: 15000 });
      const events = r.data.delivery_service_events;
      const tracking = (r.data.extra_column || []).find(c => (c.name||"").toLowerCase() === "tracking_number");
      console.log("\n---", o.count_code, "|", o.partner?.country || "?");
      console.log("Tracking:", tracking?.value || "none");
      console.log("Delivery:", r.data.delivery_type || "none");
      if (events) {
        const evArr = Array.isArray(events) ? events : [events];
        for (const e of evArr.slice(0, 5)) {
          console.log("  Event:", e.event_status || JSON.stringify(e));
        }
      } else {
        console.log("  No delivery events");
      }
    } catch (e) { console.log("  Error:", e.message); }
  }
}
main();
