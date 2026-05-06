# Tableau Embed Shortcode

Author: Lobsang Wangdu

This plugin provides a reusable `[tableau_embed]` shortcode for Tableau Public embeds.

## Global defaults

In WordPress admin, go to **Settings -> Tableau Embed** to set global defaults for:

- desktop height
- tablet height
- mobile height
- maximum width
- default loading behavior
- fallback link visibility

After setting those once, your shortcode can be much shorter.

## Basic shortcode

```text
[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" height="827" mobile_height="727"]
```

## Short shortcode using global defaults

```text
[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard"]
```

## Shortcode with summary text

```text
[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" height="827" mobile_height="727" summary="Interactive Tableau dashboard showing user numbers."]
```

## Shortcode with custom public URL

```text
[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" public_url="https://public.tableau.com/views/1_Reserveusers/Usernumbersdashboard?:showVizHome=no&:embed=true" height="827" mobile_height="727"]
```

## Shortcode with different heading level

```text
[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" heading="h2" height="827" mobile_height="727"]
```

## Example for a different page

```text
[tableau_embed title="Reserve Map" name="YOURWORKBOOK/YOURDASHBOARD" height="827" mobile_height="727"]
```

## Example for a smaller chart

```text
[tableau_embed title="Reserve Visits" name="YOURWORKBOOK/YOURDASHBOARD" height="600" mobile_height="420" summary="Interactive Tableau chart showing reserve visits."]
```

## Example with responsive width and tablet height

```text
[tableau_embed title="Reserve Map" name="YOURWORKBOOK/YOURDASHBOARD" height="827" tablet_height="640" mobile_height="500" max_width="1100"]
```

## Attributes

- `title`
  Required. Visible chart title and accessible label.
- `name`
  Required. Tableau workbook and dashboard name in this format:
  `WORKBOOK_NAME/DASHBOARD_NAME`
  HTML-encoded or URL-encoded slashes are supported, for example
  `WORKBOOK_NAME&#47;DASHBOARD_NAME` and `WORKBOOK_NAME%2FDASHBOARD_NAME`.
- `public_url`
  Optional. Full Tableau Public URL for the iframe and fallback link. Must use `https://public.tableau.com/views/`.
- `max_width`
  Optional. Maximum frontend width in pixels. Useful when you want multiple embeds to appear the same width.
- `height`
  Optional. Desktop height in pixels. Default is `827`.
- `tablet_height`
  Optional. Tablet height in pixels for medium screens. Default is `640`.
- `mobile_height`
  Optional. Mobile height in pixels. Default is `727`.
- `heading`
  Optional. Heading level. Allowed values: `h2`, `h3`, `h4`.
  Default is `h2`.
- `summary`
  Optional. Short text shown under the title.
- `show_link`
  Optional. Set to `false` to hide the visible Tableau Public fallback link.
  Default is `true`. The fallback link appears below the embedded map or chart.
- `hide_title`
  Optional. Set to `true` to visually hide the title while keeping it available to screen readers.
  When the title is hidden, the fallback link uses generic visible text and keeps the chart title in an accessible label.
- `loading`
  Optional. Iframe loading behavior: `lazy` (default) or `eager`. Use `loading="eager"` for hero charts or other above-the-fold embeds so the browser does not defer loading.

## Reusable pattern

For each new Tableau page, change only:

- `title`
- `name`
- `public_url` if needed
- `max_width` if you want charts on different pages to appear the same width
- `height`
- `tablet_height`
- `mobile_height`
- `loading` (e.g. `eager` for a primary chart at the top of the page)

## Example set

### Large featured map

```text
[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" height="827" mobile_height="727" summary="Interactive Tableau dashboard showing user numbers."]
```

### Medium chart

```text
[tableau_embed title="Reserve Use by Year" name="YOURWORKBOOK/ReserveUseByYear" height="700" mobile_height="500"]
```

### Small chart

```text
[tableau_embed title="Reserve Visits" name="YOURWORKBOOK/ReserveVisits" height="600" mobile_height="420"]
```

### Responsive map with consistent width

```text
[tableau_embed title="Reserve Map" name="YOURWORKBOOK/YOURDASHBOARD" height="827" tablet_height="640" mobile_height="500" max_width="1100"]
```

## Hero or above-the-fold chart

Use **`loading="eager"`** so the iframe is not deferred by the browser’s lazy-loading behavior (default is `lazy`).

```text
[tableau_embed title="Reserve Map" name="YOURWORKBOOK/YOURDASHBOARD" loading="eager" height="827" mobile_height="727"]
```

## Accessibility notes

- Always use a clear `title`.
- Add `summary` for important charts when possible.
- Prefer `loading="eager"` for prominent charts at the top of the page so visualization is not unnecessarily delayed.
- Use `tablet_height`, `mobile_height`, and `max_width` to improve consistency across phones, tablets, and desktop.
- Keep the visible Tableau Public fallback link unless there is a strong reason to hide it.
- Use a real Tableau Public embed link in `public_url` if you want an exact iframe and fallback URL.
- For the most important visualizations, also provide a short text summary or data table on the page.

## Security notes

- The plugin renders Tableau Public inside a sandboxed iframe instead of loading Tableau JavaScript into the WordPress page.
- The iframe uses a restricted sandbox policy (`allow-same-origin allow-scripts`) to limit capabilities.
- The `name` value is sanitized to a Tableau Public workbook/view path.
- To allow only approved dashboards, use **Settings -> Tableau Embed** and add one allowed `WORKBOOK_NAME/DASHBOARD_NAME` per line.
- You can also enforce allowed dashboards in code with the `tableau_embed_shortcode_allowed_names` filter.

## Development (coding standards)

This plugin ships a `composer.json`, `phpcs.xml.dist`, and `.editorconfig` so you can run [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) checks locally:

```bash
composer install
composer run lint
```

## Where to use it

Paste the shortcode into a WordPress page, post, or block that supports shortcodes.
