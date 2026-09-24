# ShopLab

A lightweight PHP web shop built for a within-subjects HCI experiment. It measures how
**moving a familiar button** in an e-commerce interface disrupts procedural memory and
leads to unintended clicks.

Participants complete four short shopping tasks ("add product X to your cart"). In the
first two, the buttons sit where people expect them. In the last two, the
**Add to cart** and **Buy now** buttons swap places. ShopLab logs every click, and the
logs are used to test whether the swap causes more wrong clicks (H1) and slower
decisions (H2).

The manipulation is a plain positional relocation, not a deceptive pattern. It is meant to
isolate the procedural-memory mechanism that deceptive patterns exploit.

> Built for a master's thesis at Utrecht University. Consent, demographics and post-task
> questionnaires run in Qualtrics. ShopLab handles the shopping part only, and the two are
> linked by a session ID that participants copy across.

---

## Features

- Single-file PHP app with no framework, Composer or database
- English and Dutch interface
- Works on desktop and mobile
- Steps are enforced server-side, so participants can't skip ahead through the URL
- Every interaction is appended to a CSV file, including click coordinates and
  `performance.now()` timings
- Built-in attention and comprehension checks with an automatic exclusion rule
- Password-protected admin dashboard with per-session summaries and CSV export

## Quick start

Requirements: **PHP 8.0+** (the standard build has sessions enabled).

```bash
git clone https://github.com/iRobert058/Thesis_Experiment.git
cd Thesis_Experiment
cp config.example.php config.php   # then edit config.php
php -S localhost:8000
```

Open <http://localhost:8000> for the experiment and <http://localhost:8000/admin.php> for
the dashboard.

## Configuration

All local settings live in `config.php`, which is gitignored. Start by copying
`config.example.php`.

| Constant | Purpose |
|----------|---------|
| `QUALTRICS_URL` | Survey URL shown on the final page ("Return to survey") |
| `ADMIN_PASS` | Password for `admin.php`. Admin login is disabled while it is empty |

If `config.php` doesn't exist, the app falls back to `config.example.php`. The experiment
still runs, but the admin dashboard stays locked.

## Deployment

1. Upload the repository to any PHP 8 host.
2. Create `config.php` with a strong `ADMIN_PASS` and your survey URL.
3. Make `data/` writable by the web server, for example `chmod 775 data/`.
4. Block direct HTTP access to `data/`:
   - **Apache:** the included `data/.htaccess` does this already.
   - **Nginx:** add `location /data/ { deny all; }`.
5. Serve the site over HTTPS.

## Project structure

```
.
├── index.php            # Router and page rendering (catalog, product pages, tasks, survey, end page)
├── log.php              # Event logging endpoint (POST JSON → data/events.csv)
├── admin.php            # Admin dashboard (session summary, exclusions, CSV download)
├── config.example.php   # Configuration template → copy to config.php
├── images/              # Product images 1.png … 44.png (emoji fallback if missing)
└── data/                # Runtime output, gitignored
    ├── events.csv           # Event log (created on first event)
    └── session_counter.txt  # Running count of sessions started
```

---

## Study design

### Procedure

The server controls progression through `$_SESSION['step']`. Between every step,
participants see a "Ready for the next task?" screen.

| Step | Type | Instruction | Condition |
|------|------|-------------|-----------|
| 0 | Start | Session ID shown; click **Start experiment** | — |
| 1 | Task: Standard-A | Add **Wireless Earbuds Pro** to cart | standard |
| 2 | Distraction | Click the **cheapest** product | — |
| 3 | Task: Standard-B | Add **Water Bottle** to cart | standard |
| 4 | Distraction | Click the product with the **most reviews** | — |
| 5 | Task: Modified-A | Add **Running Shoes X200** to cart | modified |
| 6 | Filler survey | Three validation questions (Q9, D1, D2) | — |
| 7 | Task: Modified-B | Add **Bamboo Desk Organizer** to cart | modified |
| 8 | End | Session ID with copy button; return to survey | — |

The four target products sit at catalog positions 21, 26, 33 and 42. Participants have to
scroll and search for them instead of clicking the first item.

### The manipulation

A product page has two button slots that never move: a **top/blue** primary slot and a
**bottom/orange** secondary slot. The labels are what change:

| Condition | Top / blue | Bottom / orange |
|-----------|-----------|-----------------|
| Standard | Add to cart | Buy now |
| Modified | **Buy now** | **Add to cart** |

Every task asks for *Add to cart*, so in the modified condition the correct button moves
to a new position and takes on a new colour.

