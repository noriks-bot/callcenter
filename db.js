// SQLite persistence layer for CallBoss
// All data persists across restarts. Background worker refreshes every 5 min.
const Database = require('better-sqlite3');
const path = require('path');
const fs = require('fs');

const DATA_DIR = path.join(__dirname, 'data');
if (!fs.existsSync(DATA_DIR)) fs.mkdirSync(DATA_DIR, { recursive: true });

const DB_PATH = path.join(DATA_DIR, 'callboss.db');
const db = new Database(DB_PATH);

// WAL mode for concurrent reads during writes
db.pragma('journal_mode = WAL');
db.pragma('synchronous = NORMAL');

// Create tables
db.exec(`
  CREATE TABLE IF NOT EXISTS cache_data (
    key TEXT PRIMARY KEY,
    data TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );
`);

// Prepared statements
const upsertStmt = db.prepare(`
  INSERT INTO cache_data (key, data, updated_at) VALUES (?, ?, ?)
  ON CONFLICT(key) DO UPDATE SET data = excluded.data, updated_at = excluded.updated_at
`);

const selectStmt = db.prepare(`SELECT data, updated_at FROM cache_data WHERE key = ?`);

function dbWrite(key, items) {
  upsertStmt.run(key, JSON.stringify(items), new Date().toISOString());
}

function dbRead(key) {
  const row = selectStmt.get(key);
  if (!row) return null;
  return { data: JSON.parse(row.data), updatedAt: row.updated_at };
}

function dbReadData(key) {
  const result = dbRead(key);
  return result ? result.data : [];
}

function dbLastRefresh() {
  const row = db.prepare(`SELECT MAX(updated_at) as last FROM cache_data`).get();
  return row ? row.last : null;
}

module.exports = { db, dbWrite, dbRead, dbReadData, dbLastRefresh };
