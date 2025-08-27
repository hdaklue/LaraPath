# Contributing

Thank you for considering contributing to PathBuilder! This document outlines the process for contributing to this project.

## Development Setup

1. Fork the repository
2. Clone your fork locally
3. Install dependencies: `composer install`
4. Create a new branch: `git checkout -b feature/your-feature-name`

## Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse

# Fix code formatting
composer format
```

## Guidelines

### Code Style
- Follow PSR-12 coding standards
- Use Laravel Pint for automatic formatting: `composer format`
- All public methods must have type hints and return types
- Add PHPDoc blocks for complex methods

### Testing
- Write tests for all new functionality
- Maintain 100% test coverage where possible
- Use descriptive test method names that explain the behavior being tested
- Group related tests in the same test class

### Documentation
- Update README.md if you add new features
- Add examples for new functionality
- Keep docblocks up to date
- Comment complex logic

### Pull Request Process

1. Ensure all tests pass: `composer test`
2. Ensure code passes static analysis: `composer analyse`
3. Format code: `composer format`
4. Update documentation if necessary
5. Create a pull request with a clear description of changes
6. Link to any relevant issues

### Commit Messages

Use conventional commit format:
- `feat:` for new features
- `fix:` for bug fixes
- `docs:` for documentation changes
- `test:` for test changes
- `refactor:` for code refactoring
- `style:` for formatting changes

Example: `feat: add custom sanitization strategy support`

## Security

If you discover a security vulnerability, please send an email to hdaklue@example.com instead of opening a public issue.

## Questions?

Feel free to open an issue for any questions or discussions about contributing.

Thank you for your contribution! 🎉