# Contributing

We love contributions from everyone. Here are the guidelines to follow:

## Issues

- Feel free to open an issue for any bugs or feature requests
- Please search existing issues before creating a new one
- Provide as much relevant information as possible
- Include steps to reproduce if reporting a bug

## Pull Requests

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Write tests for your changes
4. Ensure all tests pass (`composer test`)
5. Commit your changes (`git commit -m 'Add some amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Development Setup

```bash
# Clone your fork
git clone git@github.com:your-username/laravel-sms.git

# Install dependencies
composer install

# Run tests
composer test
```

### Code Style

This package follows the PSR-12 coding standard. Please ensure your code follows this standard:

```bash
# Fix code style
composer format
```

### Testing

All new features or bug fixes should include tests:

```bash
# Run test suite
composer test

# Generate coverage report
composer test-coverage
```

## Security Vulnerabilities

If you discover a security vulnerability, please send an email to support@messaging-service.co.tz. All security vulnerabilities will be promptly addressed.

## License

By contributing to this project, you agree that your contributions will be licensed under its MIT license. 