# Resume Auto Parser v2.1

Resume Auto Parser is a high-performance WordPress plugin designed to automate recruitment workflows. It transforms unstructured resumes into structured data to auto-fill application forms instantly.

## 🚀 Key Features
- **Smart Drag & Drop**: Modern interface for seamless file uploads.
- **Universal Multi-Format**: Extracts text from PDF, DOCX, MD, and TXT.
- **Entity Extraction**: Automatically identifies Name, Contact Info, Social Links, Skills, and Work History.
- **Visual Feedback**: Real-time parsing status and field highlighting.
- **Pivoted to Resume ATS**: Fully rebranded for HR and recruitment use cases.

## 🛠 Installation
1. Upload the plugin to `/wp-content/plugins/`.
2. Activate via the WordPress Dashboard.
3. Run `composer install` to activate the PDF/Word extraction engines.
4. Add `[auto_form_parser]` to any page.

## 📈 Roadmap (PRD v2.1)
We are currently in **Phase 2** of development.
- **Phase 1**: Core Regex Engine (Completed)
- **Phase 2**: AI (Gemini/OpenAI) & OCR Integration (In Progress)
- **Phase 3**: Form Plugin Adapters (Gravity, CF7, WPForms)
- **Phase 4**: Application Persistence (CPT)

## 🏗 Tech Stack
- **Backend**: PHP 8.0+, Composer (`smalot/pdfparser`, `phpoffice/phpword`).
- **Frontend**: Vanilla ES6 JS, jQuery (bridge), Modern CSS (Inter font).
- **Core Engine**: Regex Anchor Strategy (Tier 1 Extraction).

## 🔒 Security
- Strict file type whitelisting.
- Recursive sanitization of extracted strings.
- Nonce protection on all AJAX endpoints.

---
*Developed for Resume Automation Excellence.*
