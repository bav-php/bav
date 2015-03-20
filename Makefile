# @configure_input@

doc: composer-update
	./vendor/bin/apigen.php -d api -s $$(grep -Rl api vendor/malkusch/bav/classes/ | tr '\n' ',' | head -c -1)

composer-clean:
	rm -rf vendor/bin/bav-* vendor/malkusch/bav/ composer.lock

composer-update: composer-clean
	composer.phar update

