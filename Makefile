
composer.phar:
	@curl -s http://getcomposer.org/installer | php

install: composer.phar
	@echo "Installing..."
	@php composer.phar install --dev

update:
	@echo "Updating..."
	@php composer.phar self-update
	@php composer.phar update

test:
	@./vendor/atoum/atoum/bin/atoum --test-all

clean:
	@echo "Cleaning..."
	@rm composer.phar
	@rm composer.lock
