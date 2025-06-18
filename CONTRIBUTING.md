# Contributing to Amazon PAAPI 5.0 PHP SDK
Thank you for considering contributing to the Amazon Product Advertising API 5.0 PHP SDK! We welcome contributions from the community to improve this lightweight, modern PHP library. This document outlines the process for contributing, including reporting issues, submitting pull requests, and following coding standards.
## Getting Started

* Fork the Repository:

Fork the repository on GitHub by clicking the Fork button at https://github.com/yourusername/amazon-paapi5-php-sdk.
Clone your fork locally:
```sh
git clone https://github.com/yourusername/amazon-paapi5-php-sdk.git
cd amazon-paapi5-php-sdk
```



* Install Dependencies:

Ensure you have PHP 7.4+ and Composer installed.
Install dependencies:
```sh
composer install
```



* Set Up Your Environment:

Configure your GitHub fork to sync with the upstream repository:
```sh
git remote add upstream https://github.com/yourusername/amazon-paapi5-php-sdk.git
```




## How to Contribute
### Reporting Issues

* Use the GitHub Issues tab to report bugs or suggest features.
* Provide a clear title and description, including:
  * Steps to reproduce the issue.
  * Expected and actual behavior.
  * PHP version and SDK version (e.g., v1.0.0).
  * Relevant code snippets or error messages.


* Check for existing issues to avoid duplicates.

### Submitting Pull Requests

#### 1. Create a Branch:

* Create a new branch for your changes:
```sh
git checkout -b feature/your-feature-name
```

#### 2. Make Changes:

* Follow the coding standards below.
* Update or add tests in the tests/ directory to cover your changes.
* Ensure tests pass:
```sh
vendor/bin/phpunit
```

#### 3. Commit Changes:

* Write clear, concise commit messages (e.g., Add support for new marketplace).
* Commit your changes:
```sh
git commit -m "Your commit message"
```
#### 4. Push and Create Pull Request:

* Push your branch to your fork:
```sh
git push origin feature/your-feature-name
```
* Go to your fork on GitHub and click New pull request.
* Select your branch and target the main branch of the upstream repository.
* Provide a detailed description of your changes, referencing any related issues (e.g., Fixes #123).


#### 5. CI and Review:

* The CI pipeline (.github/workflows/ci.yml) will run PHPUnit tests on PHP 7.4, 8.0, and 8.1.
* Address any test failures or reviewer feedback.
* Your pull request will be reviewed and merged if it meets the project’s standards.



## Coding Standards

* PHP Version: Target PHP 7.4+.
* Style: Follow PSR-12 coding standards.
   * Use declare(strict_types=1) for strict typing.
   * Run composer install to ensure dependencies align with composer.json.


* Tests: Add or update tests in tests/ using PHPUnit (^9.5).
   * Aim for high test coverage, especially for new features or bug fixes.


* Documentation:
   * Update README.md for new features or changes to usage.
   * Add PHPDoc comments for new classes, methods, or parameters.


* Dependencies: Avoid adding new dependencies unless necessary. Discuss in an issue first.

## Development Guidelines

* Keep It Lightweight: The SDK is ~150 KB with minimal dependencies (GuzzleHttp, ext-curl). Ensure changes maintain performance and modularity.
* Test Locally: Run vendor/bin/phpunit before submitting a pull request.
* Marketplace Support: Ensure changes work across all supported marketplaces (US, UK, JP, DE, FR, CA, AU, IN).
* Security: Follow security best practices, such as avoiding hardcoded credentials and ensuring HTTPS usage.

## Community

* Join discussions in the Issues or Discussions tabs.
* Contact the maintainer (@yourgithubusername) for questions or clarification.

Thank you for helping improve the Amazon PAAPI 5.0 PHP SDK!
