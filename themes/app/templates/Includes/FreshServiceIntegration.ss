<%-- Example template showing how to use FreshService configuration --%>
<% if $isFreshServiceConfigured %>
    <div class="freshservice-integration">
        <h3>FreshService Integration</h3>
        <p>Connected to: $getFreshServiceConfig.FreshServiceDomain</p>
        <p>API Endpoint: $getFreshServiceApiUrl</p>

        <%-- You can now make AJAX calls to your API endpoint --%>
        <script>
            const apiUrl = '$getFreshServiceApiUrl';
            const apiKey = '$getFreshServiceConfig.FreshServiceAPIKey';

            // Example AJAX call structure
            // fetch(apiUrl, {
            //     headers: {
            //         'Authorization': 'Basic ' + btoa(apiKey + ':X'),
            //         'Content-Type': 'application/json'
            //     }
            // });
        </script>
    </div>
<% else %>
    <div class="alert alert-warning">
        <p>FreshService integration is not configured. Please contact the administrator.</p>
    </div>
<% end_if %>