### Hypotheses

- **H1, unintended interactions.** The modified layout leads to more *Buy now* clicks
  when the task asks for *Add to cart*.
- **H2, decision time.** The modified layout makes participants slower to click once
  they are on the target product's page.

On the target product, the first click on either button records the outcome and moves
the participant to the next step. There is no chance to correct, so each task produces
exactly one observation.

### Exclusion rule

Participants who fail **2 or more of the 3** validation questions are excluded.

| ID | Check | Correct answer |
|----|-------|----------------|
| Q9 | Instruction-masked attention check | "Never" (value `1`) |
| D1 | Cheapest product seen in step 2 | Portable Phone Stand, €12.99 (value `d`) |
| D2 | Review count of the most-reviewed product in step 4 | "More than 2,000" (value `d`) |

`admin.php` applies this rule automatically and marks excluded sessions in red.

---

## Data

Events are appended to `data/events.csv`. Existing rows are never overwritten, and the
header row is written with the first event.

<details>
<summary><strong>Column definitions</strong></summary>

| Column | Type | Description |
|--------|------|-------------|
| `session_id` | string | Participant session ID, format `S-2026-XXXX` (random 4 digits) |
| `timestamp_iso` | string | UTC timestamp, ISO 8601 with milliseconds |
| `ms_elapsed` | int | Milliseconds since session start, measured server-side |
| `event_type` | string | See event types below |
| `step` | int | Experiment step, 0–8 |
| `condition` | string | `standard` / `modified` (blank outside task steps) |
| `target_product_id` | int | Target product for the current task |
| `clicked_element` | string | `add_to_cart` / `buy_now`, or a product name |
| `click_x`, `click_y` | int | Click coordinates in CSS pixels |
| `time_to_first_click_ms` | int | Time from the target product page loading to the button click |
| `is_unintended_interaction` | 0/1 | `1` if the click on the target was *Buy now* |
| `question_id` | string | `Q9`, `D1` or `D2` |
| `question_text` | string | Full question text |
| `answer_value` | string | Selected option value |
| `is_validation_question` | 0/1 | `1` for Q9, D1 and D2 |
| `validation_passed` | 0/1/blank | Correctness of a survey answer |
| `completed` | 0/1 | `1` on `task_complete` and `session_complete` |

</details>

<details>
<summary><strong>Event types</strong></summary>

| `event_type` | Triggered by |
|---|---|
| `session_start` | Clicking **Start experiment** |
| `task_start` | Loading the catalog at the start of a task |
| `product_view` | Opening any product page |
| `button_click` | Clicking *Add to cart* or *Buy now* on any product |
| `task_complete` | The one decisive click on the target product |
| `distraction_click` | Clicking a product in steps 2 and 4 |
| `survey_response` | Submitting the filler survey (one row per question) |
| `page_enter` / `page_exit` | Page render / `beforeunload` (via `sendBeacon`) |
| `session_complete` | Reaching step 8 |

</details>

### Analysis example (pandas)

```python
import pandas as pd

events = pd.read_csv("data/events.csv")

# Exclusions: ≥ 2 failed validation questions
val = events[events.is_validation_question == 1]
fails = (val.validation_passed == 0).groupby(val.session_id).sum()
clean = events[~events.session_id.isin(fails[fails >= 2].index)]

tasks = clean[clean.event_type == "task_complete"].copy()

# H1: wrong-button clicks per participant and condition (0–2 each)
h1 = (tasks.groupby(["session_id", "condition"])["is_unintended_interaction"]
           .sum().unstack())

# H2: decision time per task
h2 = tasks[["session_id", "step", "condition", "time_to_first_click_ms"]]
```

---

## Product catalog

The catalog has 44 products in five categories: Electronics (9), Home & Kitchen (9),
Sports (9), Office & Study (9) and Travel & Daily Use (8). Products 1–4 are the task
targets. Product 5, the Portable Phone Stand, is the answer to both distraction
questions: it is the cheapest item (€12.99, and nothing else costs that little) and the
most-reviewed one (2,341 reviews; the runner-up has 2,218, which is still in the
"> 2,000" band).

<details>
<summary><strong>Full catalog in display order</strong></summary>

