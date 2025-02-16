# WPCustomContent

A comprehensive WordPress content management plugin with GPT-powered analysis, advanced logging, and smart notifications.

![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![WordPress Version](https://img.shields.io/badge/WordPress-5.8%2B-blue)

## Features

- **Advanced Content Management**
  - Custom post types for organized content
  - Meta Box integration for enhanced fields
  - Support for EmbedPress and PDF Embedder
  - Bricks Builder compatibility

- **AI-Powered Analysis**
  - GPT integration for content analysis
  - Automated content suggestions
  - Smart content categorization

- **Robust Logging System**
  - Multiple log levels (DEBUG to CRITICAL)
  - Database-based logging
  - Log retention management
  - Export functionality

- **Smart Notifications**
  - Email notifications for critical errors
  - Configurable notification levels
  - HTML email templates
  - Custom notification rules

- **System Diagnostics**
  - Comprehensive system status
  - Real-time diagnostics
  - Performance monitoring
  - Configuration validation

## Requirements

- PHP 7.4 or higher
- WordPress 5.8 or higher
- MySQL 5.6 or higher
- [Meta Box](https://metabox.io/) plugin
- Optional: EmbedPress, PDF Embedder

## Installation

1. Upload the plugin files to `/wp-content/plugins/wpcustomcontent/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure the plugin settings at WPCustomContent > Settings

## Configuration

### API Keys
- Set up GPT Trainer API key in Settings
- Configure email notification settings
- Adjust logging preferences

### Integration Settings
- Enable/disable EmbedPress support
- Configure PDF Embedder integration
- Set up Bricks Builder compatibility

### Logging Configuration
- Set log retention period
- Configure log levels
- Set up email notifications

## Plugin Dependencies

### Required Dependencies
- **Meta Box**: Required for custom fields and meta data management
  - Must be installed and activated for core functionality

### Optional Dependencies
- **EmbedPress**: Enhances media embedding capabilities
  - When installed and activated, enables advanced embedding features
  - Integration can be enabled/disabled in plugin settings
- **PDF Embedder**: Provides PDF document display functionality
  - When installed and activated, enables PDF embedding features
  - Integration can be enabled/disabled in plugin settings

### Dependency Management
The plugin includes smart dependency handling:
- Automatically detects plugin installation and activation status
- Prevents enabling features when required plugins are not ready
- Provides clear guidance on required actions (install/activate)
- Settings automatically sync with plugin status changes

## Settings

### Integration Settings
The plugin provides integration settings for optional dependencies:

1. **EmbedPress Integration**
   - Enables enhanced media embedding features
   - Requires EmbedPress plugin to be installed and activated
   - Settings will be disabled if EmbedPress is not ready

2. **PDF Embedder Integration**
   - Enables PDF document display features
   - Requires PDF Embedder plugin to be installed and activated
   - Settings will be disabled if PDF Embedder is not ready

### Settings Behavior
- Settings are automatically validated against plugin availability
- Integration options are disabled when plugins are not ready
- Clear messages indicate required actions for each integration
- Settings automatically update when plugin status changes

## Development

### Local Development Setup
```bash
# Clone the repository
git clone https://github.com/TrevorLowing/WPCustomContent.git

# Install dependencies
composer install

# Run tests
composer test
```

### Testing
```powershell
# Deploy for testing
.\deploy-test.ps1

# Deploy without version increment
.\deploy-test.ps1 -NoVersionIncrement
```

### Version Management
- Base version defined in plugin file
- Test versions follow format: `{base_version}-test.{number}`
- Version tracking in `version.json`

## Documentation

### Class Structure
- `Plugin`: Main plugin initialization
- `Admin\Settings`: Plugin settings management
- `Admin\SystemStatus`: System diagnostics
- `Logger\Logger`: Logging functionality
- `Notifications\EmailNotifier`: Email notifications
- `PostTypes\ContentPostType`: Content management

### Hooks and Filters
```php
// Add custom meta box
add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);

// Content display filter
add_filter('the_content', [$this, 'display_content']);

// Bricks Builder integration
add_filter('bricks/builder/supported_post_types', [$this, 'add_bricks_post_type']);
```

### API Integration
```php
// GPT API example
$response = wp_remote_post('https://api.gpt-trainer.com/analyze', [
    'headers' => [
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json'
    ],
    'body' => json_encode($data)
]);
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Credits

- Author: Trevor Lowing
- Company: Varry LLC
- Contributors: [List of contributors]

## Changelog

### 1.0.0
- Initial release
- Core functionality implementation
- Logging system
- Email notifications
- System diagnostics

## Roadmap

- [ ] Enhanced AI integrations
- [ ] Advanced content analysis
- [ ] More third-party plugin support
- [ ] Performance optimizations
- [ ] Expanded workflow automation

## Known Issues

- Rate limiting with GPT API
- Large log file management
- Cross-browser testing ongoing
