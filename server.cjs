var __create = Object.create;
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __copyProps = (to, from, except, desc) => {
  if (from && typeof from === "object" || typeof from === "function") {
    for (let key of __getOwnPropNames(from))
      if (!__hasOwnProp.call(to, key) && key !== except)
        __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
  }
  return to;
};
var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(
  // If the importer is in node compatibility mode or this is not an ESM
  // file that has been converted to a CommonJS file using a Babel-
  // compatible transform (i.e. "__esModule" has not been set), then set
  // "default" to the CommonJS "module.exports" for node compatibility.
  isNodeMode || !mod || !mod.__esModule ? __defProp(target, "default", { value: mod, enumerable: true }) : target,
  mod
));

// server.ts
var import_express = __toESM(require("express"), 1);
var import_path = __toESM(require("path"), 1);
var import_vite = require("vite");
var import_googleapis = require("googleapis");
var dotenv = __toESM(require("dotenv"), 1);
dotenv.config();
var app = (0, import_express.default)();
var PORT = 3e3;
app.use(import_express.default.json());
function getSheetsClient() {
  const credentials = {
    client_email: process.env.GOOGLE_CLIENT_EMAIL,
    // Fix private key formatting from env vars
    private_key: process.env.GOOGLE_PRIVATE_KEY?.replace(/\\n/g, "\n")
  };
  if (!credentials.client_email || !credentials.private_key) {
    throw new Error("Google Sheets credentials not configured");
  }
  const auth = new import_googleapis.google.auth.GoogleAuth({
    credentials,
    scopes: ["https://www.googleapis.com/auth/spreadsheets"]
  });
  return import_googleapis.google.sheets({ version: "v4", auth });
}
var SHEET_ID = "1Xbp-E4kdwMC_SdgrVI9VHbD9VwlG5P078NcAbK4zr7g";
app.post("/api/submit-lead", async (req, res) => {
  try {
    const { fullName, email, mobile, course, consent } = req.body;
    if (!fullName || !email || !mobile || !course) {
      return res.status(400).json({ error: "Missing required fields" });
    }
    let sheets;
    try {
      sheets = getSheetsClient();
    } catch (e) {
      console.warn("Sheets API config missing:", e.message);
      return res.status(200).json({
        message: "Lead received (Not saved to Google Sheets because credentials are missing)",
        note: "Please configure GOOGLE_CLIENT_EMAIL and GOOGLE_PRIVATE_KEY in your environment."
      });
    }
    await sheets.spreadsheets.values.append({
      spreadsheetId: SHEET_ID,
      range: "Sheet1!A:F",
      // Adjust if sheet name is different
      valueInputOption: "USER_ENTERED",
      requestBody: {
        values: [
          [(/* @__PURE__ */ new Date()).toISOString(), fullName, email, mobile, course, consent ? "Yes" : "No"]
        ]
      }
    });
    res.status(200).json({ message: "Lead submitted successfully" });
  } catch (error) {
    console.error("Error submitting to Google Sheets:", error);
    res.status(500).json({ error: "Failed to submit lead", details: error.message });
  }
});
app.post("/api/whatsapp", async (req, res) => {
  try {
    const { name, email, message, targetNumber } = req.body;
    if (!name || !email || !message) {
      return res.status(400).json({ error: "Missing required fields" });
    }
    const accountSid = process.env.TWILIO_ACCOUNT_SID;
    const authToken = process.env.TWILIO_AUTH_TOKEN;
    const fromWhatsAppNumber = process.env.TWILIO_WHATSAPP_NUMBER;
    if (!accountSid || !authToken || !fromWhatsAppNumber) {
      console.warn("Twilio credentials missing. Simulating success in dev.");
      return res.status(200).json({
        message: "Message received (Not sent because Twilio credentials are missing)",
        note: "Configure TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, and TWILIO_WHATSAPP_NUMBER"
      });
    }
    const twilio = (await import("twilio")).default;
    const client = twilio(accountSid, authToken);
    const body = `*New Inquiry via Live Chat*

*Name:* ${name}
*Email:* ${email}
*Question:* ${message}`;
    const to = targetNumber ? targetNumber.startsWith("whatsapp:") ? targetNumber : `whatsapp:+${targetNumber.replace(/\D/g, "")}` : "whatsapp:+919354238891";
    await client.messages.create({
      body,
      from: fromWhatsAppNumber,
      to
    });
    res.status(200).json({ message: "WhatsApp message sent successfully" });
  } catch (error) {
    console.error("Error sending WhatsApp message:", error);
    res.status(500).json({ error: "Failed to send WhatsApp message", details: error.message });
  }
});
async function startServer() {
  if (process.env.NODE_ENV !== "production") {
    const vite = await (0, import_vite.createServer)({
      server: { middlewareMode: true },
      appType: "spa"
    });
    app.use(vite.middlewares);
  } else {
    const distPath = import_path.default.join(process.cwd(), "dist");
    app.use(import_express.default.static(distPath));
    app.get("*", (req, res) => {
      res.sendFile(import_path.default.join(distPath, "index.html"));
    });
  }
  app.listen(PORT, "0.0.0.0", () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}
startServer();
//# sourceMappingURL=server.cjs.map
