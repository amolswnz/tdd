# FreshService API Routes

This document outlines the available routes for the FreshService integration.

## Base URLs

- **API Routes**: `/api/freshservice/`

## Available Endpoints

### Dashboard
- **URL**: `/api/freshservice/dashboard`
- **Method**: GET
- **Description**: Shows the FreshService integration dashboard with configuration status and controls

### API Testing
- **URL**: `/api/freshservice/test`
- **Method**: GET
- **Description**: Tests the FreshService API connection
- **Returns**: JSON response with connection status

### Tickets Management

#### Get All Tickets
- **URL**: `/api/freshservice/tickets`
- **Method**: GET
- **Parameters**: 
  - `per_page` (optional): Number of tickets per page (default: 30)
  - `page` (optional): Page number (default: 1)
- **Description**: Retrieves a list of tickets from FreshService
- **Returns**: JSON array of tickets

#### Get Single Ticket
- **URL**: `/api/freshservice/tickets/{id}`
- **Method**: GET
- **Description**: Retrieves a specific ticket by ID
- **Returns**: JSON object with ticket details

## Example Usage

### JavaScript/Fetch API

```javascript
// Test API connection
fetch('/api/freshservice/test')
  .then(response => response.json())
  .then(data => console.log(data));

// Get tickets
fetch('/api/freshservice/tickets?per_page=10&page=1')
  .then(response => response.json())
  .then(data => console.log(data));

// Create a ticket
fetch('/api/freshservice/tickets', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    subject: 'Test Ticket',
    description: 'This is a test ticket',
    email: 'test@example.com'
  })
})
.then(response => response.json())
.then(data => console.log(data));

// Update a ticket
fetch('/api/freshservice/tickets/123', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    subject: 'Updated Ticket Subject',
    status: 4
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

### cURL Examples

```bash
# Test API connection
curl -X GET /api/freshservice/test

# Get tickets
curl -X GET "/api/freshservice/tickets?per_page=10&page=1"

## Error Responses

All endpoints return appropriate HTTP status codes:

- **200**: Success
- **201**: Created (for new tickets)
- **400**: Bad Request (invalid data)
- **403**: Forbidden (no access)
- **404**: Not Found
- **405**: Method Not Allowed
- **500**: Internal Server Error

Error responses include a JSON object with error details:

```json
{
  "error": "Error message description"
}
```

## Authentication & Permissions

- API access requires user authentication
- FreshService API must be configured in Site Settings
- All requests are authenticated using the configured FreshService API key
