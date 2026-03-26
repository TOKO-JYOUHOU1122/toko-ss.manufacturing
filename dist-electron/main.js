import b from "electron";
import x from "node:path";
import A from "fs";
import L from "path";
import P from "os";
import R from "crypto";
var ue = {}, f = { exports: {} };
const U = "17.2.4", k = {
  version: U
}, D = A, g = L, Y = P, K = R, C = k, N = C.version, O = [
  "🔐 encrypt with Dotenvx: https://dotenvx.com",
  "🔐 prevent committing .env to code: https://dotenvx.com/precommit",
  "🔐 prevent building .env in docker: https://dotenvx.com/prebuild",
  "📡 add observability to secrets: https://dotenvx.com/ops",
  "👥 sync secrets across teammates & machines: https://dotenvx.com/ops",
  "🗂️ backup and recover secrets: https://dotenvx.com/ops",
  "✅ audit secrets and track compliance: https://dotenvx.com/ops",
  "🔄 add secrets lifecycle management: https://dotenvx.com/ops",
  "🔑 add access controls to secrets: https://dotenvx.com/ops",
  "🛠️  run anywhere with `dotenvx run -- yourcommand`",
  "⚙️  specify custom .env file path with { path: '/custom/path/.env' }",
  "⚙️  enable debug logging with { debug: true }",
  "⚙️  override existing env vars with { override: true }",
  "⚙️  suppress all logs with { quiet: true }",
  "⚙️  write to custom object with { processEnv: myObject }",
  "⚙️  load multiple .env files with { path: ['.env.local', '.env'] }"
];
function F() {
  return O[Math.floor(Math.random() * O.length)];
}
function p(e) {
  return typeof e == "string" ? !["false", "0", "no", "off", ""].includes(e.toLowerCase()) : !!e;
}
function B() {
  return process.stdout.isTTY;
}
function j(e) {
  return B() ? `\x1B[2m${e}\x1B[0m` : e;
}
const q = /(?:^|^)\s*(?:export\s+)?([\w.-]+)(?:\s*=\s*?|:\s+?)(\s*'(?:\\'|[^'])*'|\s*"(?:\\"|[^"])*"|\s*`(?:\\`|[^`])*`|[^#\r\n]+)?\s*(?:#.*)?(?:$|$)/mg;
function S(e) {
  const n = {};
  let r = e.toString();
  r = r.replace(/\r\n?/mg, `
`);
  let o;
  for (; (o = q.exec(r)) != null; ) {
    const c = o[1];
    let s = o[2] || "";
    s = s.trim();
    const t = s[0];
    s = s.replace(/^(['"`])([\s\S]*)\1$/mg, "$2"), t === '"' && (s = s.replace(/\\n/g, `
`), s = s.replace(/\\r/g, "\r")), n[c] = s;
  }
  return n;
}
function G(e) {
  e = e || {};
  const n = I(e);
  e.path = n;
  const r = i.configDotenv(e);
  if (!r.parsed) {
    const t = new Error(`MISSING_DATA: Cannot parse ${n} for an unknown reason`);
    throw t.code = "MISSING_DATA", t;
  }
  const o = $(e).split(","), c = o.length;
  let s;
  for (let t = 0; t < c; t++)
    try {
      const a = o[t].trim(), l = W(r, a);
      s = i.decrypt(l.ciphertext, l.key);
      break;
    } catch (a) {
      if (t + 1 >= c)
        throw a;
    }
  return i.parse(s);
}
function M(e) {
  console.error(`[dotenv@${N}][WARN] ${e}`);
}
function h(e) {
  console.log(`[dotenv@${N}][DEBUG] ${e}`);
}
function T(e) {
  console.log(`[dotenv@${N}] ${e}`);
}
function $(e) {
  return e && e.DOTENV_KEY && e.DOTENV_KEY.length > 0 ? e.DOTENV_KEY : process.env.DOTENV_KEY && process.env.DOTENV_KEY.length > 0 ? process.env.DOTENV_KEY : "";
}
function W(e, n) {
  let r;
  try {
    r = new URL(n);
  } catch (a) {
    if (a.code === "ERR_INVALID_URL") {
      const l = new Error("INVALID_DOTENV_KEY: Wrong format. Must be in valid uri format like dotenv://:key_1234@dotenvx.com/vault/.env.vault?environment=development");
      throw l.code = "INVALID_DOTENV_KEY", l;
    }
    throw a;
  }
  const o = r.password;
  if (!o) {
    const a = new Error("INVALID_DOTENV_KEY: Missing key part");
    throw a.code = "INVALID_DOTENV_KEY", a;
  }
  const c = r.searchParams.get("environment");
  if (!c) {
    const a = new Error("INVALID_DOTENV_KEY: Missing environment part");
    throw a.code = "INVALID_DOTENV_KEY", a;
  }
  const s = `DOTENV_VAULT_${c.toUpperCase()}`, t = e.parsed[s];
  if (!t) {
    const a = new Error(`NOT_FOUND_DOTENV_ENVIRONMENT: Cannot locate environment ${s} in your .env.vault file.`);
    throw a.code = "NOT_FOUND_DOTENV_ENVIRONMENT", a;
  }
  return { ciphertext: t, key: o };
}
function I(e) {
  let n = null;
  if (e && e.path && e.path.length > 0)
    if (Array.isArray(e.path))
      for (const r of e.path)
        D.existsSync(r) && (n = r.endsWith(".vault") ? r : `${r}.vault`);
    else
      n = e.path.endsWith(".vault") ? e.path : `${e.path}.vault`;
  else
    n = g.resolve(process.cwd(), ".env.vault");
  return D.existsSync(n) ? n : null;
}
function V(e) {
  return e[0] === "~" ? g.join(Y.homedir(), e.slice(1)) : e;
}
function Q(e) {
  const n = p(process.env.DOTENV_CONFIG_DEBUG || e && e.debug), r = p(process.env.DOTENV_CONFIG_QUIET || e && e.quiet);
  (n || !r) && T("Loading env from encrypted .env.vault");
  const o = i._parseVault(e);
  let c = process.env;
  return e && e.processEnv != null && (c = e.processEnv), i.populate(c, o, e), { parsed: o };
}
function J(e) {
  const n = g.resolve(process.cwd(), ".env");
  let r = "utf8", o = process.env;
  e && e.processEnv != null && (o = e.processEnv);
  let c = p(o.DOTENV_CONFIG_DEBUG || e && e.debug), s = p(o.DOTENV_CONFIG_QUIET || e && e.quiet);
  e && e.encoding ? r = e.encoding : c && h("No encoding is specified. UTF-8 is used by default");
  let t = [n];
  if (e && e.path)
    if (!Array.isArray(e.path))
      t = [V(e.path)];
    else {
      t = [];
      for (const u of e.path)
        t.push(V(u));
    }
  let a;
  const l = {};
  for (const u of t)
    try {
      const d = i.parse(D.readFileSync(u, { encoding: r }));
      i.populate(l, d, e);
    } catch (d) {
      c && h(`Failed to load ${u} ${d.message}`), a = d;
    }
  const m = i.populate(o, l, e);
  if (c = p(o.DOTENV_CONFIG_DEBUG || c), s = p(o.DOTENV_CONFIG_QUIET || s), c || !s) {
    const u = Object.keys(m).length, d = [];
    for (const w of t)
      try {
        const v = g.relative(process.cwd(), w);
        d.push(v);
      } catch (v) {
        c && h(`Failed to load ${w} ${v.message}`), a = v;
      }
    T(`injecting env (${u}) from ${d.join(",")} ${j(`-- tip: ${F()}`)}`);
  }
  return a ? { parsed: l, error: a } : { parsed: l };
}
function H(e) {
  if ($(e).length === 0)
    return i.configDotenv(e);
  const n = I(e);
  return n ? i._configVault(e) : (M(`You set DOTENV_KEY but you are missing a .env.vault file at ${n}. Did you forget to build it?`), i.configDotenv(e));
}
function z(e, n) {
  const r = Buffer.from(n.slice(-64), "hex");
  let o = Buffer.from(e, "base64");
  const c = o.subarray(0, 12), s = o.subarray(-16);
  o = o.subarray(12, -16);
  try {
    const t = K.createDecipheriv("aes-256-gcm", r, c);
    return t.setAuthTag(s), `${t.update(o)}${t.final()}`;
  } catch (t) {
    const a = t instanceof RangeError, l = t.message === "Invalid key length", m = t.message === "Unsupported state or unable to authenticate data";
    if (a || l) {
      const u = new Error("INVALID_DOTENV_KEY: It must be 64 characters long (or more)");
      throw u.code = "INVALID_DOTENV_KEY", u;
    } else if (m) {
      const u = new Error("DECRYPTION_FAILED: Please check your DOTENV_KEY");
      throw u.code = "DECRYPTION_FAILED", u;
    } else
      throw t;
  }
}
function X(e, n, r = {}) {
  const o = !!(r && r.debug), c = !!(r && r.override), s = {};
  if (typeof n != "object") {
    const t = new Error("OBJECT_REQUIRED: Please check the processEnv argument being passed to populate");
    throw t.code = "OBJECT_REQUIRED", t;
  }
  for (const t of Object.keys(n))
    Object.prototype.hasOwnProperty.call(e, t) ? (c === !0 && (e[t] = n[t], s[t] = n[t]), o && h(c === !0 ? `"${t}" is already defined and WAS overwritten` : `"${t}" is already defined and was NOT overwritten`)) : (e[t] = n[t], s[t] = n[t]);
  return s;
}
const i = {
  configDotenv: J,
  _configVault: Q,
  _parseVault: G,
  config: H,
  decrypt: z,
  parse: S,
  populate: X
};
f.exports.configDotenv = i.configDotenv;
f.exports._configVault = i._configVault;
f.exports._parseVault = i._parseVault;
f.exports.config = i.config;
f.exports.decrypt = i.decrypt;
f.exports.parse = i.parse;
f.exports.populate = i.populate;
f.exports = i;
var Z = f.exports;
const { app: E, BrowserWindow: ee, ipcMain: te, dialog: re } = b, _ = x;
Z.config();
let y = null;
function ne() {
  y = new ee({
    width: 1400,
    height: 900,
    webPreferences: {
      contextIsolation: !0,
      nodeIntegration: !1,
      preload: _.join(__dirname, "preload.cjs")
    }
  });
  let e = "http://127.0.0.1:8000";
  process.env.APP_URL && (e = process.env.APP_URL);
  try {
    const r = require("node:fs"), o = _.dirname(E.getPath("exe")), c = _.join(o, "config.json");
    if (r.existsSync(c)) {
      const s = JSON.parse(r.readFileSync(c, "utf8"));
      s.url && (e = s.url);
    }
  } catch (r) {
    console.error("Config load error:", r);
  }
  const n = process.env.NODE_ENV !== "production" ? process.env.APP_URL || "http://127.0.0.1:8000" : e;
  y.loadURL(n), process.env.NODE_ENV !== "production" && y.webContents.openDevTools();
}
E.whenReady().then(ne);
E.on("window-all-closed", () => E.quit());
te.handle("dialog:openFile", async (e, n = {}) => {
  const r = await re.showOpenDialog(n);
  return r.canceled || r.filePaths.length === 0 ? null : r;
});
export {
  ue as default
};
