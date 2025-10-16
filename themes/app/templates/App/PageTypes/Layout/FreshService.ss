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
        <div class="freshservice-features">
            <%-- This section can be customized for FreshService specific functionality --%>
        </div>
    </div>
</div>
