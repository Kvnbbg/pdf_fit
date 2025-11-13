# 📄 pdf_fit — Smart PDF Optimizer
**Tech & Stream** · Kevin Marville

A clean, fast, UNIX-style CLI for compressing, resizing, and optimizing PDFs.
Written in PHP, structured like Python, designed for multi-platform automation.

---

## 🔥 Features

- `pdf_fit smart file.pdf` → automatic optimization strategy
- Compression with tunable quality / DPI controls
- Resize presets for mobile or web deliverables
- Detailed before/after summary with percentage gain and execution time
- Modular architecture ready for Ghostscript, Imagick, and batch extensions

---

## 🚀 Installation

```bash
chmod +x bin/pdf_fit
```

Optional: add `pdf_fit/bin` to your `$PATH` or install via a global Composer package.

Requirements:

- PHP 8.1+
- Ghostscript (`gs`) for native compression (falls back gracefully if missing)
- `pdfinfo` (Poppler) for advanced analysis (optional)

---

## ⚙️ Usage

```bash
pdf_fit smart invoice.pdf
pdf_fit compress report.pdf --quality=60 --dpi=144
pdf_fit resize catalog.pdf --width=1080 --height=1920
```

All CLI options support both `--key=value` and `--key value` formats.

---

## 🧠 Architecture

```
bin/
  pdf_fit
config/
  defaults.php
src/
  Cli/ArgvParser.php
  Logger.php
  PdfAnalyzer.php
  PdfExporter.php
  PdfLoader.php
  PdfProcessor.php
  Pipeline.php
  StrategySelector.php
  Support/Stopwatch.php
```

Each component mirrors a Python-style pipeline:

1. **PdfLoader** — loads binaries and metadata.
2. **PdfAnalyzer** — inspects size/pages to guide the strategy.
3. **StrategySelector** — chooses smart/manual/resize profiles.
4. **PdfProcessor** — executes Ghostscript (if available) or stubs for future plugins.
5. **PdfExporter** — writes stable filenames without overwriting.
6. **Logger** — colorful feedback + summary reporting.
7. **Pipeline** — orchestrates the full workflow.

---

## 🧪 Roadmap / Extensions

- Plugin directory for extreme compression, thumbnails, or metadata extraction.
- Batch mode to optimize entire folders.
- HTTP API façade for integrations with Notion, Zapier, or SaaS dashboards.
- GitHub Actions workflow to self-test compression strategies.
- PHPUnit coverage for regression-proof refactors.

PRs and ideas welcome — let’s keep pushing the Tech & Stream toolbelt.

---

## 📜 Licence

MIT. Use, fork, and adapt for your automation stack.
