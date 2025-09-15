# Sets the default goal to be used if no targets were specified on the command line
.DEFAULT_GOAL := help

ifneq ("$(wildcard .env)","")
include .env
export
endif

docker_path := $(shell realpath $(DOCKER_PATH))

uname_OS := $(shell uname -s)
user_UID := $(if $(USER_UID_SHELL),$(USER_UID_SHELL),$(shell id -u))
user_GID := $(if $(USER_GID_SHELL),$(USER_GID_SHELL),$(shell id -g))
current_uid ?= "${user_UID}:${user_GID}"

ifeq ($(uname_OS),Darwin)
	user_UID := 1001
	user_GID := 1001
endif

# Handling environment variables
app_full_path := $(if $(APP_PATH_SHELL),$(APP_PATH_SHELL),$(APP_PATH))

# Passing the >_ options option
options := $(if $(options),$(options),--env-file $(docker_path)/.env)
# Passing the >_ up_options option
up_options := $(if $(up_options),$(up_options),--force-recreate --no-build --no-deps --detach)

# Passing the >_ project_name option
project_name := $(if $(project_name),$(project_name),$(COMPOSE_PROJECT_NAME))
version := $(if $(version),$(version),$(if $(CONTAINER_VERSION_SHELL),$(CONTAINER_VERSION_SHELL),$(CONTAINER_VERSION)))

project_name_and_version := $(if $(with_version),$(project_name)-$(version),$(project_name))

# Passing the >_ common_options option
common_options := $(if $(common_options),$(common_options),--compatibility --ansi=auto --project-name $(project_name_and_version))

# ==================================================================================== #
# HELPERS
# ==================================================================================== #

# internal functions
define message_failure
	"\033[1;31m ❌$(1)\033[0m"
endef

define message_success
	"\033[1;32m ✅$(1)\033[0m"
endef

define message_info
	"\033[0;34m❕$(1)\033[0m"
endef

# ==================================================================================== #
# DOCKER
# ==================================================================================== #

