# WPCustomContent Plugin Conventions

## Namespaces
- Root namespace: `WPCustomContent`
- Sub-namespaces use underscores: `WPCustomContent\Includes\Post_Types`
- All namespace parts are PascalCase with underscores between words

## Class Names
- PascalCase with underscores between words: `Content_Collection`, `Content_Article`
- Match the filename without the `class-` prefix
- Example: `class-content-collection.php` contains class `Content_Collection`

## File & Directory Names
- Lowercase with hyphens
- Class files prefixed with `class-`
- Directories: `includes`, `post-types`, `admin`
- Files: `class-content-collection.php`, `class-content-article.php`, `class-content-category.php`

## Directory Structure
```
piper-content/
├── admin/
├── includes/
│   ├── post-types/
│   │   ├── class-content-collection.php
│   │   ├── class-content-article.php
│   │   └── class-content-category.php
│   └── core/
└── piper-content.php
```

## Example Usage
```php
namespace WPCustomContent\Includes\Post_Types;

class Content_Collection {
    // Class implementation
}

// Using the class
use WPCustomContent\Includes\Post_Types\Content_Collection;
