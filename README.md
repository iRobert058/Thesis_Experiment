# ShopLab - PHP Experiment Server

PHP-based, within-subjects HCI study on how relocating a familiar UI element in an
e-commerce interface disrupts users' procedural memory and produces unintended
interactions. ShopLab runs the shopping tasks and logs every click. Demographics,
consent, and the post-task questions live externally in Qualtrics; the two systems are
linked by a session ID that participants copy manually.

The in-experiment manipulation is a **positional relocation** of the primary action
button, not a deceptive pattern in itself — it is designed to isolate the procedural-memory
mechanism that deceptive patterns exploit.

---

## Quick start (local)

```bash
cd "path/to/experiment"
php -S localhost:8000
```

Then open <http://localhost:8000> in a browser.

**Requirements:** PHP 8.0+ with `session` enabled (default). No Composer, no external
dependencies.

---

## Folder structure

```
Thesis_Experiment/
├── index.php          # Main application — router + all page rendering (EN/NL)
├── log.php            # Event logging endpoint (POST JSON → CSV)
├── admin.php          # Admin dashboard (password: shoplab2025)
├── images/            # 44 product images (1.png … 44.png)
├── data/
│   ├── .gitkeep       # Keeps the data/ directory tracked by git
│   ├── events.csv     # Created automatically on first event
│   └── session_counter.txt  # Running session count (created automatically)
└── README.md
```

---

## Before deployment

1. **Set the Qualtrics return URL.** Near the top of `index.php`, edit the
   `QUALTRICS_URL` constant:
   ```php
   define('QUALTRICS_URL', 'https://survey.uu.nl/jfe/form/SV_XXXXXXXXXXXX');
   ```
2. **Ensure `data/` is writable** by the web server process:
   ```bash
   chmod 775 data/
   ```
3. **Protect `data/` from direct HTTP access.** On Apache, add a `.htaccess` inside
   `data/` with `Deny from all`. On Nginx, add a `location /data { deny all; }` block.

---

## Session ID

Format: `S-2026-XXXX`, where `XXXX` is a random 4-digit number generated per session
(`random_int(1000, 9999)`). `data/session_counter.txt` holds a separate running total of
sessions started; it is not part of the ID.

---

## Procedure (steps controlled server-side by `$_SESSION['step']`)

| Step | Type | Description | Condition |
|------|------|-------------|-----------|
| 0 | Start page | Session ID displayed; participant clicks "Start experiment" | — |
| 1 | Task — Standard-A | Add **Wireless Earbuds Pro** to cart | standard |
| 2 | Distraction | Browse the catalog; click the **cheapest** product | — |
| 3 | Task — Standard-B | Add **Water Bottle** to cart | standard |
| 4 | Distraction | Browse the catalog; click the product with the **most reviews** | — |
| 5 | Task — Modified-A | Add **Running Shoes X200** to cart | modified |
| 6 | Filler survey | 3-question survey; all three are validation checks (Q9, D1, D2) | — |
| 7 | Task — Modified-B | Add **Bamboo Desk Organizer** to cart | modified |
| 8 | End page | Session ID shown large; copy button; "Return to survey" button | — |

An interstitial screen ("Ready for the next task? Press Continue.") appears between every
step. Step advancement is server-side only — no GET parameter can skip a step. The four
target products are spread across the catalog (display positions 21, 26, 33, 42) so
participants have to scroll and search rather than click the first item shown.

**Button layout per condition (product detail page):**

The primary slot (top, blue) and secondary slot (bottom, orange) are fixed by position.
What relocates between conditions is the *label pairing*:

- **Standard:** "Add to cart" occupies the top/blue slot; "Buy now" the bottom/orange slot.
- **Modified:** the two labels swap slots, so "Buy now" is now top/blue and "Add to cart"
  is bottom/orange.

So the "Add to cart" button — the one the task always asks for — moves both position and
colour between the standard and modified conditions. That relocation is the manipulation.

---

## Events CSV — column definitions

File: `data/events.csv` (never overwritten; header row written on first entry).

