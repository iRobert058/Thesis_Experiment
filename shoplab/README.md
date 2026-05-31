# ShopLab — PHP Experiment Server

PHP-based within-subjects HCI study on UI dark patterns in e-commerce.
Handles experimental tasks and click logging only. Demographics, consent, and post-task questions are managed externally in Qualtrics; the two systems are linked via a session ID that participants copy manually.

---

## Quick start (local)

```bash
1. Open terminal
2. Copy filepath
3. cd "filepath"
4. php -S Localhost:8000

```x

Then open <http://localhost:8000> in a browser.

**Requirements:** PHP 8.0+ with `session` enabled (default). No Composer, no external dependencies.

---

## Folder structure

```
shoplab/
├── index.php          # Main application — router + all page rendering
├── log.php            # Event logging endpoint (POST JSON → CSV)
├── admin.php          # Admin dashboard (password: shoplab2025)
├── data/
│   ├── .gitkeep       # Keeps the data/ directory tracked by git
│   ├── events.csv     # Created automatically on first event
│   └── session_counter.txt  # Created automatically on first session
└── README.md
```

---

## Before deployment

1. **Set the Qualtrics return URL.** Open `index.php` and change line 7:
   ```php
   define('QUALTRICS_URL', 'https://REPLACE_WITH_YOUR_QUALTRICS_SURVEY_URL');
   ```
2. **Ensure `data/` is writable** by the web server process:
   ```bash
   chmod 775 data/
   ```
3. **Protect `data/` from direct HTTP access.** If using Apache, add a `.htaccess` inside `data/`:
   ```
   Deny from all
   ```
   On Nginx, add a `location /data { deny all; }` block.

---

## Session ID format

`S-2026-XXXX` — auto-randomized session ID generator.

---

## Procedure (7 steps, controlled by `$_SESSION['step']`)

| Step | Type | Description |
|------|------|-------------|
| 0 | Start page | Session ID displayed; participant clicks "Start experiment" |
| 1 | Task — Standard-A | Find Product 1, add to cart (standard button order) |
| 2 | Distraction | Browse catalog; click the cheapest product |
| 3 | Task — Standard-B | Find Product 2, add to cart (standard button order) |
| 4 | Distraction | Browse catalog; click the product with the most reviews |
| 5 | Task — Modified-A | Find Product 3, add to cart (modified button order) |
| 6 | Filler survey | 5-question survey with embedded validation checks |
| 7 | Task — Modified-B | Find Product 4, add to cart (modified button order) |
| 8 | End page | Session ID shown large; copy button; "Return to survey" button |

An interstitial screen ("Ready for the next task? Press Continue.") is shown between every step. Step advancement is server-side only — no GET parameter can skip a step.

**Button order per condition:**
- Standard: "Add to cart" on top, "Buy now" below.
- Modified: "Buy now" on top, "Add to cart" below. Colors of the button's change aswell, only the labels change.

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
| `target_product_id` | integer | Product ID of the target product for this task (blank otherwise) |
| `clicked_element` | string | Button name (`add_to_cart` / `buy_now`) or product name (distraction) |
| `click_x` | integer | Click X coordinate in CSS pixels |
| `click_y` | integer | Click Y coordinate in CSS pixels |
| `time_to_first_click_ms` | integer | Time from `task_start` event to first button click (ms); JS-measured |
| `is_unintended_interaction` | 0/1 | `1` if participant clicked "Buy now" first on an "Add to cart" task |
| `question_id` | string | Survey question ID: `F1`, `F2`, `Q9`, `Q14`, `Q15` |
| `question_text` | string | Full question text |
| `answer_value` | string | Selected option value (1–5) |
| `is_validation_question` | 0/1 | `1` for Q9, Q14, Q15; `0` for F1, F2 |
| `validation_passed` | 0/1/blank | `1` = correct; `0` = incorrect; blank for non-validation questions |
| `completed` | 0/1 | `1` for `session_complete` and `task_complete` events |

### Event types

| `event_type` | Triggered by | Notes |
|---|---|---|
| `session_start` | Clicking "Start experiment" | Step 0 |
| `task_start` | JS on task catalog page load | Fired once per task step; marks overall task start |
| `product_view` | JS on product detail page load | `clicked_element` = product name viewed; fired for every product opened, target or not |
| `button_click` | Clicking either button on a product page | `is_unintended_interaction` set only for the target product; blank for non-target clicks |
| `task_complete` | Same click as `button_click` on the **target** product | `completed = 1`; `time_to_first_click_ms` measured from target product page load |
| `distraction_click` | Clicking any product card in a distraction step | |
| `survey_response` | Survey form submission | One row per question (Q9, D1, D2) |
| `page_enter` | JS/PHP on page render | Logged on catalog, product detail, survey, end pages |
| `page_exit` | JS `beforeunload` via `sendBeacon` | |
| `session_complete` | Reaching step 8 | `completed = 1` |

