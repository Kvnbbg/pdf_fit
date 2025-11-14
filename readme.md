# 📄 pdf_fit v3 — Quantum-grade PDF optimization

**Tech & Stream** · Kevin Marville

A production-ready toolkit for compressing, resizing, and orchestrating PDF workflows. Built in PHP, structured like Python, branded for the Tech & Stream ecosystem.

---

## ✨ Highlights

- **Smart CLI** — `pdf_fit smart file.pdf` auto-selects the best Ghostscript strategy.
- **Extreme modes** — `compress`, `optimize`, `extreme`, `resize`, and **batch** directory processing.
- **Plugin ecosystem** — metadata extraction, thumbnail generation, quality suggestions.
- **REST API** — ship as a microservice with `pdf_fit server` or deploy `public/index.php`.
- **Logging & telemetry** — before/after stats, plugin reports, execution timing.
- **Job Application Toolkit** — `job_apply` analyses job posts and outputs a Kevin-specific 10-step plan.
- **CI-ready** — PHPUnit coverage + GitHub Actions.
- **Composer global tool** — `composer global require techandstream/pdf_fit` (after publishing).

---

## 🚀 Quick start

```bash
composer install
chmod +x bin/pdf_fit
php bin/pdf_fit smart tests/Fixtures/example.pdf
```

Install globally once released:

```bash
composer global require techandstream/pdf_fit
pdf_fit smart doc.pdf
```

Requirements:

- PHP 8.1+
- Ghostscript (`gs`) for compression + thumbnail plugins
- `pdfinfo` (Poppler) for metadata enrichment (optional)

---

## 🧠 CLI commands

```bash
pdf_fit smart invoice.pdf
pdf_fit compress report.pdf --quality=55 --dpi=150
pdf_fit resize brochure.pdf --width=1080 --height=1920
pdf_fit extreme archive.pdf
pdf_fit batch ./statements --mode=smart
pdf_fit server --host=0.0.0.0 --port=8080
job_apply mission.txt --json
```

The CLI prints a quantum-style summary: input/output, before/after size, gain %, strategy JSON, runtime, processing notes, and plugin payloads.

---

## 🏗 Architecture

```
src/
  Core/
    Pipeline.php
    PdfLoader.php
    PdfAnalyzer.php
    StrategySelector.php
    PdfProcessor.php
    PdfExporter.php
    Logger.php
    Utils.php
    Stopwatch.php
  Plugins/
    CompressExtreme.php
    HighQuality.php
    MetadataExtractor.php
    ThumbnailGenerator.php
    PluginManager.php
  Batch/BatchProcessor.php
  Api/Server.php
bin/pdf_fit
public/index.php
config.php
```

Everything is modular: the CLI, API, plugins, and batch processor all call the same Pipeline orchestrator.

---

## 🔌 Plugin matrix

| Plugin | Purpose | Output |
| ------ | ------- | ------ |
| `compress_extreme` | Suggests extreme follow-up when the PDF remains heavy | CLI summary & JSON |
| `high_quality` | Flags dense imagery to nudge optimize mode | CLI summary |
| `metadata` | Extracts Poppler metadata and size metrics | CLI + API |
| `thumbnail` | Generates a JPEG preview of page 1 | File path |

Register your own plugin by calling `PluginManager::register('name', fn($context) => [...])` before `Pipeline::run()`.

---

## 🧰 Job Application Toolkit

`job_apply` turns any job description into a rapid intelligence brief for Kevin Marville (Tech & Stream):

```bash
php bin/job_apply job_post.txt
```

What you get:

- A summary of the role, company, tech keywords, culture cues, budget and deadlines.
- A 10-step roadmap derived from the "10 Things You MUST Do Before Applying For a Job" video, but rewritten for the Tech & Stream positioning (web + IoT + content).
- Optional JSON output with `--json` for automations (Notion, Obsidian, custom CRM).

See [`docs/job_application_roadmap.md`](docs/job_application_roadmap.md) for the full checklist that powers the agent.

---

## 🌐 REST API

```bash
php bin/pdf_fit server --port=8080
# POST a PDF to http://localhost:8080 with multipart/form-data
```

`public/index.php` accepts:

- `pdf`: uploaded file
- `mode`: `smart|compress|optimize|extreme|resize`
- Extra fields become pipeline options (e.g., `quality`, `dpi`, `width`, `height`).

Response payload mirrors the CLI summary, perfect for Zapier, Notion, or SaaS control panels.

---

## 🧪 Quality gates

- PHPUnit suite (`tests/`) with analyzer, strategy, and processor coverage.
- GitHub Actions workflow (`.github/workflows/ci.yml`) for automated testing.
- Configurable smart profiles via `config.php`.

Run locally:

```bash
./vendor/bin/phpunit
```

---

## 🛣 Roadmap

- GPU-accelerated compression via Dockerized Ghostscript builds.
- Queue-based batch ingestion (Redis + Supervisor).
- Frontend dashboard with progressive previews.
- Plugin marketplace for Tech & Stream automation clients.

---

## 📜 Licence

MIT — shipped with ❤️ by Tech & Stream. Use it, fork it, improve it.
