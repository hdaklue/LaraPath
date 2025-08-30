# LaraPath

[![Tests](https://github.com/hdaklue/LaraPath/workflows/Tests/badge.svg)](https://github.com/hdaklue/LaraPath/actions)
[![Latest Stable Version](https://poser.pugx.org/hdaklue/larapath/v/stable)](https://packagist.org/packages/hdaklue/larapath)
[![License](https://poser.pugx.org/hdaklue/larapath/license)](https://packagist.org/packages/hdaklue/larapath)

![Lara Path](img.png)
A secure, fluent path builder for PHP with sanitization strategies and Laravel 11-12 integration.

## Features

- 🔒 **Security First**: Built-in protection against directory traversal attacks with comprehensive validation
- 🎯 **Fluent API**: Clean, readable path building with method chaining
- 🔧 **Sanitization Strategies**: Multiple strategies for different use cases (hash, slug, snake_case, timestamp)
- 📁 **Extension Preservation**: Automatic file extension preservation during sanitization
- 🚀 **Immutable Operations**: Thread-safe path building with no side effects
- 🏗️ **Strategy Pattern**: Extensible sanitization system with automatic validation
- 🎨 **Type Safety**: Full type hints and IDE autocompletion
- 📦 **Laravel Integration**: Optional Laravel Storage facade integration
- ⚡ **Error Handling**: Comprehensive exception handling with specific error types

## Installation

```bash
composer require hdaklue/larapath
```

## Quick Start

```php
use Hdaklue\PathBuilder\PathBuilder;
use Hdaklue\PathBuilder\Enums\SanitizationStrategy;
// Or use the facade in Laravel
use LaraPath;

// Basic usage (static)
$path = PathBuilder::base('uploads')
    ->add('images')
    ->add('avatar.jpg')
    ->toString(); // "uploads/images/avatar.jpg"

// Laravel facade usage (auto-registered)
$path = LaraPath::base('uploads')
    ->add('images') 
    ->add('avatar.jpg')
    ->toString();

// With sanitization strategies
$path = PathBuilder::base('uploads')
    ->add('user-123', SanitizationStrategy::HASHED)
    ->add('My File.jpg', SanitizationStrategy::SLUG)
    ->validate()
    ->toString(); // "uploads/a665a45920422f9d417e4867efdc4fb8/my-file.jpg"
```

## Real-World Use Cases

For detailed examples of how LaraPath solves critical problems in multi-tenant Laravel applications, see [Real World Problems](real-world-problems.md).

The guide covers 5 essential scenarios:
- Multi-tenant storage organization with privacy protection
- Database-friendly storage strategies across multiple disks  
- File naming conflicts and data loss prevention
- Cross-platform filename sanitization and migration
- Team-wide consistency and standardization

Perfect for understanding LaraPath's value in complex Laravel applications.

## Usage Examples

### Sanitization Strategies

LaraPath automatically preserves file extensions during sanitization, ensuring your files maintain their proper types.

```php
// Hash sensitive data (preserves extensions)
$path = PathBuilder::base('storage')
    ->addFile('user@email.com', SanitizationStrategy::HASHED)
    ->toString(); // "storage/d549c81aa88e6e76e1f4c141aaae4c6e.com"

// Create URL-friendly names (preserves extensions)
$path = PathBuilder::base('uploads')
    ->addFile('My Amazing File!.pdf', SanitizationStrategy::SLUG)
    ->toString(); // "uploads/my-amazing-file.pdf"

// Convert to snake_case (preserves extensions)
$path = PathBuilder::base('files')
    ->addFile('CamelCase Name.docx', SanitizationStrategy::SNAKE)
    ->toString(); // "files/camel_case_name.docx"

// Add timestamps for uniqueness (preserves extensions)
$path = PathBuilder::base('temp')
    ->addFile('session.log', SanitizationStrategy::TIMESTAMP)
    ->toString(); // "temp/session_1640995200.log"

// Directory names (no extensions to preserve)
$path = PathBuilder::base('uploads')
    ->add('User Documents', SanitizationStrategy::SLUG)
    ->toString(); // "uploads/user-documents"
```

### File Extension Preservation

All sanitization strategies automatically detect and preserve file extensions:

```php
// Complex filename with special characters
$path = PathBuilder::base('documents')
    ->addFile('My Complex File Name!@#.pdf', SanitizationStrategy::SLUG)
    ->toString(); // "documents/my-complex-file-name.pdf"

// Multiple dots in filename - preserves only the last extension
$path = PathBuilder::base('archives')
    ->addFile('backup.2023.tar.gz', SanitizationStrategy::SLUG)
    ->toString(); // "archives/backup-2023-tar.gz"

// Files without extensions work normally
$path = PathBuilder::base('configs')
    ->addFile('README', SanitizationStrategy::SLUG)
    ->toString(); // "configs/readme"

// Hidden files (starting with dot)
$path = PathBuilder::base('configs')
    ->addFile('.env.example', SanitizationStrategy::SLUG)
    ->toString(); // "configs/env.example"
```

### Path Operations

```php
$builder = PathBuilder::base('files/video.mp4');

// Extract path components
$extension = $builder->getExtension(); // "mp4"
$filename = $builder->getFilename(); // "video.mp4"
$filenameWithoutExt = $builder->getFilenameWithoutExtension(); // "video"
$directory = $builder->getDirectoryPath(); // "files"

// Modify paths
$newPath = $builder->replaceExtension('webm')->toString(); // "files/video.webm"
```

### Laravel Integration

LaraPath is automatically registered in Laravel applications with facade support and container binding.

```php
// Using the facade (recommended for Laravel)
use LaraPath;

$exists = LaraPath::base('uploads')
    ->add('avatar.jpg')
    ->exists('public'); // Uses Storage::disk('public')->exists()

// Using container binding
$builder = app('larapath');
$size = $builder->base('files')
    ->add('document.pdf')
    ->size(); // Uses Storage::size()

// Using static methods (framework-agnostic)
use Hdaklue\PathBuilder\PathBuilder;

$url = PathBuilder::base('images')
    ->add('logo.png')
    ->url('public'); // Uses Storage::disk('public')->url()

// Delete file
$deleted = LaraPath::base('temp')
    ->add('cache.tmp')
    ->delete(); // Uses Storage::delete()
```

### Validation and Security

LaraPath provides comprehensive security and validation features:

```php
// Automatic path validation
$path = PathBuilder::base('uploads')
    ->add('../../../etc/passwd') // Dangerous path
    ->validate() // Throws UnsafePathException
    ->toString();

// Manual safety check
$isSafe = PathBuilder::isSafe('uploads/../dangerous/path'); // false
$isSafe = PathBuilder::isSafe('uploads/safe/file.txt'); // true

// File existence validation
$path = PathBuilder::base('uploads')
    ->addFile('document.pdf')
    ->mustExist('public') // Throws PathNotFoundException if file doesn't exist
    ->toString();

$path = PathBuilder::base('uploads')
    ->addFile('new-file.pdf')
    ->mustNotExist('public') // Throws PathAlreadyExistsException if file exists
    ->toString();
```

### Error Handling

LaraPath throws specific exceptions for different error conditions:

```php
use Hdaklue\PathBuilder\Exceptions\UnsafePathException;
use Hdaklue\PathBuilder\Exceptions\PathNotFoundException;
use Hdaklue\PathBuilder\Exceptions\PathAlreadyExistsException;
use Hdaklue\PathBuilder\Exceptions\InvalidSanitizationStrategyException;

try {
    $path = PathBuilder::base('../dangerous')
        ->addFile('file.txt')
        ->validate();
} catch (UnsafePathException $e) {
    // Handle directory traversal attempt
    echo "Unsafe path detected: " . $e->getMessage();
}

try {
    $path = PathBuilder::base('uploads')
        ->addFile('missing.txt')
        ->mustExist('local');
} catch (PathNotFoundException $e) {
    // Handle missing file
    echo "File not found: " . $e->getMessage();
}

try {
    $path = PathBuilder::base('uploads')
        ->add('input', 'InvalidStrategy');
} catch (InvalidSanitizationStrategyException $e) {
    // Handle invalid sanitization strategy
    echo "Invalid strategy: " . $e->getMessage();
}
```

### Custom Strategies

Create custom sanitization strategies by implementing the `SanitizationStrategyContract`:

```php
use Hdaklue\PathBuilder\Contracts\SanitizationStrategyContract;

class UuidStrategy implements SanitizationStrategyContract
{
    public static function apply(string $input): string
    {
        return \Str::uuid()->toString();
    }
}

// Use custom strategy
$path = PathBuilder::base('files')
    ->add('temp-file', UuidStrategy::class)
    ->toString(); // "files/550e8400-e29b-41d4-a716-446655440000"
```

### Strategy Validation

LaraPath automatically validates that custom strategies implement the required contract:

```php
// ✅ Valid strategy - implements SanitizationStrategyContract
$path = PathBuilder::base('files')
    ->add('input', MyCustomStrategy::class)
    ->toString();

// ❌ Invalid strategy - throws InvalidSanitizationStrategyException
$path = PathBuilder::base('files')
    ->add('input', 'NonExistentStrategy')
    ->toString(); // Exception: Strategy class NonExistentStrategy not found

// ❌ Invalid strategy - missing contract implementation
$path = PathBuilder::base('files')
    ->add('input', \stdClass::class)
    ->toString(); // Exception: Strategy class stdClass must implement SanitizationStrategyContract interface
```

## API Reference

### PathBuilder Methods

- `PathBuilder::base(string $path, ?SanitizationStrategy $strategy = null): self`
- `add(string $name, ?SanitizationStrategy $strategy = null): self`
- `addFile(string $filename, ?SanitizationStrategy $strategy = null): self`
- `addTimestampedDir(): self`
- `addHashedDir(string $input, string $algorithm = 'md5'): self`
- `replaceExtension(string $newExt): self`
- `getExtension(): string`
- `getFilename(): string`
- `getFilenameWithoutExtension(): string`
- `getDirectoryPath(): string`
- `ensureTrailing(): self`
- `removeTrailing(): self`
- `validate(): self`
- `toString(): string`

### Laravel Integration Methods

- `mustExist(string $disk = 'local'): self`
- `mustNotExist(string $disk = 'local'): self`
- `exists(string $disk = 'local'): bool`
- `size(string $disk = 'local'): int`
- `url(string $disk = 'local'): string`
- `delete(string $disk = 'local'): bool`

### Static Utility Methods

- `PathBuilder::build(array $segments): string`
- `PathBuilder::join(string ...$segments): string`
- `PathBuilder::normalize(string $path): string`
- `PathBuilder::isSafe(string $path): bool`
- `PathBuilder::buildRelativePath(string $absolutePath, string $basePath): string`

## Available Strategies

All strategies automatically preserve file extensions when present:

- `SanitizationStrategy::HASHED` - MD5 hash of input (preserves extensions: `user.txt` → `hash.txt`)
- `SanitizationStrategy::SLUG` - URL-friendly slug (preserves extensions: `My File!.pdf` → `my-file.pdf`)
- `SanitizationStrategy::SNAKE` - snake_case conversion (preserves extensions: `CamelCase.docx` → `camel_case.docx`)
- `SanitizationStrategy::TIMESTAMP` - Appends Unix timestamp (preserves extensions: `file.log` → `file_1640995200.log`)

## Requirements

- PHP ^8.2
- illuminate/support ^11.0|^12.0 (for Laravel integration)

## Testing

```bash
composer test
```

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hassan@daklue.com instead of using the issue tracker.