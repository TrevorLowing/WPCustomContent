# WordPress Custom Content Plugin - Coding Conventions

## Project Structure

### Directory Organization
```
wp-custom-content/
├── admin/                  # Admin-specific assets
│   ├── css/               # Admin styles
│   └── js/                # Admin scripts
├── build/                  # Build configuration and output
├── docs/                  # Documentation files
├── languages/            # Translation files
├── public/               # Public-facing assets
│   ├── css/              # Public styles
│   └── js/               # Public scripts
├── src/                  # PHP classes (PSR-4)
│   ├── Admin/            # Admin-specific classes
│   ├── Frontend/         # Frontend-specific classes
│   └── PostTypes/        # Post type definitions
├── tests/                # Test files
│   ├── Unit/             # Unit tests
│   └── Integration/      # Integration tests
└── vendor/               # Composer dependencies
```

## General Guidelines

1. Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
2. Use PHP 7.4+ compatible code
3. Maintain WordPress compatibility with version 5.8 and above
4. Use modern PHP features appropriately
5. Follow PSR-4 autoloading standards
6. Implement proper error handling

## Naming Conventions

### Namespaces
- Root namespace: `WPCustomContent`
- Use PSR-4 compliant structure
- Match directory structure
```php
namespace WPCustomContent\PostTypes;
namespace WPCustomContent\Admin;
```

### Classes
- Use PascalCase
- One class per file
- Meaningful and descriptive names
```php
class ContentPostType
class AdminSettings
class FrontendDisplay
```

### Interfaces
- Use PascalCase
- Suffix with Interface
```php
interface ContentHandlerInterface
interface FileStorageInterface
```

### Traits
- Use PascalCase
- Suffix with Trait
```php
trait FilesystemTrait
trait MetaBoxTrait
```

### Functions
- Prefix all functions with `wpcc_` to avoid namespace conflicts
- Use lowercase letters and underscores
- Be descriptive and clear about the function's purpose
```php
// Good
wpcc_register_content_post_type()
wpcc_handle_remote_file()

// Bad
WPCCRegisterContent()
handle_file()
```

### Variables
- Use lowercase letters and underscores
- Be descriptive with variable names
```php
// Good
$post_meta = get_post_meta($post_id);
$remote_file_url = sanitize_url($url);

// Bad
$meta = get_post_meta($id);
$url = sanitize_url($u);
```

### Constants
- Use uppercase letters and underscores
- Include plugin prefix
```php
// Good
WP_CUSTOM_CONTENT_VERSION
WP_CUSTOM_CONTENT_PLUGIN_DIR

// Bad
VERSION
PLUGIN_PATH
```

## Code Organization

### Class Structure
1. Constants
2. Properties
3. Constructor
4. Public methods
5. Protected methods
6. Private methods
7. Utility methods

### Method Organization
- Keep methods focused and single-purpose
- Group related methods together
- Use proper access modifiers
- Follow SOLID principles

## Documentation

### File Headers
```php
/**
 * Class name and description
 *
 * @package WPCustomContent
 * @subpackage PostTypes
 * @since 1.0.0
 */
```

### Class Documentation
```php
/**
 * Handles content post type registration and management.
 *
 * @package WPCustomContent\PostTypes
 * @since 1.0.0
 */
class ContentPostType {
```

### Method Documentation
```php
/**
 * Handle remote file download and storage
 *
 * @since 1.0.0
 * @param string $url     The remote file URL
 * @param int    $post_id Optional post ID to attach file to
 * @return int|WP_Error  Attachment ID on success, WP_Error on failure
 */
```

## Security

### Input Validation
1. Always validate and sanitize input
2. Use WordPress security functions:
   ```php
   sanitize_text_field()
   wp_kses()
   esc_html()
   esc_url()
   ```

### Capability Checks
```php
// Check user capabilities
if (!current_user_can('edit_post', $post_id)) {
    return new WP_Error('insufficient_permissions', __('You do not have permission to edit this post.', 'wp-custom-content'));
}
```

### Nonce Verification
```php
// Add nonce to forms
wp_nonce_field('wpcc_action', 'wpcc_nonce');

// Verify nonce
if (!wp_verify_nonce($_POST['wpcc_nonce'], 'wpcc_action')) {
    wp_die(__('Security check failed', 'wp-custom-content'));
}
```

## Performance

### Database Queries
1. Use WordPress caching functions
   ```php
   wp_cache_get()
   wp_cache_set()
   wp_cache_delete()
   ```

2. Optimize database queries
   ```php
   // Good
   $posts = get_posts([
       'post_type' => 'content',
       'numberposts' => 10,
       'fields' => 'ids'
   ]);

   // Bad
   $posts = new WP_Query(['post_type' => 'content']);
   ```

### Asset Loading
1. Enqueue assets properly
   ```php
   wp_enqueue_style()
   wp_enqueue_script()
   ```

2. Use asset versioning
   ```php
   wp_enqueue_style(
       'wpcc-admin',
       WP_CUSTOM_CONTENT_PLUGIN_URL . 'admin/css/admin.css',
       [],
       WP_CUSTOM_CONTENT_VERSION
   );
   ```

## Version Control

1. Use semantic versioning (MAJOR.MINOR.PATCH)
2. Include meaningful commit messages
3. Document breaking changes in CHANGELOG.md
4. Follow GitFlow branching model
   - main: production-ready code
   - develop: latest development changes
   - feature/*: new features
   - hotfix/*: urgent fixes

## Testing

### Unit Tests
```php
class ContentPostTypeTest extends WP_UnitTestCase {
    public function test_register_post_type() {
        // Test code
    }
}
```

### Integration Tests
```php
class ContentIntegrationTest extends WP_Test_REST_TestCase {
    public function test_create_content() {
        // Test code
    }
}