| Column | Type | Description |
|--------|------|-------------|
| `session_id` | string | Participant session ID (e.g. `S-2026-0001`) |
| `timestamp_iso` | string | UTC timestamp, ISO 8601 with milliseconds |
| `ms_elapsed` | integer | Milliseconds since session start (server-side) |
| `event_type` | string | See event types table below |
| `step` | integer | Experiment step 0–8 |
| `condition` | string | `standard` or `modified` (blank for non-task steps) |
| `target_product_id` | integer | Product ID of the target for this task (blank otherwise) |
| `clicked_element` | string | Button name (`add_to_cart` / `buy_now`) or product name (distraction / product view) |
| `click_x` | integer | Click X coordinate in CSS pixels |
| `click_y` | integer | Click Y coordinate in CSS pixels |
| `time_to_first_click_ms` | integer | Time from target product-page load to the button click (ms); JS-measured with `performance.now()` |
| `is_unintended_interaction` | 0/1 | `1` if the participant's click on the target product was "Buy now" |
| `question_id` | string | Survey question ID: `Q9`, `D1`, or `D2` |
| `question_text` | string | Full question text |
| `answer_value` | string | Selected option value |
| `is_validation_question` | 0/1 | `1` for all three survey questions (Q9, D1, D2) |
| `validation_passed` | 0/1/blank | `1` = correct; `0` = incorrect; blank for non-survey rows |
| `completed` | 0/1 | `1` for `session_complete` and `task_complete` events |

### Event types

| `event_type` | Triggered by | Notes |
|---|---|---|
| `session_start` | Clicking "Start experiment" | Step 0 |
| `task_start` | JS on task catalog page load | Fired once per task step; marks task start |
| `product_view` | JS on product detail page load | `clicked_element` = product viewed; fired for every product opened, target or not |
| `button_click` | Clicking either button on a product page | On the target, `is_unintended_interaction` is set; on non-target products it is blank |
| `task_complete` | The single click on either button of the **target** product | `completed = 1`. On the target, the first click on either button both records the outcome and advances the step — there is no correction step, so exactly one `task_complete` per task |
| `distraction_click` | Clicking a product card in a distraction step | Steps 2 and 4 |
| `survey_response` | Survey form submission | One row per question (Q9, D1, D2) |
| `page_enter` | JS/PHP on page render | Catalog, product detail, survey, end pages |
| `page_exit` | JS `beforeunload` via `sendBeacon` | |
| `session_complete` | Reaching step 8 | `completed = 1` |

---

## H1 — unintended interactions

**Hypothesis:** the modified button order causes more "Buy now" clicks on tasks that ask
for "Add to cart."

**Measure:** `is_unintended_interaction` on `task_complete` rows.

- `1` when the participant's decisive click on the target was "Buy now" (wrong button).
- `0` when it was "Add to cart" (correct button).

Because the first click on the target both records the outcome and advances the task,
each task contributes exactly one value.

**Per-participant score:**
```
H1_score = sum(is_unintended_interaction) over Modified-A (step 5) and Modified-B (step 7)
         → range: 0, 1, or 2
```
Standard-A and Standard-B (steps 1 and 3) are the within-subjects baseline (expected 0).

**Python (pandas):**
```python
import pandas as pd

events = pd.read_csv("data/events.csv")
tasks  = events[events.event_type == "task_complete"].copy()

tasks["phase"] = tasks["step"].map({1: "standard", 3: "standard",
                                     5: "modified", 7: "modified"})

h1 = (tasks.groupby(["session_id", "phase"])["is_unintended_interaction"]
           .sum().reset_index(name="errors"))
```

---

## H2 — time to first click

**Hypothesis:** the modified button order increases decision time (hesitation or mis-click
correction).

**Measure:** `time_to_first_click_ms` on `task_complete` rows — the interval from opening
the **target product's** detail page (`performance.now()` at load) to the click on either
button, regardless of correctness. This excludes browsing/search time and isolates the
button-decision moment.

**Python (pandas):**
```python
timing = tasks[["session_id", "step", "condition", "time_to_first_click_ms"]].copy()
timing["time_ms"] = pd.to_numeric(timing["time_to_first_click_ms"])
```

---

## Exclusion rule

Participants are excluded if they fail **≥ 2 of the 3 validation questions** (Q9, D1, D2).

| ID | Type | Correct response | Fails when |
|----|------|-----------------|------------|
| Q9 | Instruction-masked attention check | "Never" (value `1`) | Any other value |
| D1 | Distraction comprehension (step 2 — cheapest) | "Portable Phone Stand (€12.99)" (value `d`) | Any other product |
| D2 | Distraction comprehension (step 4 — most reviews) | "More than 2,000" (value `d`) | Any other range |

D1 and D2 double as engagement checks: participants who did not attend to the catalog
during the distraction steps tend to get them wrong.

