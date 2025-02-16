# GPT-Trainer Dashboard Data Model

This document explains the relationships between different entities in the GPT-Trainer Dashboard system.

## Core Entities

### 1. Chatbots (Parent Entity)
The primary entity in the system.
- **Properties**:
  - `uuid`: Unique identifier
  - `name`: Chatbot name
  - `description`: Chatbot description
  - `visibility`: public/private
  - `system_prompt`: System prompt for the chatbot
  - `show_citations`: Boolean flag for citations
  - `temperature`: Float value for response randomness
  - `created_at`: Creation timestamp
  - `updated_at`: Last modification timestamp
  - `deleted_at`: Soft delete timestamp (nullable)

### 2. Agents (Child of Chatbot)
Specialized components that belong to a chatbot.
- **Properties**:
  - `uuid`: Unique identifier
  - `chatbot_uuid`: Reference to parent chatbot
  - `name`: Agent name
  - `type`: Agent type (user-facing/background/human-escalation/pre-canned/spam-defense)
  - `description`: Agent description
  - `meta`: JSON object containing settings
    - `prompt`: Agent's specific prompt
    - `temperature`: Float value for response randomness
    - `max_tokens`: Maximum tokens for response
    - `top_p`: Nucleus sampling parameter
    - `frequency_penalty`: Frequency penalty value
    - `presence_penalty`: Presence penalty value
    - `bias`: Response bias value
    - `stickness`: Response consistency value
    - `default_message`: Default response message
    - `use_all_sources`: Boolean for data source access
    - `tags`: Array of associated tags
  - `created_at`: Creation timestamp
  - `updated_at`: Last modification timestamp
  - `deleted_at`: Soft delete timestamp (nullable)
- **Relationship**: One chatbot can have multiple agents (1:N)

### 3. Chat Sessions
Represents individual conversation instances.
- **Properties**:
  - `uuid`: Unique identifier
  - `chatbot_uuid`: Reference to chatbot
  - `status`: Session status (active/completed/error)
  - `created_at`: Session start timestamp
  - `updated_at`: Last activity timestamp
  - `deleted_at`: Soft delete timestamp (nullable)
- **Relationship**: One chatbot can have multiple sessions (1:N)

### 4. Messages
Individual messages within a chat session.
- **Properties**:
  - `uuid`: Unique identifier
  - `session_uuid`: Reference to parent session
  - `role`: Message role (user/assistant/system)
  - `content`: Message content
  - `created_at`: Message timestamp
  - `updated_at`: Last modification timestamp
  - `deleted_at`: Soft delete timestamp (nullable)
- **Relationship**: One session can have multiple messages (1:N)

### 5. Data Sources
External knowledge sources for chatbots.
- **Properties**:
  - `uuid`: Unique identifier
  - `name`: Source name
  - `type`: Source type (file/database/api)
  - `description`: Source description
  - `configuration`: JSON object with source-specific settings
  - `status`: Source status (active/inactive/error)
  - `created_at`: Creation timestamp
  - `updated_at`: Last modification timestamp
  - `deleted_at`: Soft delete timestamp (nullable)
- **Relationship**: Many-to-many with chatbots (M:N)

### 6. Tags
Organization system for data sources and agents.
- **Properties**:
  - `uuid`: Unique identifier
  - `name`: Tag name
  - `description`: Tag description
  - `created_at`: Creation timestamp
  - `updated_at`: Last modification timestamp
  - `deleted_at`: Soft delete timestamp (nullable)
- **Relationship**: Many-to-many with data sources and agents (M:N)

## Relationship Hierarchy

```
Chatbot
├── Agents (1:N)
│   └── Tags (M:N)
├── Chat Sessions (1:N)
│   └── Messages (1:N)
└── Data Sources (M:N)
    └── Tags (M:N)
```

## Key Design Points

1. **Hierarchical Structure**
   - Chatbots are top-level entities
   - Agents provide specialized functionality
   - Sessions track conversations
   - Messages store conversation content
   - Data sources provide knowledge base
   - Tags organize both data sources and agents

2. **Scalability Features**
   - UUID-based identification
   - Soft delete support
   - Flexible JSON metadata storage
   - Efficient many-to-many relationships
   - Independent session tracking

3. **System Benefits**
   - Clear entity relationships
   - Flexible data source integration
   - Comprehensive conversation tracking
   - Efficient tagging system
   - Robust agent management
   - Support for multiple chatbot types
