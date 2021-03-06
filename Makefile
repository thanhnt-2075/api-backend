
DOCKER_IMAGE=reconmap

.PHONY: prepare
prepare: build
	docker-compose run --rm -w /var/www/webapp --entrypoint composer svc install

.PHONY: build
build:
	docker-compose build

.PHONY: tests
tests: start
	docker-compose run --rm -w /var/www/webapp --entrypoint ./run-tests.sh svc

.PHONY: security-tests
security-tests:
	docker-compose run --rm -w /var/www/webapp --entrypoint composer svc require sensiolabs/security-checker
	docker-compose run --rm -w /var/www/webapp --entrypoint ./vendor/bin/security-checker svc security:check

.PHONY: db-reset
db-reset:
	cat $(wildcard docker/database/initdb.d/*.sql) | docker container exec -i $(shell docker-compose ps -q db) mysql -uroot -preconmuppet reconmap
	
.PHONY: start
start:
	docker-compose up -d

.PHONY: stop
stop:
	docker-compose stop

.PHONY: clean
clean: stop
	docker-compose down -v --remove-orphans
	docker-compose rm
	rm -rf {data-mysql,data-redis} 

.PHONY: runswagger
runswagger:
	docker run -p 80:8080 -e SWAGGER_JSON=/local/openapi.yaml -v $(PWD):/local swaggerapi/swagger-ui

.PHONY: generateapidocs
generateapidocs:
	docker run --rm -v $(PWD):/local swaggerapi/swagger-codegen-cli-v3 generate -i /local/openapi.yaml -l dynamic-html -o /local/apidocs

.PHONY: api-shell
api-shell:
	@docker-compose exec -w /var/www/webapp svc bash

.PHONY: db-shell
db-shell:
	@docker-compose exec db mysql --silent -uroot -preconmuppet reconmap

.PHONY: redis-shell
redis-shell:
	@docker-compose exec redis redis-cli

