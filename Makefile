# Setup ————————————————————————————————————————————————————————————————————————
SHELL         = bash
PROJECT       = flysystem-sharepoint
USER 		  = $(shell id -u)
GROUP		  = $(shell id -g)
PHP           = php
COMPOSER      = composer
DOCKER_ENV    = USER_ID=$(USER) GROUP_ID=$(GROUP)
DOCKER		  = docker-compose
PCOV          = -dpcov.enabled=1 -dpcov.directory=. -dpcov.exclude="~vendor~"
PHPUNIT       = XDEBUG_MODE=off $(PHP) $(PCOV) vendor/bin/phpunit -d memory_limit=-1 --stop-on-failure --testdox
PHPUNIT_CI    = XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit -d memory_limit=-1 --stop-on-failure --testdox
PHPQA		  = $(DOCKER_ENV) $(DOCKER) run --rm phpqa
.DEFAULT_GOAL = help
# Forcing run of not-file-related targets
.PHONY: tests

## —— IT Makefile 🍺 ——————————————————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Project 🛠———————————————————————————————————————————————————————————————
install: composer-install ## Install requirements (php dependencies)

analyze: docker-phpqa-up php-cpd php-cs php-stan php-insights ## Run static analysis tools
analysis: analyze ## Run static analysis tools (alias analyze)

fix: docker-phpqa-up php-cbf php-insights-fix ## Fix coding standards

## —— Composer 🧙‍♂️ ————————————————————————————————————————————————————————————
vendor/autoload.php: composer.lock
	$(COMPOSER) install --no-progress --prefer-dist --optimize-autoloader

composer-install: vendor/autoload.php  ## Install vendors according to the current composer.lock file

composer-update: ./symfony composer.json ## Update vendors according to the composer.json file
	$(COMPOSER) update

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
docker-phpqa-up: docker-compose.yml ## Start the phpqa container
	$(DOCKER_ENV) $(DOCKER) up -d phpqa

## —— CI: Tests ✅ —————————————————————————————————————————————————————————————
tests: ## Run the PHPUnit tests
	$(PHPUNIT)

test: phpunit.xml.dist ## Run tests with optional suite and filter [eg: make test testsuite=unit or make test filter=testRedirectToLogin]
	@$(eval testsuite ?= 'all')
	@$(eval filter ?= '.')
	@$(PHPUNIT) --testsuite=$(testsuite) --filter=$(filter)

test-ci: composer-install ## Run the PHPUnit tests
	$(PHPUNIT_CI) --log-junit=./tmp/coverage/tests.xml --coverage-cobertura=./tmp/coverage/coverage.xml

## —— CI: Coding standards ✨ ——————————————————————————————————————————————————
php-cs: ## Run php_codesniffer with PSR12 standard
	$(PHPQA) phpcs -v --standard=PSR12 src/

php-cbf: ## Run PHP Code Beautifier and Fixer
	$(PHPQA) phpcbf -v --standard=PSR12 src/

php-mess: ## Run PHP Mess Detector
	$(PHPQA) phpmd src/ ansi ./.phpmd.xml

php-stan: ## Run PHPStan
	$(PHPQA) phpstan analyse -c phpstan.neon --no-interaction src/

php-insights: ## Run PHP Insights
	$(PHPQA) phpinsights -n --ansi

php-insights-fix: ## Run PHP Insights
	$(PHPQA) phpinsights -n --fix

php-cpd: ## Run PHP Copy/Paste Detector
	$(PHPQA) phpcpd --min-lines 30 src/