---

## H1 operationalisation — unintended interactions

**Hypothesis:** The modified button order causes more "Buy now" clicks when the task specifies "Add to cart."

**Measure:** `is_unintended_interaction` column in `button_click` / `task_complete` rows.

- Value = `1` when the participant's first click was on "Buy now" (the wrong button).
- Value = `0` when the first click was on "Add to cart" (the correct button).

**Score per participant:**
```
H1_score = sum(is_unintended_interaction) across Modified-A (step 5) and Modified-B (step 7)
         → range: 0, 1, or 2
```

Standard-A and Standard-B serve as within-subjects baseline (expected score: 0).

**R filter:**
```r
task_events <- events %>%
  filter(event_type == "task_complete")

h1 <- task_events %>%
  mutate(phase = case_when(
    step %in% c(1, 3) ~ "standard",
    step %in% c(5, 7) ~ "modified"
  )) %>%
  group_by(session_id, phase) %>%
  summarise(errors = sum(as.integer(is_unintended_interaction)), .groups = "drop")
```

---

## H2 operationalisation — time to first click

**Hypothesis:** The modified button order increases decision time (hesitation or mis-click correction).

**Measure:** `time_to_first_click_ms` in `task_complete` rows.

- Time from when the participant first opens the **target product's detail page** (`performance.now()` at page load) to the first click on either button, regardless of correctness. This excludes browsing time and isolates the button-decision moment.

**R filter:**
```r
timing <- task_events %>%
  select(session_id, step, condition, time_to_first_click_ms) %>%
  mutate(time_ms = as.integer(time_to_first_click_ms))
```

---

## Exclusion rule

Participants are excluded if they answer **≥ 2 of the 3 validation questions** (Q9, D1, D2) incorrectly.

**Validation question logic:**
| ID | Type | Correct response | `validation_passed = 0` when |
|----|------|-----------------|-------------------------------|
| Q9 | Instruction-masked attention check | Select "Never" (value `1`) | Any other value selected |
| D1 | Distraction-task comprehension (step 2 — cheapest product) | Select "Portable Phone Stand (€12.99)" (value `d`) | Any other product selected |
| D2 | Distraction-task comprehension (step 4 — most reviews) | Select "More than 2,000" (value `d`) | Any other range selected |

D1 and D2 double as engagement checks: participants who did not pay attention to the catalog during distraction steps are likely to answer these incorrectly.

**R exclusion:**
```r
library(dplyr)

val_scores <- events %>%
  filter(is_validation_question == 1) %>%
  group_by(session_id) %>%
  summarise(val_failures = sum(validation_passed == 0), .groups = "drop")

excluded_ids <- val_scores %>%
  filter(val_failures >= 2) %>%
  pull(session_id)

clean_events <- events %>%
  filter(!session_id %in% excluded_ids)
```

The admin dashboard (`admin.php`) pre-computes this and flags excluded sessions in red.

---

## Admin dashboard

URL: `/admin.php` — password: `shoplab2025`

Features:
- Summary table grouped by session with task timings, error flags, validation failures, and exclusion status.
- Download link for the raw `events.csv` file.
- Sessions flagged for exclusion (≥ 2 validation failures) are shown with a red badge.

---

## Products

| ID | Name | Price | Reviews | Category | Role |
|----|------|-------|---------|----------|------|
| 1 | Wireless Earbuds Pro | €39.99 | 847 | Electronics | Standard-A target |
| 2 | Bamboo Water Bottle | €24.95 | 312 | Home & Kitchen | Standard-B target |
| 3 | Running Shoes X200 | €89.00 | 1,203 | Sports | Modified-A target |
| 4 | Bamboo Desk Organizer | €32.50 | 564 | Home & Kitchen | Modified-B target |
| 5 | Portable Phone Stand | €12.99 | 2,341 | Electronics | Filler |
| 6 | Yoga Mat Premium | €45.00 | 789 | Sports | Filler |
| 7 | Manual Coffee Grinder | €28.75 | 156 | Home & Kitchen | Filler |
| 8 | Resistance Bands Set | €19.99 | 1,876 | Sports | Filler |
| 9 | Atomic Habits | €16.99 | 1,654 | Books | Filler |
| 10 | The Psychology of Money | €13.50 | 982 | Books | Filler |
| 11 | Deep Work | €15.99 | 741 | Books | Filler |
| 12 | Classic Building Blocks Set | €29.99 | 743 | Toys | Filler |
| 13 | Magnetic Drawing Board | €17.50 | 421 | Toys | Filler |
| 14 | Wooden Puzzle Set | €22.99 | 318 | Toys | Filler |

Cheapest product (distraction step 2): **Portable Phone Stand** (€12.99).  
Most reviewed (distraction step 4): **Portable Phone Stand** (2,341 reviews).

> All new filler products are priced above €12.99 and have fewer than 2,341 reviews, preserving the correct answers for the D1 and D2 validation questions.
