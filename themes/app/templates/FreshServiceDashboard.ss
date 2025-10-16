<div class="freshservice-dashboard">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>$Title</h1>
            </div>
        </div>

        <% with $FreshServiceData %>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>API Configuration Status</h3>
                        </div>
                        <div class="card-body">
                            <% if $IsConfigured %>
                                <div class="alert alert-success">
                                    <h4><i class="fas fa-check-circle"></i> FreshService API is configured</h4>
                                    <p><strong>Domain:</strong> $Domain</p>
                                    <p><strong>API Endpoint:</strong> $ApiUrl</p>
                                    <p><strong>API Key:</strong> <% if $HasApiKey %>✓ Configured<% else %>✗ Not configured<% end_if %></p>
                                </div>
                            <% else %>
                                <div class="alert alert-warning">
                                    <h4><i class="fas fa-exclamation-triangle"></i> FreshService API is not fully configured</h4>
                                    <p>Please configure the API settings in <a href="/admin/settings">Site Settings</a>.</p>

                                    <h5>Missing Configuration:</h5>
                                    <ul>
                                        <% if not $Domain %><li>FreshService Domain</li><% end_if %>
                                        <% if not $ApiUrl %><li>API Endpoint</li><% end_if %>
                                        <% if not $HasApiKey %><li>API Key</li><% end_if %>
                                    </ul>
                                </div>
                            <% end_if %>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <% if $IsConfigured %>
                                <div class="btn-group-vertical w-100" role="group">
                                    <button type="button" class="btn btn-primary" onclick="testAPIConnection()">
                                        <i class="fas fa-wifi"></i> Test API Connection
                                    </button>
                                    <% if $CanAccess %>
                                        <button type="button" class="btn btn-info" onclick="loadTickets()">
                                            <i class="fas fa-list"></i> View Tickets
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="showCreateTicketForm()">
                                            <i class="fas fa-plus"></i> Create Ticket
                                        </button>
                                    <% else %>
                                        <p class="text-muted">
                                            <small>Please log in to access ticket management features.</small>
                                        </p>
                                    <% end_if %>
                                </div>
                            <% else %>
                                <p class="text-muted">Configure the API settings first to access FreshService features.</p>
                                <a href="/admin/settings" class="btn btn-primary">
                                    <i class="fas fa-cog"></i> Configure Settings
                                </a>
                            <% end_if %>
                        </div>
                    </div>
                </div>
            </div>
        <% end_with %>

        <% if $FreshServiceData.IsConfigured %>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>API Test Results</h3>
                        </div>
                        <div class="card-body">
                            <div id="api-test-results">
                                <p class="text-muted">Click "Test API Connection" to check the API status.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <% if $FreshServiceData.CanAccess %>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3>Recent Tickets</h3>
                            </div>
                            <div class="card-body">
                                <div id="tickets-container">
                                    <p class="text-muted">Click "View Tickets" to load recent tickets.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <% end_if %>
        <% end_if %>
    </div>
</div>

<script>
function testAPIConnection() {
    const resultsDiv = document.getElementById('api-test-results');
    resultsDiv.innerHTML = '<div class="spinner-border" role="status"><span class="sr-only">Testing...</span></div> Testing API connection...';

    fetch('/api/freshservice/test')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                resultsDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> ${data.message}</h5>
                        <p><strong>Endpoint:</strong> ${data.endpoint}</p>
                    </div>
                `;
            } else {
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle"></i> Connection Failed</h5>
                        <p>${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle"></i> Error</h5>
                    <p>Failed to test connection: ${error.message}</p>
                </div>
            `;
        });
}

function loadTickets() {
    const container = document.getElementById('tickets-container');
    container.innerHTML = '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div> Loading tickets...';

    fetch('/api/freshservice/tickets?per_page=10')
        .then(response => response.json())
        .then(data => {
            if (data.tickets && data.tickets.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>ID</th><th>Subject</th><th>Status</th><th>Priority</th><th>Created</th></tr></thead><tbody>';
                data.tickets.forEach(ticket => {
                    html += `
                        <tr>
                            <td><a href="/api/freshservice/tickets/${ticket.id}" target="_blank">#${ticket.id}</a></td>
                            <td>${ticket.subject}</td>
                            <td><span class="badge badge-info">${ticket.status_name || ticket.status}</span></td>
                            <td><span class="badge badge-secondary">${ticket.priority_name || ticket.priority}</span></td>
                            <td>${new Date(ticket.created_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-info">No tickets found.</div>';
            }
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle"></i> Error</h5>
                    <p>Failed to load tickets: ${error.message}</p>
                </div>
            `;
        });
}

function showCreateTicketForm() {
    // This could open a modal or redirect to a form page
    alert('Create ticket functionality - implement as needed for your use case');
}
</script>

<% require css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css') %>
<% require css('https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css') %>
<% require javascript('https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js') %>
