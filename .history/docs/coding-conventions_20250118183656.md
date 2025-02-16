# PiperPrivacy Plugin Conventions

## Namespaces
- Root namespace: `PiperPrivacy`
- Sub-namespaces use underscores: `PiperPrivacy\Includes\Post_Types`
- All namespace parts are PascalCase with underscores between words

## Class Names
- PascalCase with underscores between words: `Privacy_Collection`, `Privacy_Threshold`
- Match the filename without the `class-` prefix
- Example: `class-privacy-collection.php` contains class `Privacy_Collection`

## File & Directory Names
- Lowercase with hyphens
- Class files prefixed with `class-`
- Directories: `includes`, `post-types`, `admin`
- Files: `class-privacy-collection.php`, `class-privacy-threshold.php`

## Directory Structure
```
piper-privacy/
├── admin/
├── includes/
│   ├── post-types/
│   │   ├── class-privacy-collection.php
│   │   ├── class-privacy-threshold.php
│   │   └── class-privacy-impact.php
│   └── core/
└── piper-privacy.php
```

## Example Usage
```php
namespace PiperPrivacy\Includes\Post_Types;

class Privacy_Collection {
    // Class implementation
}

// Using the class
use PiperPrivacy\Includes\Post_Types\Privacy_Collection;
```
