# Tableau Embed Shortcode

Author: Lobsang Wangdu

This plugin provides a reusable `[tableau_embed]` shortcode for Tableau Public embeds.

## Basic shortcode

```text
[tableau_embed title="User Numbers Dashboard" name="1_Reserveusers/Usernumbersdashboard" height="827" mobile_height="727"]
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
- `height`
  Optional. Desktop height in pixels. Default is `827`.
- `mobile_height`
  Optional. Mobile height in pixels. Default is `727`.
- `heading`
  Optional. Heading level. Allowed values: `h2`, `h3`, `h4`.
  Default is `h2`.
- `summary`
  Optional. Short text shown under the title.
- `show_link`
  Optional. Set to `false` to hide the visible Tableau Public fallback link.
  Default is `true`.
- `hide_title`
  Optional. Set to `true` to visually hide the title while keeping it available to screen readers.

## Reusable pattern

For each new Tableau page, change only:

- `title`
- `name`
- `public_url` if needed
- `height`
- `mobile_height`

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

## Accessibility notes

- Always use a clear `title`.
- Add `summary` for important charts when possible.
- Keep the visible Tableau Public fallback link unless there is a strong reason to hide it.
- Use a real Tableau Public embed link in `public_url` if you want an exact iframe and fallback URL.
- For the most important visualizations, also provide a short text summary or data table on the page.

## Security notes

- The plugin renders Tableau Public inside a sandboxed iframe instead of loading Tableau JavaScript into the WordPress page.
- The `name` value is sanitized to a Tableau Public workbook/view path.
- To allow only approved dashboards, add a `tableau_embed_shortcode_allowed_names` filter that returns the permitted `WORKBOOK_NAME/DASHBOARD_NAME` values.

## Where to use it

Paste the shortcode into a WordPress page, post, or block that supports shortcodes.
