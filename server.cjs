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

// src/data.ts
var programs = [
  {
    id: "mba-muj",
    title: "MBA",
    university: "Manipal University Jaipur",
    fullName: "Master of Business Administration",
    duration: "24 Months",
    fee: "INR 1,80,000",
    image: "https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "mba-smu",
    title: "MBA",
    university: "Sikkim Manipal University",
    fullName: "Master of Business Administration",
    duration: "24 Months",
    fee: "INR 1,20,000",
    image: "https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "mca-muj",
    title: "MCA",
    university: "Manipal University Jaipur",
    fullName: "Master of Computer Applications",
    duration: "24 Months",
    fee: "INR 1,58,000",
    image: "https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "mca-smu",
    title: "MCA",
    university: "Sikkim Manipal University",
    fullName: "Master of Computer Applications",
    duration: "24 Months",
    fee: "INR 1,10,000",
    image: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "bba-muj",
    title: "BBA",
    university: "Manipal University Jaipur",
    fullName: "Bachelor of Business Administration",
    duration: "36 Months",
    fee: "INR 1,39,500",
    image: "https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "bba-smu",
    title: "BBA",
    university: "Sikkim Manipal University",
    fullName: "Bachelor of Business Administration",
    duration: "36 Months",
    fee: "INR 90,000",
    image: "https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "bca-muj",
    title: "BCA",
    university: "Manipal University Jaipur",
    fullName: "Bachelor of Computer Applications",
    duration: "36 Months",
    fee: "INR 1,39,500",
    image: "https://images.unsplash.com/photo-1498050108023-c5249f4df085?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "bcom-muj",
    title: "BCOM",
    university: "Manipal University Jaipur",
    fullName: "Bachelor of Commerce",
    duration: "36 Months",
    fee: "INR 99,000",
    image: "https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "bcom-smu",
    title: "BCOM",
    university: "Sikkim Manipal University",
    fullName: "Bachelor of Commerce",
    duration: "36 Months",
    fee: "INR 75,000",
    image: "https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "ba-smu",
    title: "BA",
    university: "Sikkim Manipal University",
    fullName: "Bachelor of Arts",
    duration: "36 Months",
    fee: "INR 75,000",
    image: "https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "mcom-muj",
    title: "MCOM",
    university: "Manipal University Jaipur",
    fullName: "Master of Commerce",
    duration: "24 Months",
    fee: "INR 1,08,000",
    image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "majmc-muj",
    title: "MA-JMC",
    university: "Manipal University Jaipur",
    fullName: "MA in Journalism & Mass Communication",
    duration: "24 Months",
    fee: "INR 80,000",
    image: "https://images.unsplash.com/photo-1585829365295-ab7cd400c167?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "ma-eco-muj",
    title: "MA Economics",
    university: "Manipal University Jaipur",
    fullName: "Master of Arts in Economics",
    duration: "24 Months",
    fee: "INR 80,000",
    image: "https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "msc-math-muj",
    title: "MSc Math",
    university: "Manipal University Jaipur",
    fullName: "Master of Science in Mathematics",
    duration: "24 Months",
    fee: "INR 80,000",
    image: "https://images.unsplash.com/photo-1509228468518-180dd4864904?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "ma-pol-smu",
    title: "MA",
    university: "Sikkim Manipal University",
    fullName: "MA Political Science",
    duration: "24 Months",
    fee: "INR 75,000",
    image: "https://images.unsplash.com/photo-1541872703-74c5e44368f9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "mcom-smu",
    title: "MCOM",
    university: "Sikkim Manipal University",
    fullName: "Master of Commerce",
    duration: "24 Months",
    fee: "INR 75,000",
    image: "https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "ma-eng-smu",
    title: "MA English",
    university: "Sikkim Manipal University",
    fullName: "Master of Arts in English",
    duration: "24 Months",
    fee: "INR 75,000",
    image: "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  },
  {
    id: "ma-soc-smu",
    title: "MA Sociology",
    university: "Sikkim Manipal University",
    fullName: "Master of Arts in Sociology",
    duration: "24 Months",
    fee: "INR 75,000",
    image: "https://images.unsplash.com/photo-1577962917302-cd874c4e31d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
  }
];

// src/utils/seoUtils.ts
function generateDetailedSlug(program) {
  const universityShort = program.university.includes("Jaipur") ? "manipal-jaipur" : "sikkim-manipal";
  let detailedName;
  if (program.id === "majmc-muj") {
    detailedName = "ma-journalism-mass-communication";
  } else if (program.id === "ma-eco-muj") {
    detailedName = "ma-economics";
  } else if (program.id === "msc-math-muj") {
    detailedName = "msc-mathematics";
  } else if (program.id === "ma-pol-smu") {
    detailedName = "ma-political-science";
  } else if (program.id === "ma-eng-smu") {
    detailedName = "ma-english";
  } else if (program.id === "ma-soc-smu") {
    detailedName = "ma-sociology";
  } else {
    detailedName = program.fullName.toLowerCase().replace(/\s+/g, "-");
  }
  return `online-${detailedName}-${universityShort}`;
}

// server.ts
dotenv.config();
var app = (0, import_express.default)();
var PORT = 3e3;
app.use((req, res, next) => {
  res.setHeader("Strict-Transport-Security", "max-age=31536000; includeSubDomains");
  res.setHeader("X-Frame-Options", "SAMEORIGIN");
  res.setHeader("X-Content-Type-Options", "nosniff");
  res.setHeader("X-XSS-Protection", "1; mode=block");
  res.setHeader("Referrer-Policy", "strict-origin-when-cross-origin");
  res.setHeader("Permissions-Policy", "geolocation=(), microphone=(), camera=()");
  res.setHeader("X-DNS-Prefetch-Control", "on");
  res.setHeader("X-Content-Digest", "none");
  next();
});
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
app.get("/sitemap.xml", (req, res) => {
  const baseUrl = process.env.BASE_URL || "https://onlinemanipal.com";
  const currentDate = (/* @__PURE__ */ new Date()).toISOString().split("T")[0];
  let sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">`;
  sitemap += `
  <url>
    <loc>${baseUrl}/</loc>
    <lastmod>${currentDate}</lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>${baseUrl}/blogs</loc>
    <lastmod>${currentDate}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>`;
  programs.forEach((program) => {
    sitemap += `
  <url>
    <loc>${baseUrl}/programs/${program.id}</loc>
    <lastmod>${currentDate}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
  </url>`;
    const seoSlug = generateDetailedSlug(program);
    sitemap += `
  <url>
    <loc>${baseUrl}/${seoSlug}</loc>
    <lastmod>${currentDate}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>`;
  });
  sitemap += `
</urlset>`;
  res.set("Content-Type", "application/xml");
  res.send(sitemap);
});
app.get("/robots.txt", (req, res) => {
  const baseUrl = process.env.BASE_URL || "https://onlinemanipal.com";
  const robotsTxt = `# Robots.txt for Manipal Online University
User-agent: *
Allow: /

# Disallow API and private endpoints
Disallow: /api/
Disallow: /admin/
Disallow: /private/
Disallow: /auth/

# Crawl-delay for respectful crawling
Crawl-delay: 1

# Sitemap location
Sitemap: ${baseUrl}/sitemap.xml`;
  res.set("Content-Type", "text/plain");
  res.send(robotsTxt);
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
