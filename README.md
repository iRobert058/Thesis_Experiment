# Thesis_Experiment

Static webshop experiment template for running frontend-only design experiments (no backend).

## Run

Open `/home/runner/work/Thesis_Experiment/Thesis_Experiment/index.html` in a browser.

## Experimental conditions

The template supports three design conditions:

- `control`
- `minimal`
- `promo`

Choose a condition with a query parameter:

- `index.html?condition=control`
- `index.html?condition=minimal`
- `index.html?condition=promo`

or from the on-page condition selector.

## Measurement hooks (frontend only)

The template logs user interactions in memory and in `localStorage`:

- condition changes
- product card clicks
- add-to-cart clicks
- checkout button clicks

Use the **Export Logs** button to download a JSON log file for analysis.
