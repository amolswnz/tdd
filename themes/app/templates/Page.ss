<!DOCTYPE html>
<html lang="en-NZ" class="h-full">
<head>
    <% base_tag %>
    <title>$Title | $SiteConfig.Title</title>
    <% if $MetaDescription %><meta name="description" content="$MetaDescription" /><% end_if %>
    <% if $ExtraMeta %>$ExtraMeta<% end_if %>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="$themedResourceURL('dist/main.css')">
    <script src="$themedResourceURL('dist/main.js')" type="module" defer></script>
</head>
<body class="page $ClassName">
    <div id="app" v-cloak class="flex flex-col min-h-screen">
        <%-- <% include SiteBanners %> --%>
        <% include Header %>

        <main id="main" class="flex-1 p-8 bg-gray-50/40">
            $Layout
        </main>

        <% include Footer %>
    </div>
</body>
</html>
