<div class="freshservice-page">
    <div class="freshservice-header">
        <h1 class="freshservice-title">$Title</h1>
        <% if $Content %>
            <div class="freshservice-content">
                $Content
            </div>
        <% end_if %>
    </div>

    <div class="freshservice-main">
        <% if $ElementalArea %>
            $ElementalArea
        <% end_if %>

        <%-- Add custom FreshService specific content here --%>
        <pre>
            FreshService Integration -> API Endpoint: $getFreshServiceApiUrl
        </pre>
    </div>

    <div id="vue-app"></div>
</div>

<script src="$themedResourceURL('dist/vue-app.js')"></script>
