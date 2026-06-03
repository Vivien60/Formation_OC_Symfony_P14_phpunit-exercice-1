db-test:
	php bin/console doctrine:database:drop -f --if-exists --env=test
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:migrations:migrate -n --env=test
	php bin/console doctrine:fixtures:load -n --purge-with-truncate --env=test

test: db-test
	php bin/phpunit --testdox --coverage-html "public/test-coverage/"  --filter tests

test-unit: db-test
	php bin/phpunit --testdox --coverage-html "public/test-coverage/"  --filter Unit

test-config-github-actions:
	 time act --pull=false -j symfony-tests --container-options "--user 0"