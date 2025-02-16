# WordPress Custom Content Plugin - Project Requirements

## Overview
The WordPress Custom Content Plugin is designed to enhance WordPress content management capabilities by providing a structured content library system with advanced features and integrations.

## Project Structure

### Core Files
```
wp-custom-content/
├── admin/                  # Admin-specific assets
├── build/                  # Build files
├── docs/                   # Documentation
├── languages/             # Translations
├── public/                # Public assets
├── src/                   # PHP classes
├── tests/                 # Test files
├── CHANGELOG.md           # Version history
├── README.md             # Project documentation
├── composer.json         # Dependencies
├── uninstall.php        # Cleanup
└── wp-custom-content.php # Main plugin file
```

## Core Features

### 1. Content Library Management
- Custom post type for content items
- Advanced meta fields for structured data
- Custom taxonomies for content organization
- Search and filter capabilities
- Modern OOP architecture

### 2. File Management
- Remote file download and storage
- Integration with WordPress media library
- Support for various file types
- Secure file handling
- Organized file structure

### 3. Content Display
- EmbedPress integration for rich media display
- Customizable content templates
- Responsive design support
- Content filtering options
- Modern front-end assets

### 4. Bricks Builder Integration
- Full compatibility with Bricks Builder
- Custom templates support
- Content element integration
- Dynamic data support
- Extensible architecture

## Custom Post Types

### Content Library (`wpcc_content`)

The Content Library post type is the core component of this plugin, designed to store and manage various types of content with rich metadata.

#### Features
- **Title & Description**: Standard WordPress title and content fields
- **Content Type**: Categorize content (e.g., Document, Video, Presentation)
- **Remote File Management**: Store and manage files from external sources
- **Version Control**: Track content versions and updates
- **Integration Support**: Optional integrations with:
  - EmbedPress for enhanced media embedding
  - PDF Embedder for document viewing
  - Bricks Builder for layout customization

#### Meta Fields
| Field Name | Type | Description | Usage |
|------------|------|-------------|--------|
| content_type | select | Type of content (Document, Video, etc.) | Categorization and filtering |
| file_url | url | URL to the remote file | External file reference |
| file_version | text | Version identifier | Content versioning |
| last_updated | date | Last content update | Content management |
| embedpress_shortcode | text | EmbedPress integration | Media embedding (optional) |

#### Taxonomies
- **Content Categories**: Hierarchical taxonomy for content organization
- **Content Tags**: Non-hierarchical taxonomy for flexible labeling

#### Capabilities
- `edit_wpcc_content`
- `read_wpcc_content`
- `delete_wpcc_content`
- `edit_wpcc_contents`
- `publish_wpcc_contents`
- `read_private_wpcc_contents`

#### Integration Points
1. **Meta Box Integration**
   - Uses Meta Box plugin for field management
   - Efficient field registration and validation
   - Custom field types and layouts

2. **Optional Integrations**
   - EmbedPress: Enhanced media embedding (toggleable)
   - PDF Embedder: Document viewing (toggleable)
   - Bricks Builder: Layout customization

#### Performance Considerations
- Meta fields are registered efficiently using Meta Box API
- Queries are optimized for content listing
- Caching implemented for remote file metadata

## Analysis Results System

### Overview
The Analysis Results system provides a user interface for viewing and managing GPT-powered content analysis within the WordPress admin interface. It integrates with the GPT Trainer API and Prompt Library to deliver comprehensive content insights.

### Components

#### Analysis Results Display
- Meta box integration in content post editor
- List view column for analysis status
- Bulk analysis capabilities
- Real-time analysis updates via AJAX
- Responsive design for all screen sizes

#### Data Structure
Analysis results are stored in the `wpcc_gpt_analysis` table with the following schema:
- `id` (bigint): Primary key
- `post_id` (bigint): Associated post ID
- `analysis_type` (varchar): Type of analysis performed
- `analysis_data` (json): Analysis results in JSON format
- `created_at` (datetime): Timestamp of analysis

#### User Interface Elements
1. Meta Box
   - Analysis status and timestamp
   - Summary section
   - Key points list
   - Suggestions with actionable items
   - Metadata display
   - Manual analysis trigger

2. List View
   - Analysis status column
   - Bulk analysis action
   - Status indicators
   - Quick analysis information

### Integration Points
- GPT Trainer API for content analysis
- Prompt Library for customizable analysis prompts
- WordPress post editor
- Content post type system
- Admin settings

### Security Considerations
- Nonce verification for AJAX requests
- Capability checks for analysis operations
- Sanitization of analysis data
- Error handling and user feedback
- Rate limiting for API requests

## Class Architecture

### Core Classes

#### `WPCustomContent\Plugin`
Main plugin class that initializes the plugin and manages core functionality.
- Handles autoloading
- Initializes admin components
- Manages plugin lifecycle

#### `WPCustomContent\PostTypes\ContentPostType`
Manages the custom post type for content items.
- Registers the `wpcc_content` post type
- Handles meta box integration
- Manages file attachments and remote files
- Integrates with EmbedPress and PDF Embedder

#### `WPCustomContent\Admin\Settings`
Manages plugin settings and integration configurations.
- Provides settings page interface
- Handles integration toggles
- Manages plugin options

#### `WPCustomContent\Admin\HelpTabs`
Provides contextual help documentation in the WordPress admin.
- Adds help tabs for post type screens
- Documents fields and features
- Provides usage guidelines

### Custom Post Types