| ID | Name | Price | Reviews | Category | Role |
|----|------|-------|---------|----------|------|
| 5 | Portable Phone Stand | €12.99 | 2,341 | Electronics | D1/D2 answer |
| 6 | Yoga Mat Premium | €45.00 | 789 | Sports | Filler |
| 7 | Manual Coffee Grinder | €28.75 | 156 | Home & Kitchen | Filler |
| 8 | Resistance Bands Set | €19.99 | 1,876 | Sports | Filler |
| 9 | A5 Notebook Set | €16.99 | 654 | Office & Study | Filler |
| 10 | Cable Organizer Kit | €13.50 | 982 | Office & Study | Filler |
| 11 | Desk Mat Large | €15.99 | 741 | Office & Study | Filler |
| 12 | Packing Cubes Set | €29.99 | 743 | Travel & Daily Use | Filler |
| 13 | Compact Umbrella | €17.50 | 421 | Travel & Daily Use | Filler |
| 14 | Insulated Lunch Box | €22.99 | 318 | Travel & Daily Use | Filler |
| 15 | Bluetooth Speaker Mini | €34.99 | 1,298 | Electronics | Filler |
| 16 | USB-C Charging Hub | €27.95 | 684 | Electronics | Filler |
| 17 | Smart LED Desk Lamp | €41.50 | 934 | Electronics | Filler |
| 18 | Wireless Mouse Silent | €18.99 | 1,511 | Electronics | Filler |
| 19 | Laptop Sleeve 14 Inch | €21.99 | 508 | Electronics | Filler |
| 20 | Digital Kitchen Scale | €15.99 | 1,124 | Home & Kitchen | Filler |
| 21 | Ceramic Dinner Bowl Set | €38.00 | 376 | Home & Kitchen | Filler |
| 22 | Cotton Throw Blanket | €36.95 | 642 | Home & Kitchen | Filler |
| 23 | Airtight Food Containers | €26.49 | 1,842 | Home & Kitchen | Filler |
| 24 | Stainless Steel Mixing Bowls | €31.99 | 733 | Home & Kitchen | Filler |
| 1 | Wireless Earbuds Pro | €39.99 | 847 | Electronics | **Target, step 1** |
| 25 | Adjustable Dumbbell Pair | €74.99 | 826 | Sports | Filler |
| 26 | Foam Roller Pro | €23.50 | 1,419 | Sports | Filler |
| 27 | Cycling Gloves | €18.50 | 592 | Sports | Filler |
| 28 | Quick-Dry Sports Towel | €14.95 | 1,022 | Sports | Filler |
| 2 | Water Bottle | €24.95 | 312 | Home & Kitchen | **Target, step 3** |
| 29 | Fitness Jump Rope | €16.99 | 1,197 | Sports | Filler |
| 30 | Monitor Stand Riser | €18.99 | 457 | Office & Study | Filler |
| 31 | Reusable To-Go Cutlery | €24.50 | 899 | Travel & Daily Use | Filler |
| 32 | Sticky Notes Bundle | €21.95 | 312 | Office & Study | Filler |
| 33 | Travel Toiletry Bag | €17.75 | 664 | Travel & Daily Use | Filler |
| 34 | Fine Tip Pen Set | €19.99 | 528 | Office & Study | Filler |
| 3 | Running Shoes X200 | €89.00 | 1,203 | Sports | **Target, step 5** |
| 35 | Travel Pillow Memory Foam | €42.99 | 883 | Travel & Daily Use | Filler |
| 36 | Reusable Shopping Tote | €33.95 | 706 | Travel & Daily Use | Filler |
| 37 | Document Tray Organizer | €25.99 | 389 | Office & Study | Filler |
| 38 | Commuter Travel Mug | €39.50 | 1,158 | Travel & Daily Use | Filler |
| 39 | Ergonomic Wrist Rest | €18.95 | 477 | Office & Study | Filler |
| 40 | Noise-Isolating Headphones | €58.99 | 1,672 | Electronics | Filler |
| 41 | Compact Power Bank | €29.99 | 2,218 | Electronics | Filler |
| 42 | Cordless Hand Vacuum | €49.95 | 975 | Home & Kitchen | Filler |
| 4 | Bamboo Desk Organizer | €32.50 | 564 | Office & Study | **Target, step 7** |
| 43 | Herb Garden Starter Kit | €27.50 | 612 | Home & Kitchen | Filler |
| 44 | Trail Hiking Backpack | €64.99 | 734 | Sports | Filler |

</details>

---

## Privacy

ShopLab does not store names, IP addresses or other direct identifiers. The only link to
the Qualtrics responses is the random session ID. Collected data in `data/` is
gitignored, so keep it that way and handle it according to your ethics approval.

## License

[MIT](LICENSE) © 2026 Robert Karzijn
