db-test:
	symfony console doctrine:database:drop -f --if-exists --env=test
	symfony console doctrine:database:create --env=test
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --purge-with-truncate --env=test

test: db-test
	symfony php bin/phpunit --testdox --coverage-html "public/test-coverage/"  --filter tests 2>/dev/null

test-unit: db-test
	symfony php bin/phpunit --testdox --coverage-html "public/test-coverage/"  --filter Unit 2>/dev/null