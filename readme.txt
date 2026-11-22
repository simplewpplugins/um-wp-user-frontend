=== WP User Frontend Integration for Ultimate Member ===
Contributors: simplewpplugins
Tags: ultimate member, wp user frontend, frontend posting, custom post types, user profiles
Requires at least: 6.5
Requires PHP: 7.4
Requires Plugins: ultimate-member, wp-user-frontend
Stable tag: 1.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add WP User Frontend submission forms, form-specific entries, frontend editing, and customizable content layouts to Ultimate Member profile tabs.

== Description ==

**Give members a frontend content workspace inside their Ultimate Member profiles.**

Connect [Ultimate Member](https://wordpress.org/plugins/ultimate-member/) with [WP User Frontend](https://wordpress.org/plugins/wp-user-frontend/). Create profile tabs with an **Entries** subtab showing content submitted through a selected form and an **Add New** subtab for submissions. Owners can edit their entries within the same profile tab without opening the WordPress admin editor.

The workspace supports the WordPress post type configured in the selected WP User Frontend form, including registered custom post types. Form-specific listings use WP User Frontend's `_wpuf_form_id` association, so entries submitted through different forms can remain separate even when they share a post type.

= Features =

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

= Privacy and editing =

Visitors can see published entries in eligible public workspaces. Only the profile owner can use Add New and Edit, subject to WordPress capabilities and WP User Frontend's own rules. Owners can see their own draft, pending, scheduled and published entries in their workspace.

The plugin does not import older entries lacking a matching `_wpuf_form_id` value into a form-specific workspace. It does not provide a general-purpose Gutenberg or Elementor builder or a frontend deletion workflow.

== Installation ==

1. Install WordPress 6.5 or newer, Ultimate Member, and WP User Frontend.
2. Activate both required plugins, then install and activate this integration.
3. Create and publish a Post Form in WP User Frontend. Configure its post type and submission permissions.
4. Open **Ultimate Member > Frontend Post Tabs**, then add a tab.
5. Enter a tab title, choose the WP User Frontend form and select **Entries + Add New** for a combined workspace.
6. Choose a post layout, optional search and taxonomy filters, posts per page, and any role restrictions.
7. Select **Custom layout (designer)** under Entries layout to reveal the layout designer. Other layouts do not require it.
8. Publish the tab and open an Ultimate Member profile to verify the Entries and Add New subtabs.
9. Submit a test entry, check that it appears in Entries, and use its pencil icon to test frontend editing.

= Custom layout designer =

Choose **Custom layout (designer)** as the entries layout. The designer shows an editable card and a grid preview:

* Add rows and pick a column layout (1 to 4 columns, including content-sized "auto" columns for avatars and buttons).
* Drag elements from the palette into any column, or select a column and click an element to add it. Drag elements to reorder or move them between columns.
* Elements: featured image, title, excerpt, custom field, taxonomy terms, static text, author avatar and name, publish and last-updated dates, comment count, status (owner only), button, divider and spacer.
* Choose how many cards appear per row on desktop, tablet and phone. Multi-column rows stack automatically when a card is narrower than 420px, or only on phones, or never.
* Switch the preview between sample content and real published entries of the selected form, rendered by the same template as the profile tab.
* Keyboard: Enter selects an element, Alt+Arrow keys move it, Delete removes it, Ctrl+Z / Ctrl+Shift+Z undo and redo.

Designs created with earlier versions are converted automatically into a single-column row.

Tip: Ultimate Member limits the profile content width (Ultimate Member > Settings > Appearance > Profile > Profile Area Max Width, 600px by default). Increase it if you want several cards side by side.

= Dynamic taxonomy filters =

Available taxonomy filters are derived from the post type configured by the selected form. Choose one or more available public, UI-enabled taxonomies. The frontend displays a selector for each enabled taxonomy; selected filters can be combined. A post type without such taxonomies has no taxonomy filter controls.

= Existing installations =

Existing tabs and their saved slugs are retained. Existing submission-only tabs are not automatically converted into workspaces. Back up your site and test an upgrade on staging, particularly if you use custom profile permissions, custom post types or form-specific redirects.

== Frequently Asked Questions ==

= Can I use custom post types? =

Yes, in the Entries + Add New workspace. Select a WP User Frontend form configured to create that registered post type. Legacy My Posts and Published Posts modes continue to query standard WordPress posts.

= Are entries filtered by the form or just by post type? =

Workspace entries must match the profile owner, the form's configured post type, and the `_wpuf_form_id` value associated with the selected form. Entries without that association are not automatically included.

= Can members edit entries without visiting wp-admin? =

Yes. In workspace mode, eligible owners can open the edit form inside their profile tab. The edit integration reuses WP User Frontend's native editing behavior and permissions. Legacy My Posts mode has a different editing workflow.

= Where do users go after submitting or updating an entry? =

For a recognized workspace submission or edit request, the integration redirects back to that workspace's Entries view. Other forms and requests retain WP User Frontend's redirect behavior.

= What if a custom post type has no category taxonomy? =

You can select any eligible taxonomy registered for that post type. If there are none, the taxonomy filter is not shown. Search and pagination still work.

= Why is a tab missing? =

Check that the tab is published, that the profile owner's role matches any restrictions, and that Ultimate Member and WP User Frontend are active. Private legacy modes are visible only to the profile owner.

= Does this plugin replace WP User Frontend's permissions? =

No. WP User Frontend and WordPress remain responsible for underlying form submission and editing authorization. The integration adds profile-tab and entry ownership checks.

= Is WP User Frontend Pro required? =

Not for the integration's core functionality. Individual Pro-only features still require Pro.

== Changelog ==

= 1.6.0 =

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

= 1.5.0 =

* Added owner-only and public post dashboards with AJAX search and pagination.
* Added grid, list, table and custom loop item layouts.
* Added role-based profile tab restrictions and plugin dependency checks.

== Upgrade Notice ==

= 1.6.0 =

Back up and test on staging before upgrading. Existing tabs and custom loop designs keep their configuration (designs are converted automatically). A tab whose slug collided with a core Ultimate Member tab, such as "posts", now uses the URL key umwpuf-{ID}; update any links to it. Existing tabs keep their saved configuration; choose Entries + Add New explicitly to enable the new workspace. Requires active Ultimate Member and WP User Frontend plugins.