**Python (pandas):**
```python
val = events[events.is_validation_question == 1]
fails = (val.assign(failed=(val.validation_passed == 0))
            .groupby("session_id")["failed"].sum())

excluded_ids = fails[fails >= 2].index
clean = events[~events.session_id.isin(excluded_ids)]
```

The admin dashboard (`admin.php`) pre-computes this and flags excluded sessions in red.

---

## Admin dashboard

URL: `/admin.php` — password: `shoplab2025`

- Summary table grouped by session with task timings, error flags, validation failures,
  and exclusion status.
- Download link for the raw `events.csv`.
- Sessions with ≥ 2 validation failures shown with a red badge.

---

## Filler survey (step 6)

Three questions, all validation (`is_validation_question = 1`), stored as `survey_response`
rows. There is no non-validation "bogus" filler item — all validation lives in ShopLab.

- **Q9** — instruction-masked attention check ("select Never").
- **D1** — cheapest product in the store just browsed.
- **D2** — approximate review count of the most-reviewed product.

---

## Products

44 products across 5 categories (Electronics 9, Home & Kitchen 9, Sports 9,
Office & Study 9, Travel & Daily Use 8). Products 1–4 are the task targets; product 5 is
the pivot for both distraction questions; the remaining 39 are fillers.

The table below is in catalog display order (the order participants scroll through).

| ID | Name | Price | Reviews | Category | Role |
|----|------|-------|---------|----------|------|
| 5 | Portable Phone Stand | €12.99 | 2,341 | Electronics | Filler — cheapest & most-reviewed (D1/D2 answer) |
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
| 1 | Wireless Earbuds Pro | €39.99 | 847 | Electronics | **Standard-A target (step 1)** |
| 25 | Adjustable Dumbbell Pair | €74.99 | 826 | Sports | Filler |
| 26 | Foam Roller Pro | €23.50 | 1,419 | Sports | Filler |
| 27 | Cycling Gloves | €18.50 | 592 | Sports | Filler |
| 28 | Quick-Dry Sports Towel | €14.95 | 1,022 | Sports | Filler |
| 2 | Water Bottle | €24.95 | 312 | Home & Kitchen | **Standard-B target (step 3)** |
| 29 | Fitness Jump Rope | €16.99 | 1,197 | Sports | Filler |
| 30 | Monitor Stand Riser | €18.99 | 457 | Office & Study | Filler |
| 31 | Reusable To-Go Cutlery | €24.50 | 899 | Travel & Daily Use | Filler |
| 32 | Sticky Notes Bundle | €21.95 | 312 | Office & Study | Filler |
| 33 | Travel Toiletry Bag | €17.75 | 664 | Travel & Daily Use | Filler |
| 34 | Fine Tip Pen Set | €19.99 | 528 | Office & Study | Filler |
| 3 | Running Shoes X200 | €89.00 | 1,203 | Sports | **Modified-A target (step 5)** |
| 35 | Travel Pillow Memory Foam | €42.99 | 883 | Travel & Daily Use | Filler |
| 36 | Reusable Shopping Tote | €33.95 | 706 | Travel & Daily Use | Filler |
| 37 | Document Tray Organizer | €25.99 | 389 | Office & Study | Filler |
| 38 | Commuter Travel Mug | €39.50 | 1,158 | Travel & Daily Use | Filler |
| 39 | Ergonomic Wrist Rest | €18.95 | 477 | Office & Study | Filler |
| 40 | Noise-Isolating Headphones | €58.99 | 1,672 | Electronics | Filler |
| 41 | Compact Power Bank | €29.99 | 2,218 | Electronics | Filler |
| 42 | Cordless Hand Vacuum | €49.95 | 975 | Home & Kitchen | Filler |
| 4 | Bamboo Desk Organizer | €32.50 | 564 | Office & Study | **Modified-B target (step 7)** |
| 43 | Herb Garden Starter Kit | €27.50 | 612 | Home & Kitchen | Filler |
| 44 | Trail Hiking Backpack | €64.99 | 734 | Sports | Filler |

Distraction invariants:

- **Cheapest** (step 2 / D1): Portable Phone Stand, €12.99 — the only product at or below
  €12.99, so the answer is unambiguous.
- **Most reviewed** (step 4 / D2): Portable Phone Stand, 2,341 reviews. The next highest is
  Compact Power Bank at 2,218; both fall in the "More than 2,000" band, so D2's answer holds
  regardless.

Product images live in `images/` as `1.png … 44.png`; each product page falls back to an
emoji icon if its image is missing.