#### Content Library (`wpcc_content`)

##### Registration Details
```php
[
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'           => true,
    'show_in_menu'      => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'content-library'],
    'capability_type'    => ['wpcc_content', 'wpcc_contents'],
    'map_meta_cap'      => true,
    'has_archive'       => true,
    'hierarchical'      => false,
    'menu_position'     => 20,
    'menu_icon'         => 'dashicons-library',
    'supports'          => [
        'title',
        'editor',
        'author',
        'thumbnail',
        'excerpt',
        'revisions'
    ],
    'show_in_rest'      => true,
]
```

##### Meta Fields
| Field | Type | Description | Implementation |
|-------|------|-------------|----------------|
| content_type | select | Content category | Meta Box field |
| file_url | url | Remote file location | Meta Box field |
| file_version | text | Version identifier | Meta Box field |
| last_updated | date | Update timestamp | Meta Box field |
| embedpress_shortcode | text | EmbedPress integration | Meta Box field (conditional) |

##### Taxonomies
1. **Content Categories** (`wpcc_category`)
   - Hierarchical taxonomy
   - Used for content organization
   - Supports REST API

2. **Content Tags** (`wpcc_tag`)
   - Non-hierarchical taxonomy
   - Used for flexible labeling
   - Supports REST API

##### Capabilities
Custom capabilities are mapped for granular access control:
- `edit_wpcc_content`
- `read_wpcc_content`
- `delete_wpcc_content`
- `edit_wpcc_contents`
- `publish_wpcc_contents`
- `read_private_wpcc_contents`

##### Integration Points
1. **Meta Box Integration**
   - Uses Meta Box plugin for field management
   - Efficient field registration and validation
   - Custom field types and layouts

2. **Optional Integrations**
   - EmbedPress: Enhanced media embedding (toggleable)
   - PDF Embedder: Document viewing (toggleable)
   - Bricks Builder: Layout customization

#### Performance Considerations
- Meta fields are registered efficiently using Meta Box API
- Queries are optimized for content listing
- Caching implemented for remote file metadata

## Integration Architecture

#### EmbedPress Integration
- Conditionally loaded based on settings
- Adds custom meta box for shortcode management
- Handles media embedding from various providers

#### PDF Embedder Integration
- Optional integration for PDF display
- Adds viewer configuration options
- Handles secure document display

### Database Schema

#### Options Table
| Option Name | Description | Default |
|-------------|-------------|----------|
| wpcc_settings | Plugin settings | Array |
| wpcc_version | Plugin version | String |

#### Post Meta
| Meta Key | Description | Type |
|----------|-------------|------|
| _wpcc_content_type | Content type | String |
| _wpcc_file_url | Remote file URL | String |
| _wpcc_file_version | File version | String |
| _wpcc_last_updated | Update timestamp | Timestamp |
| _wpcc_embedpress_shortcode | EmbedPress shortcode | String |

## Development Guidelines

#### Adding New Features
1. Create new class in appropriate namespace
2. Register hooks in constructor
3. Add settings if needed
4. Update documentation
5. Add help documentation

#### Extending Post Type
1. Use `wpcc_meta_boxes` filter for new fields
2. Add capability checks
3. Update database schema
4. Add migration if needed

#### Integration Development
1. Create new integration class
2. Add settings toggle
3. Implement conditional loading
4. Add help documentation

## Technical Requirements

### WordPress Compatibility
- WordPress Version: 5.8+
- PHP Version: 7.4+
- MySQL Version: 5.6+
- Modern hosting environment

### Dependencies
- Meta Box Framework
- EmbedPress Plugin
- Bricks Builder Theme (optional)
- Composer for dependency management

### Development Tools
- Composer for PHP dependencies
- PHPUnit for testing
- PHPCS for coding standards
- PHP Compatibility checker
- Build tools for assets

### Performance
- Optimized database queries
- Efficient file handling
- Caching implementation
- Minimal impact on page load time
- Asset optimization

### Security
- Input validation and sanitization
- Proper capability checks
- Secure file handling
- XSS prevention
- CSRF protection
- Modern security practices

## User Interface

### Admin Interface
- Intuitive content management
- Clear navigation structure
- Responsive admin design
- User-friendly meta fields
- Modern UI components

### Frontend Display
- Customizable templates
- Responsive design
- Accessibility compliance
- SEO-friendly markup
- Modern front-end practices

## Documentation Requirements

### Developer Documentation
- Installation guide
- API documentation
- Hook reference
- Filter documentation
- Development setup guide

### User Documentation
- User manual
- Content creation guide
- Best practices
- Troubleshooting guide
- Configuration guide

## Testing Requirements

### Unit Testing
- PHPUnit tests for core functionality
- Integration tests
- WordPress coding standards compliance
- Automated testing
- CI/CD integration

### Browser Testing
- Cross-browser compatibility
- Mobile responsiveness
- Performance testing
- Accessibility testing
- Security testing

## Deployment

### Release Process
- Version control (Git)
- Semantic versioning
- Changelog maintenance
- Update procedure documentation
- Automated builds

### Distribution
- WordPress.org compatibility
- Plugin repository guidelines
- Update mechanism
- License compliance (GPL v2+)
- Asset optimization

## Maintenance

### Regular Updates
- Security patches
- WordPress compatibility
- Dependency updates
- Performance optimization
- Bug fixes

### Support
- Documentation updates
- User support
- Bug tracking
- Feature requests
- Community engagement