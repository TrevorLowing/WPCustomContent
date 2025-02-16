# GPT-Trainer Dashboard API Documentation

## Overview
The GPT-Trainer Dashboard API provides endpoints for managing chatbots, agents, data sources, and tags. All endpoints require authentication using Laravel Sanctum tokens.

## Authentication
- **Type**: Bearer Token (Laravel Sanctum)
- **Header**: `Authorization: Bearer {token}`

### Getting a Token
```http
POST /api/auth/token
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

## API Endpoints

### Chatbots

#### List Chatbots
```http
GET /api/chatbots
```
Response: Array of chatbot objects

#### Get Chatbot
```http
GET /api/chatbots/{chatbot_uuid}
```
Response: Single chatbot object

#### Create Chatbot
```http
POST /api/chatbots
Content-Type: application/json

{
    "name": "My Chatbot",
    "description": "Description",
    "visibility": "public",
    "system_prompt": "You are a helpful assistant",
    "show_citations": true,
    "temperature": 0.7
}
```

#### Update Chatbot
```http
PUT /api/chatbots/{chatbot_uuid}
Content-Type: application/json

{
    "name": "Updated Name",
    "description": "Updated description",
    ...
}
```

#### Delete Chatbot
```http
DELETE /api/chatbots/{chatbot_uuid}
```

### Agents

#### List Chatbot Agents
```http
GET /api/chatbots/{chatbot_uuid}/agents
```
Response: Array of agent objects

#### Get Agent
```http
GET /api/chatbots/{chatbot_uuid}/agents/{agent_uuid}
```
Response: Single agent object

#### Create Agent
```http
POST /api/chatbots/{chatbot_uuid}/agents
Content-Type: application/json

{
    "name": "My Agent",
    "type": "user-facing",
    "description": "Agent description",
    "meta": {
        "prompt": "You are a specialized agent",
        "temperature": 0.7,
        "max_tokens": 150,
        "top_p": 1,
        "frequency_penalty": 0,
        "presence_penalty": 0,
        "use_all_sources": true,
        "tags": ["tag1", "tag2"]
    }
}
```

#### Update Agent
```http
PUT /api/chatbots/{chatbot_uuid}/agents/{agent_uuid}
Content-Type: application/json

{
    "name": "Updated Agent",
    "description": "Updated description",
    ...
}
```

#### Delete Agent
```http
DELETE /api/chatbots/{chatbot_uuid}/agents/{agent_uuid}
```

### Chat Sessions

#### List Sessions
```http
GET /api/chatbots/{chatbot_uuid}/sessions
```
Response: Array of session objects

#### Get Session
```http
GET /api/chatbots/{chatbot_uuid}/sessions/{session_uuid}
```
Response: Single session object

#### Create Session
```http
POST /api/chatbots/{chatbot_uuid}/sessions
```

#### End Session
```http
PUT /api/chatbots/{chatbot_uuid}/sessions/{session_uuid}/end
```

### Messages

#### List Session Messages
```http
GET /api/chatbots/{chatbot_uuid}/sessions/{session_uuid}/messages
```
Response: Array of message objects

#### Send Message
```http
POST /api/chatbots/{chatbot_uuid}/sessions/{session_uuid}/messages
Content-Type: application/json

{
    "content": "User message",
    "role": "user"
}
```

### Data Sources

#### List Data Sources
```http
GET /api/data-sources
```
Response: Array of data source objects

#### Get Data Source
```http
GET /api/data-sources/{source_uuid}
```
Response: Single data source object

#### Create Data Source
```http
POST /api/data-sources
Content-Type: application/json

{
    "name": "My Data Source",
    "type": "file",
    "description": "Source description",
    "configuration": {
        "path": "/path/to/file",
        "format": "pdf"
    }
}
```

#### Update Data Source
```http
PUT /api/data-sources/{source_uuid}
Content-Type: application/json

{
    "name": "Updated Source",
    "description": "Updated description",
    ...
}
```

#### Delete Data Source
```http
DELETE /api/data-sources/{source_uuid}
```

### Tags

#### List Tags
```http
GET /api/tags
```
Response: Array of tag objects

#### Create Tag
```http
POST /api/tags
Content-Type: application/json

{
    "name": "My Tag",
    "description": "Tag description"
}
```

#### Update Tag
```http
PUT /api/tags/{tag_uuid}
Content-Type: application/json

{
    "name": "Updated Tag",
    "description": "Updated description"
}
```

#### Delete Tag
```http
DELETE /api/tags/{tag_uuid}
```

## Models

### Chatbot
```json
{
    "uuid": "string",
    "name": "string",
    "description": "string",
    "visibility": "string (public/private)",
    "system_prompt": "string",
    "show_citations": "boolean",
    "temperature": "float",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Agent
```json
{
    "uuid": "string",
    "chatbot_uuid": "string",
    "name": "string",
    "type": "string",
    "description": "string",
    "meta": {
        "prompt": "string",
        "temperature": "float",
        "max_tokens": "integer",
        "top_p": "float",
        "frequency_penalty": "float",
        "presence_penalty": "float",
        "use_all_sources": "boolean",
        "tags": ["string"]
    },
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Session
```json
{
    "uuid": "string",
    "chatbot_uuid": "string",
    "status": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Message
```json
{
    "uuid": "string",
    "session_uuid": "string",
    "role": "string",
    "content": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Data Source
```json
{
    "uuid": "string",
    "name": "string",
    "type": "string",
    "description": "string",
    "configuration": "object",
    "status": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Tag
```json
{
    "uuid": "string",
    "name": "string",
    "description": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

## Error Handling

### Error Response Format
```json
{
    "error": {
        "code": "string",
        "message": "string",
        "details": "object (optional)"
    }
}
```

### Common Error Codes
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `429`: Too Many Requests
- `500`: Server Error

### Validation Errors
```json
{
    "error": {
        "code": "422",
        "message": "Validation failed",
        "details": {
            "field_name": ["error message"]
        }
    }
}
```

## Rate Limiting
- Default: 60 requests per minute
- Authenticated: 120 requests per minute
- Headers:
  - `X-RateLimit-Limit`
  - `X-RateLimit-Remaining`
  - `X-RateLimit-Reset`
