# Contributing to the project

Huge thanks for the time you're dedicating to helping maintain this project 😉 !

This project supports multiple **PHP** and **Symfony** versions.  
Don't worry, everything is explained below, and **helpers** are provided to ease this workflow.

### Supported versions

| Component   | Supported versions |
| ----------- | ------------------ |
| **Symfony** | 5.4 → 7            |
| **PHP**     | >= 8.2             |

### Prerequisites

- **PHP >= 8.2** for local runs, depending on the **Symfony** version you are testing against.

If you want to avoid **PHP** and **Symfony** versions environment configuration issues, the following tools will be required to automate formatting and testing without additional configuration:

- [**Docker**](https://docs.docker.com/get-started/get-docker/)
- [**act**](https://nektosact.com/installation/index.html)

### Switching Symfony versions

To ensure compatibility, code changes should be tested against the supported **Symfony** versions.  
**Symfony Flex** offers a clean solution for managing dependencies versions without **PHP** environment configuration issues. 

#### 1. Installing Symfony Flex
You can install the plugin globally using the following command:

```bash
composer global config --no-plugins allow-plugins.symfony/flex true
composer global require symfony/flex:^2.0
```

#### 2. Switching to a different Symfony version

Use the following commands to switch to another **Symfony** version:

**Linux / macOS / Bash:**

```bash
SYMFONY_REQUIRE=<symfony_semver_version> composer update --prefer-stable

# Symfony 5.4 example
# SYMFONY_REQUIRE=^5.4 composer update --prefer-stable
```

**Windows (PowerShell):**

```powershell
$env:SYMFONY_REQUIRE = "<symfony_semver_version>"; composer update --prefer-stable

# Symfony 5.4 example
# $env:SYMFONY_REQUIRE = "^5.4"; composer update --prefer-stable
```

## 1. How to contribute
* [**Fork**](https://github.com/Zemasterkrom/zmkr-cloudflare-turnstile-bundle/fork) this project. Since development is mainly done on the **development** branch, please **uncheck** "Copy the `main` branch only".
* **Checkout** the **development** branch of your forked repository.
* **Make changes** on the **development** branch or create a new **feature** branch.
* Once your changes are finalized and tested, **open a Pull Request** from your branch against the **development** branch of this repository.
* Before your code is merged, **CI checks will run** to validate your changes.
* Your code will then be **reviewed** and merged upon approval.

## 2. Code formatting

To ensure consistent code formatting across the codebase, this project uses [**PHP CS Fixer**](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) to format the code following **PHP Coding Standards** and **Symfony conventions**.

Run the formatter using the lowest supported versions (**PHP 8.2**, **Symfony 5.4**):

```bash
# Format the PHP files using PHP CS Fixer
composer php-cs-fixer fix
```

```bash
# Check the PHP files formatting using PHP CS Fixer
composer php-cs-fixer check
```

To avoid **PHP** and **Symfony** configuration issues, you can use the **Docker** wrapper:

```bash
# Format the PHP files using PHP CS Fixer
composer php-cs-fixer:docker fix
```

```bash
# Check the PHP files formatting using PHP CS Fixer
composer php-cs-fixer:docker check
```

## 3. Testing

The project is tested with **code tests** (**Unit**, **Integration**, **Functional**) and **static analysis tests**.  
It would be appreciated if you could add **Unit** tests for your changes.

Run **PHPUnit** for **code tests**:

```bash
composer phpunit
```

Run **PHPStan** for **static analysis tests**:

```bash
composer phpstan
```

### Testing the entire workflow without configuration
You can use [**act**](https://github.com/nektos/act) to run the full repository test workflow across supported **PHP/Symfony** versions without extra configuration:

```bash
act --artifact-server-path=$PWD/.artifacts --artifact-server-addr 127.0.0.1
```

> [!TIP]
> Run [code formatting](#2-code-formatting) before running that command, otherwise **PHP CS Fixer** check will fail.
