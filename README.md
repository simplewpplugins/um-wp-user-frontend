# WP User Frontend Integration for Ultimate Member

## Description

# WP User Frontend Integration for Ultimate Member

Give members a frontend content workspace inside their Ultimate Member profiles.

## Description

WP User Frontend Integration for Ultimate Member connects [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) with [WP User Frontend](https://wordpress.org/plugins/wp-user-frontend/). It adds profile tabs where members can list, submit and edit their content without visiting the WordPress admin.

Each tab is linked to a WP User Frontend post form. The tab shows an **Entries** list of the profile owner's submissions for that form. The owner also gets an **Add New** view for creating entries and can edit existing ones in place. Visitors see the owner's published entries. Workspaces support any registered post type, including custom post types, as configured in the selected form.

Entry cards can use a built-in layout (grid, list or compact rows) or a custom design. The **multi-column layout designer** lets administrators build cards visually from rows and columns. Cards can include the post title, featured image, taxonomy terms and the form's own custom fields. A live preview shows real entries exactly as members will see them.

## Features

- **Entries + Add New workspace.** List, create and edit entries inside one profile tab.
- **Form-specific listings.** Entries are matched to the selected WP User Frontend form, so several forms can share a post type and still be listed separately.
- **Custom post types.** Workspaces use the post type configured in the selected form.
- **Frontend editing.** Reuses WP User Frontend's own edit form, validation and permissions.
- **Stays in the profile.** After a member submits or updates an entry, they return to Entries with a confirmation.
- **Multi-column layout designer:**
  - Rows of one to four columns, with proportional or content-sized widths.
  - Drag and drop, presets, undo/redo and keyboard support.
  - Cards per row set separately for desktop, tablet and phone.
  - Columns stack automatically when a card is narrow.
  - Preview with sample content or real published entries.
- **Custom fields in cards.** Display the selected form's custom fields, including formatted numbers and uploaded images. Hidden, password and protected fields are never shown.
- **Search, filters and pagination.** Live search, per-taxonomy filters, a "Clear filters" button and AJAX pagination.
- **Role restrictions.** Choose which WordPress or Ultimate Member roles a tab appears for.
- **Privacy-aware.** Respects Ultimate Member profile privacy. Only the profile owner can add and edit entries.
- **Translation-ready.** All interface text is translatable, and a `.pot` file is included.

## Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) (active)
- [WP User Frontend](https://wordpress.org/plugins/wp-user-frontend/) (active)

WP User Frontend Pro is not required. Form fields or features that need Pro in WP User Frontend still need the appropriate license.

## Installation

1. Install and activate Ultimate Member and WP User Frontend.
2. Upload the plugin ZIP via **Plugins › Add New › Upload Plugin**, then activate it.
3. Create and publish a post form in WP User Frontend, and set its post type and submission permissions.
4. Go to **Ultimate Member › Frontend Post Tabs** and click **Add New Tab**.
5. Enter a title, choose **Entries + Add New workspace**, and select your form.
6. Choose a layout, posts per page, search, taxonomy filters and any role restrictions.
7. Publish the tab, then open a member profile to check the new tab.

## Designing custom cards

1. In the tab settings, set **Entries layout** to **Custom layout (designer)**.
2. Start from a preset or build from scratch:
   - **Add row** creates a row. Choose its column layout in the settings panel.
   - Drag elements from the palette into a column, or select a column and click an element.
   - Click any row or element on the card to edit its content and style.
3. Use **Desktop / Tablet / Phone** to check each screen size.
4. Switch the preview to **Real entries** to see actual posts.
5. Click **Update** to save.

**Available elements:** title, excerpt, custom field, taxonomy terms, static text, featured image, author avatar, author name, publish date, last updated, status (owner only), comment count, button, divider and spacer.

**Keyboard shortcuts:**

| Key | Action |
| --- | --- |
| Enter | Select the focused element |
| Alt + Arrow keys | Move the selected element |
| Delete | Remove the selected element |
| Ctrl/Cmd + Z | Undo |
| Ctrl/Cmd + Shift + Z | Redo |

> **Tip:** Ultimate Member limits profile content width to 600px by default. To show several cards side by side, raise **Profile Area Max Width** under **Ultimate Member › Settings › Appearance › Profile**.

## Developer hooks

| Hook | Type | Purpose |
| --- | --- | --- |
| `umwpuf_results_query_args` | filter | Modify the `WP_Query` arguments for entries. Receives `$args`, `$tab_id`, `$profile_id`. |
| `umwpuf_form_meta_fields` | filter | Adjust which form fields the designer can display. Receives `$fields`, `$form_id`. |
| `umwpuf_reserved_tab_keys` | filter | Add profile tab keys that custom tabs must not reuse (for example, tabs from other extensions). |
| `UMWPUF_template` | filter | Change the path of a plugin template file. Receives `$path`, `$template`. |

## FAQ

**Can I use custom post types?**
Yes. Select a WP User Frontend form configured for that post type in a workspace tab.

**Are entries filtered by form or by post type?**
By both. A workspace lists entries by the profile owner, of the form's post type, that were submitted through the selected form.

**Can visitors edit or add entries?**
No. Only the profile owner can add and edit entries, subject to WordPress capabilities and WP User Frontend's rules. Visitors see published entries only.

**What happens to my existing tabs and designs when I upgrade?**
They keep their settings, and custom designs from earlier versions are converted automatically. See the changelog for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) or the `readme.txt` file for the full version history.

## License

GPL v2 or later. See [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).

Developed by [Simple Plugins](https://github.com/simplewpplugins).

## Installation

### Requirements

- WordPress 6.5 or later
- PHP 7.4 or later
- [Ultimate Member](https://wordpress.org/plugins/ultimate-member/), installed and active
- [WP User Frontend](https://wordpress.org/plugins/wp-user-frontend/), installed and active

WP User Frontend Pro is not required.

### Install the plugin

1. Install and activate **Ultimate Member** and **WP User Frontend**.
2. In WordPress, go to **Plugins › Add New › Upload Plugin**.
3. Choose `um-wp-user-frontend-1.6.0.zip` and click **Install Now**.
4. Click **Activate**.

The plugin will not activate unless both required plugins are active. If either one is later deactivated, the integration pauses and shows an admin notice.

### Upgrading from an earlier version

1. Back up your site and test the upgrade on a staging site first.
2. Upload the new ZIP. WordPress offers to replace the installed version; confirm it.
3. Existing tabs keep their settings, and custom card designs are converted automatically.
4. Check the **Tab key** column under **Ultimate Member › Frontend Post Tabs**. A tab whose slug matched a core Ultimate Member tab (for example "posts") now uses the key `umwpuf-{ID}`. Update any links to it.

## Usage

### 1. Prepare a WP User Frontend form

1. Go to **User Frontend › Post Forms** and create a form, or open an existing one.
2. Set the **post type** the form creates. Custom post types are supported.
3. Configure submission permissions, then **publish** the form.

### 2. Create a profile tab

1. Go to **Ultimate Member › Frontend Post Tabs** and click **Add New Tab**.
2. Enter the tab title shown on member profiles.
3. Configure the tab under **Tab Settings**:
   - **Tab purpose:** choose **Entries + Add New workspace** (recommended).
   - **WP User Frontend form:** select the form from step 1.
   - **Entries layout:** Grid, List, Compact rows, or **Custom layout (designer)**.
   - **Entries per page** and **Enable live search**.
   - **Tab icon** and an optional **Description**, shown above the entries.
   - **Show on profiles with these roles:** leave empty for everyone.
   - **Taxonomy filters:** tick the taxonomies visitors can filter by.
4. Optionally set the tab's position under **Page Attributes › Order**.
5. Click **Publish**.

### 3. How members use the tab

- **Owners** see two sub-tabs:
  - **Entries:** their published, pending, draft and scheduled entries. The pencil icon on a card edits that entry inside the profile.
  - **Add New:** the submission form. After saving, they return to Entries with a confirmation.
- **Visitors** see the owner's published entries only, with search and filters if enabled.

### 4. Design custom cards (optional)

1. Set **Entries layout** to **Custom layout (designer)**. The **Custom Layout Designer** box appears.
2. Pick a preset from **Start from a preset…**, or build your own:
   - Click **+ Add row** and choose a column layout. Rows can have one to four columns.
   - Drag elements from the **Elements** palette into a column. You can also select a column and click an element to add it.
   - Click any row or element on the card to edit it in the settings panel. Elements have **Content** and **Style** tabs.
   - Drag elements to reorder them or move them between columns.
3. Under **Card & grid**, set how many cards appear per row on desktop, tablet and phone.
4. For multi-column rows, choose when columns stack:
   - **When the card is narrow** (recommended)
   - **On phones only**
   - **Never**
5. Use **Desktop / Tablet / Phone** to check each screen size. Switch **Grid preview** to **Real entries** to see actual posts.
6. Click **Update** to save.

**Keyboard shortcuts in the designer**

| Key | Action |
| --- | --- |
| Enter | Select the focused element |
| Alt + Arrow keys | Move the selected element |
| Delete | Remove the selected element |
| Ctrl/Cmd + Z | Undo |
| Ctrl/Cmd + Shift + Z | Redo |

### Tips

- **Showing custom fields.** Add a **Custom field** element and pick a field from the form. Hidden and password fields are never displayed.
- **Clickable taxonomy terms.** Set a **Taxonomy terms** element's link to **Filter this tab's entries**. Clicking a term then filters the list.
- **Wider profiles.** Ultimate Member limits profile content to 600px by default. To show several cards side by side, raise **Profile Area Max Width** under **Ultimate Member › Settings › Appearance › Profile**.
- **Legacy tab modes.** Submission-only, owner's posts and public posts modes are still available for existing tabs.

## Changelog

### 1.6.0 — 2026-11-22

# WP User Frontend Integration for Ultimate Member — 1.6.0

**Release date:** November 2026
**Requires:** WordPress 6.5+, PHP 7.4+, Ultimate Member, WP User Frontend
**Tested with:** WordPress 7.0, Ultimate Member 2.14.2, WP User Frontend 4.3.15

## Highlights

Version 1.6.0 turns each profile tab into a complete content workspace and adds a visual, multi-column layout designer for entry cards. It also fixes several long-standing bugs, including two privacy and permission issues, so every site running an earlier version should update.

## New: Multi-column layout designer

Choose **Custom layout (designer)** as a tab's entries layout to design the card shown for each entry.

- **Rows and columns.** Build cards from rows of one to four columns. Column widths can be proportional or "auto", which fits avatars, icons and buttons.
- **Drag and drop.** Drag elements from the palette into any column, reorder them, or move them between columns. You can also select a column and click an element to add it.
- **Live, editable canvas.** The canvas uses the same markup and styles as the profile tab, so what you see is what members see.
- **Per-device grid.** Set how many cards appear per row on desktop, tablet and phone.
- **Smart stacking.** Multi-column rows stack automatically when a card is narrower than 420px, so layouts adapt to any theme width. You can also stack on phones only, or never.
- **Presets.** Start from Classic, Horizontal, Listing, Author spotlight, Compact rows or Minimal. Presets fill in the selected form's custom fields and taxonomy automatically.
- **Real-entry preview.** Switch the grid preview from sample content to real published entries of the selected form. Unsaved changes are included.
- **Undo/redo and keyboard support.**
  - Ctrl+Z / Ctrl+Shift+Z undo and redo.
  - Enter selects an element, Alt+Arrow keys move it, and Delete removes it.
- **Unsaved-changes warning.** A badge appears when the design has changed, and the browser warns before you leave with unsaved changes.

### New card elements

- Custom field (WP User Frontend form fields)
- Author avatar
- Last updated date
- Comment count
- Static text
- Divider
- Spacer

### New element and card options

- Image aspect ratios (16:9, 4:3, 1:1, 3:4, 21:9) and an optional placeholder for entries without an image.
- Heading tag for titles, and excerpt length in words.
- Card hover effects (lift, glow) and the spacing between cards.

## New: WP User Frontend custom fields in cards

Cards can now display the custom fields of the tab's selected form.
- Number fields are formatted, image uploads are shown as images, and multi-choice answers display as readable lists.
- Hidden, password and protected (underscore) fields are never displayed.

## Improvements

**Entries**
- Previous/next pagination with a page summary ("Showing 1–6 of 15").
- A "Clear filters" button appears whenever a search or filter is active.
- Clearer empty states. Owners see "No entries yet" with a button to create their first entry, while searches show "No matching entries".
- Clicking a taxonomy badge in a card also updates the matching filter dropdown.
- A friendly error message with a Retry button if entries fail to load.

**Editing workflow**
- Separate confirmations for "Entry submitted" and "Changes saved".
- Notices can be dismissed and don't reappear on refresh.
- The edit view shows which entry is being edited and has a clear back link.

**Visitors and owners**
- Visitors no longer see the Entries / Add New sub-navigation, which only applies to the owner.
- Status badges use translated labels and colours for published, pending, draft and scheduled.

**Admin**
- The tab list shows new Purpose, Form, Layout and Tab key columns.
- Tabs can be ordered.
- The settings screen shows only the options that apply to the chosen tab purpose.
- New tabs default to the recommended Entries + Add New workspace.

**Other**
- Styles load in the page head on profile pages, which avoids a flash of unstyled content.
- All interface text is translatable, and a translation template (`.pot`) is included.

## Bug fixes

- **Tab slug collisions.** A tab whose slug matched a core Ultimate Member tab (for example "posts") replaced that tab, and both rendered on the same page. Such tabs now use their own key.
- **Broken edit links after AJAX.** After searching, filtering or changing pages, edit links pointed to `admin-ajax.php` instead of the profile tab.
- **Post-save redirect.** Returning to Entries after submitting or editing an entry did not work with Ultimate Member's standard `?profiletab=` URLs.
- **Side effects in role checks.** The role check switched Ultimate Member's current user as a side effect.
- **Legacy roles.** Ultimate Member 1.x role slugs (without the `um_` prefix) are now recognised.
- **Default links.** Elements saved without a link setting now use their type's default (titles, images and buttons link to the entry).

## Security

- **Private profiles.** Entries of profiles set to private could be retrieved through the AJAX endpoint. The endpoint now respects Ultimate Member profile privacy.
- **Tab management permissions.** Users with page-editing rights (such as Editors) could create and edit tab configurations. Tab management now requires the `manage_options` capability.
- **Hardened layout data.** Every value in a saved layout is whitelisted or clamped on the server, and layout content is never treated as HTML or arbitrary CSS.

## Upgrade notes

- **Back up and test on staging first.**
- **Existing tabs** keep their settings. Existing custom loop designs are converted automatically to the new row-and-column format.
- **Changed tab URL.** A tab whose slug collided with a core Ultimate Member tab now uses the URL key `umwpuf-{ID}`. Links to it were already broken by the collision, but update any you have shared. The new key is shown in the tab list's Tab key column.
- **Legacy tab modes** (submission only, owner's posts, public posts) continue to work. Existing submission-only tabs are not converted into workspaces automatically.
- **Tip:** Ultimate Member limits profile content width to 600px by default. To show several cards side by side, raise **Profile Area Max Width** under Ultimate Member › Settings › Appearance › Profile.

## Known limitations

- Entries submitted before WP User Frontend recorded their form ID (`_wpuf_form_id`) do not appear in form-specific workspaces.
- Frontend deletion of entries is not included.

### 1.5.0

- Added owner-only and public post dashboards with AJAX search and pagination.
- Added grid, list, table and custom loop item layouts.
- Added role-based profile tab restrictions and plugin dependency checks.

## WordPress Readme (converted)

# WP User Frontend Integration for Ultimate Member
Contributors: simplewpplugins
Tags: ultimate member, wp user frontend, frontend posting, custom post types, user profiles
Requires at least: 6.5
Requires PHP: 7.4
Requires Plugins: ultimate-member, wp-user-frontend
Stable tag: 1.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add WP User Frontend submission forms, form-specific entries, frontend editing, and customizable content layouts to Ultimate Member profile tabs.

## Description
**Give members a frontend content workspace inside their Ultimate Member profiles.**

Connect [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) with [WP User Frontend](https://wordpress.org/plugins/wp-user-frontend/). Create profile tabs with an **Entries** subtab showing content submitted through a selected form and an **Add New** subtab for submissions. Owners can edit their entries within the same profile tab without opening the WordPress admin editor.

The workspace supports the WordPress post type configured in the selected WP User Frontend form, including registered custom post types. Form-specific listings use WP User Frontend's `_wpuf_form_id` association, so entries submitted through different forms can remain separate even when they share a post type.

### Features
* **Entries + Add New workspace:** List submissions, create entries and edit existing entries inside one Ultimate Member profile tab.
* **Form-specific listings:** Display entries associated with the chosen WP User Frontend form and authored by the profile owner.
* **Custom post types:** Use the post type configured in the selected form; the post type must be registered and available on the site.
* **Frontend editing:** Reuse WP User Frontend's editing engine and its validation, form fields, and permissions. The integration isolates edit-form markup inside the profile tab.
* **Post-save navigation:** Redirect successful workspace submissions and updates to the Entries subtab when the request can be matched to the workspace.
* **Flexible layouts:** Grid, list, compact rows, and a per-tab drag-and-drop layout designer.
* **Multi-column card designer:** Split each card into rows of up to four columns (fractional or content-sized widths), with separate card counts for desktop, tablet and phone, presets, undo/redo and a live preview using real entries.
* **Custom fields in cards:** Show the selected form's WP User Frontend custom fields, including numbers and uploaded images. Hidden and password fields are never displayed.
* **Dynamic taxonomy filters:** Choose from public, UI-enabled taxonomies registered for the selected post type. Post types without suitable taxonomies do not require a filter.
* **Search and AJAX pagination:** Browse entries without reloading the entire profile page.
* **Role restrictions:** Control which profile owners are eligible for a tab using WordPress and Ultimate Member roles.
* **Legacy tab modes:** Existing submission-only, private My Posts and public Published Posts tabs remain available.

The core integration does not require WP User Frontend Pro. Features or form fields that require Pro in WP User Frontend still require the appropriate license.

### Privacy and editing
Visitors can see published entries in eligible public workspaces. Only the profile owner can use Add New and Edit, subject to WordPress capabilities and WP User Frontend's own rules. Owners can see their own draft, pending, scheduled and published entries in their workspace.

The plugin does not import older entries lacking a matching `_wpuf_form_id` value into a form-specific workspace. It does not provide a general-purpose Gutenberg or Elementor builder or a frontend deletion workflow.

## Installation
1. Install WordPress 6.5 or newer, Ultimate Member, and WP User Frontend.
2. Activate both required plugins, then install and activate this integration.
3. Create and publish a Post Form in WP User Frontend. Configure its post type and submission permissions.
4. Open **Ultimate Member > Frontend Post Tabs**, then add a tab.
5. Enter a tab title, choose the WP User Frontend form and select **Entries + Add New** for a combined workspace.
6. Choose a post layout, optional search and taxonomy filters, posts per page, and any role restrictions.
7. Select **Custom layout (designer)** under Entries layout to reveal the layout designer. Other layouts do not require it.
8. Publish the tab and open an Ultimate Member profile to verify the Entries and Add New subtabs.
9. Submit a test entry, check that it appears in Entries, and use its pencil icon to test frontend editing.

### Custom layout designer
Choose **Custom layout (designer)** as the entries layout. The designer shows an editable card and a grid preview:

* Add rows and pick a column layout (1 to 4 columns, including content-sized "auto" columns for avatars and buttons).
* Drag elements from the palette into any column, or select a column and click an element to add it. Drag elements to reorder or move them between columns.
* Elements: featured image, title, excerpt, custom field, taxonomy terms, static text, author avatar and name, publish and last-updated dates, comment count, status (owner only), button, divider and spacer.
* Choose how many cards appear per row on desktop, tablet and phone. Multi-column rows stack automatically when a card is narrower than 420px, or only on phones, or never.
* Switch the preview between sample content and real published entries of the selected form, rendered by the same template as the profile tab.
* Keyboard: Enter selects an element, Alt+Arrow keys move it, Delete removes it, Ctrl+Z / Ctrl+Shift+Z undo and redo.

Designs created with earlier versions are converted automatically into a single-column row.

Tip: Ultimate Member limits the profile content width (Ultimate Member > Settings > Appearance > Profile > Profile Area Max Width, 600px by default). Increase it if you want several cards side by side.

### Dynamic taxonomy filters
Available taxonomy filters are derived from the post type configured by the selected form. Choose one or more available public, UI-enabled taxonomies. The frontend displays a selector for each enabled taxonomy; selected filters can be combined. A post type without such taxonomies has no taxonomy filter controls.

### Existing installations
Existing tabs and their saved slugs are retained. Existing submission-only tabs are not automatically converted into workspaces. Back up your site and test an upgrade on staging, particularly if you use custom profile permissions, custom post types or form-specific redirects.

## Frequently Asked Questions
### Can I use custom post types?
Yes, in the Entries + Add New workspace. Select a WP User Frontend form configured to create that registered post type. Legacy My Posts and Published Posts modes continue to query standard WordPress posts.

### Are entries filtered by the form or just by post type?
Workspace entries must match the profile owner, the form's configured post type, and the `_wpuf_form_id` value associated with the selected form. Entries without that association are not automatically included.

### Can members edit entries without visiting wp-admin?
Yes. In workspace mode, eligible owners can open the edit form inside their profile tab. The edit integration reuses WP User Frontend's native editing behavior and permissions. Legacy My Posts mode has a different editing workflow.

### Where do users go after submitting or updating an entry?
For a recognized workspace submission or edit request, the integration redirects back to that workspace's Entries view. Other forms and requests retain WP User Frontend's redirect behavior.

### What if a custom post type has no category taxonomy?
You can select any eligible taxonomy registered for that post type. If there are none, the taxonomy filter is not shown. Search and pagination still work.

### Why is a tab missing?
Check that the tab is published, that the profile owner's role matches any restrictions, and that Ultimate Member and WP User Frontend are active. Private legacy modes are visible only to the profile owner.

### Does this plugin replace WP User Frontend's permissions?
No. WP User Frontend and WordPress remain responsible for underlying form submission and editing authorization. The integration adds profile-tab and entry ownership checks.

### Is WP User Frontend Pro required?
Not for the integration's core functionality. Individual Pro-only features still require Pro.

## Changelog
### 1.6.0
* New: multi-column layout designer (rows, up to four columns per row, drag and drop, presets, undo/redo, device preview, real-entry preview, keyboard support).
* New: per-device card columns, card hover effects, image aspect ratios, and new card elements (custom field, avatar, last updated, comment count, static text, divider, spacer).
* New: WP User Frontend custom fields can be displayed in cards; hidden, password and protected fields are excluded.
* New: Prev/next pagination with page summary, "Clear filters", clearer empty states, dismissible save notices, translated status labels.
* New: Purpose, Form, Layout and Tab key columns in the tab list; tab ordering; settings show only the options that apply to the chosen tab purpose.
* Fix: Edit links generated after AJAX search, filtering or pagination pointed to admin-ajax.php.
* Fix: Returning to Entries after submitting or editing did not work with Ultimate Member's ?profiletab= URLs.
* Fix: A tab whose slug matched a core Ultimate Member tab (for example "posts") replaced and double-rendered that tab. Such tabs now use the key umwpuf-{ID}.
* Fix: Entries of profiles set to private could be loaded through the AJAX endpoint.
* Fix: Editors could create and edit tab configurations; tab management now requires manage_options.
* Fix: The role check switched Ultimate Member's current user as a side effect; legacy UM 1.x role slugs are now recognised.
* Added form-specific Entries + Add New workspace inside Ultimate Member profile tabs.
* Added custom post type support for form-associated workspace entries.
* Added frontend edit integration with isolated output buffering to avoid duplicate page markup.
* Added workspace-aware redirects after successful submission and editing.
* Added dynamic multi-taxonomy filters based on the selected form's post type.
* Improved edit action placement within post cards and tables.
* Made the visual loop designer conditional on the Custom Loop Builder layout.
* Displayed tab descriptions below workspace navigation and removed the unused tab content editor.
* Preserved existing profile tab slugs and legacy tab modes.

### 1.5.0
* Added owner-only and public post dashboards with AJAX search and pagination.
* Added grid, list, table and custom loop item layouts.
* Added role-based profile tab restrictions and plugin dependency checks.

## Upgrade Notice
### 1.6.0
Back up and test on staging before upgrading. Existing tabs and custom loop designs keep their configuration (designs are converted automatically). A tab whose slug collided with a core Ultimate Member tab, such as "posts", now uses the URL key umwpuf-{ID}; update any links to it. Existing tabs keep their saved configuration; choose Entries + Add New explicitly to enable the new workspace. Requires active Ultimate Member and WP User Frontend plugins.
