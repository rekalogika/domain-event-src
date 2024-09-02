PHP=php

.PHONY: test
test: clean phpstan psalm monorepo-validate lint-container phpunit

.PHONY: clean
clean:
	rm -rf var

.PHONY: monorepo
monorepo: validate monorepo-merge

.PHONY: monorepo-merge
monorepo-merge:
	$(PHP) vendor/bin/monorepo-builder merge

.PHONY: validate
monorepo-validate:
	$(PHP) vendor/bin/monorepo-builder validate

.PHONY: lint-container
lint-container:
	$(PHP) tests/bin/console lint:container

.PHONY: phpstan
phpstan:
	$(PHP) vendor/bin/phpstan analyse

.PHONY: psalm
psalm:
	$(PHP) vendor/bin/psalm

.PHONY: phpunit
phpunit: clean
	$(eval c ?=)
	$(PHP) vendor/bin/phpunit $(c)

.PHONY: php-cs-fixer
php-cs-fixer: tools/php-cs-fixer
	$(PHP) $< fix --config=.php-cs-fixer.dist.php --verbose --allow-risky=yes

.PHONY: tools/php-cs-fixer
tools/php-cs-fixer:
	phive install php-cs-fixer

.PHONY: dump
dump:
	$(PHP) tests/bin/console server:dump

.PHONY: rector
rector:
	$(PHP) vendor/bin/rector process > rector.log
	make php-cs-fixer