.PHONY: docker/config-env
docker/config-env:
	@cp -n .env.compose .env || true
	@sed -i "/^# PWD/c\PWD=$(shell pwd)" .env
	@sed -i "/^# APP_PATH/c\APP_PATH=$(shell dirname -- `pwd`)" .env
	@sed -i "/# USER_UID=.*/c\USER_UID=$(user_UID)" .env
	@sed -i "/# USER_GID=.*/c\USER_GID=$(user_GID)" .env
	@sed -i "/^# CURRENT_UID/c\CURRENT_UID=${current_uid}" .env
	@cp ./mysql/.env.container ./mysql/.env
	@cp ./php/app/.env.container ./php/app/.env
	@cp ./php/queue/.env.container ./php/queue/.env
	@cp ./php/scheduler/.env.container ./php/scheduler/.env
	@echo
	@echo -e $(call message_success, Run \`make docker/config-env\` successfully executed)

# make php/composer-install
.PHONY: php/composer-install
php/composer-install:
	@echo
	@echo -e $(call message_info, Installing PHP dependencies with Composer 🗂)
	@echo
	docker run \
		--rm \
		--tty \
		--interactive \
		--volume $(app_full_path):/app \
		--workdir /app \
		$(if $(options), $(options),) \
		composer:2.5 \
			install \
				--optimize-autoloader \
				--ignore-platform-reqs \
				--prefer-dist \
				--ansi \
				--no-dev \
				--no-interaction

.PHONY: docker/healthcheck
docker/healthcheck: $(eval SHELL:=/bin/bash)
	@timeout=90; counter=0; \
	printf "\n\033[3;33mEsperando healthcheck do container de \"$${container}\" = \"healthy\" ⏳ \033[0m\n" ; \
	until [[ "$$(docker container inspect -f '{{.State.Health.Status}}' $${container})" == "healthy" ]] ; do \
		printf '.' ; \
		if [[ $${timeout} -lt $${counter} ]]; then \
			printf "\n\033[1;31mERROR: Timed out waiting for \"$${container}\" to come up/healthy ❌\033[0m\n\n" ; \
			exit 1 ; \
		fi ; \
		\
		printf "\n\033[35mWaiting for \"$${container}\" to be ready/healthy ($${counter}/$${timeout}) ⏱ \033[0m\n" ; \
		sleep 5s; counter=$$((counter + 5)) ; \
	done
	@echo
	@echo -e $(call message_success, HEALTHCHECK do Container \"$${container}\" OK)

.PHONY: docker/app/build
docker/app/build:
	@echo
	@echo -e $(call message_info, Build an image APP... 🏗)
	@echo
	DOCKER_BUILDKIT=1 docker build \
		--progress=plain \
		--target main \
		--tag ${APP_DOCKER_IMAGE} \
		--file ${DOCKER_PHP_PATH}/Dockerfile \
		--build-arg USER_UID=${USER_UID} \
		--build-arg USER_GID=${USER_GID} \
		--build-arg DOCKER_FOLDER=${DOCKER_FOLDER} \
		--build-arg APP_DOCKER_REPO=${APP_DOCKER_REPO} \
	${APP_PATH}

.PHONY: docker/app/up
docker/app/up:
	@echo
	@echo -e $(call message_info, Running APP Container... 🚀)
	@echo
	$(MAKE) -f $(docker_path)/Makefile \
			--no-print-directory \
			docker/service/up \
			context="php/app" \
			scale=$(if $(num_scale),$(num_scale),$(if $(APP_NUM_SCALE),$(APP_NUM_SCALE),1)) \
			service=$(if $(scale_service),$(scale_service),app) \
			with_version=true \
			version=$(if $(new_version),$(new_version),$(version))

.PHONY: docker/queue/up
docker/queue/up:
	@echo
	@echo -e $(call message_info, Running QUEUE Container... 🚀)
	@echo
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/service/up context="php/queue"

.PHONY: docker/scheduler/up
docker/scheduler/up:
	@echo
	@echo -e $(call message_info, Running SCHEDULER Container... 🚀)
	@echo
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/service/up context="php/scheduler"

.PHONY: docker/database/up
docker/database/up:
	@echo
	@echo -e $(call message_info, Running Docker Database... 🚀)
	@echo
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/service/up context=mysql up_options="--force-recreate --detach"

.PHONY: docker/redis/up
docker/redis/up:
	@echo
	@echo -e $(call message_info, Running Docker Redis... 🚀)
	@echo
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/service/up context=redis up_options="--force-recreate --detach"

.PHONY: docker/mail/up
docker/mail/up:
	@echo
	@echo -e $(call message_info, Running Docker Mail... 📨)
	@echo
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/service/up context=mail up_options="--detach"

.PHONY: docker/typesense/up
docker/typesense/up:
	@echo
	@echo -e $(call message_info, Running Docker Typesense... )
	@echo
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/service/up context=typesense up_options="--detach"

.PHONY: docker/webserver/up
docker/webserver/up:
	@echo
	@echo -e $(call message_info, Running Webserver Container... 🚀)
	@echo
	@$(MAKE) -f $(docker_path)/Makefile \
			--no-print-directory \
			docker/service/up \
			context="nginx" \
			services="webserver"

# make  docker/service/up \
		context=FOLDER_IN_SERVICES \
		options=--verbose \
		up_options="--force-recreate" \
		services="services_in_docker_compose"
.PHONY: docker/service/up
docker/service/up:
	@docker compose -f $(docker_path)/$(context)/docker-compose.yml \
		$(options) $(common_options) \
		up $(up_options) \
		$(if $(and $(scale), $(service)),--scale $(service)=$(scale)) $(if $(services),$(services),)

.PHONY: docker/up
docker/up:
# make -f docker/Makefile docker/up database_container="app_database" redis_container="app-redis"
	@echo
	@echo -e $(call message_info, Running Docker Application... 🚀)
	@echo
	@docker compose -f $(docker_path)/docker-compose.yml up
	@docker compose rm hello-world --force
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/database/up
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/healthcheck container=$(if $(database_container),$(database_container),${project_name}-mysql)
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/redis/up
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/healthcheck container=$(if $(redis_container),$(redis_container),${project_name}-redis)
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/mail/up
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/typesense/up
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/app/up
# 	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/scheduler/up
# 	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/queue/up
	@$(MAKE) -f $(docker_path)/Makefile --no-print-directory docker/webserver/up
