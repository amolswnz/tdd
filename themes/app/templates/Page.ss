<!DOCTYPE html>
<html lang="en-NZ">
    <head>
        <% base_tag %>
        <title>$Title | $SiteConfig.Title</title>
        <% if $MetaDescription %><meta name="description" content="$MetaDescription" /><% end_if %>
        <% if $ExtraMeta %>$ExtraMeta<% end_if %>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
<body class="page $ClassName">
    <div id="app" v-cloak>
        <%-- <% include SiteBanners %> --%>
        <%-- <% include Header %> --%>

        <%-- <% include MobileMenu %> --%>
        <nav class="primary">
            <button class="nav-open-button">²</button>
            <ul>
                <% loop $Menu(1) %>
                    <li class="$LinkingMode"><a href="$Link" title="$Title.XML">$MenuTitle.XML</a></li>
                <% end_loop %>
            </ul>
        </nav>

        <main id="main">
            $Layout
        </main>

        <%-- <% include Footer %> --%>
    </div>
    </body>
</html>
