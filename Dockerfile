FROM artifact.coppel.space/library/composer:2-php8.3-old AS dependencies
WORKDIR /packages
COPY composer.json auth.json .

RUN composer config --unset repositories
RUN composer config repositories.rac vcs https://dev.azure.com/Coppel-Argentina/solution.Omnicanal/_git/rac
RUN composer config repositories.nexus composer  https://nexus.coppel.space/repository/composer

RUN composer update --no-scripts


FROM artifact.coppel.space/library/php:8.2 AS source
WORKDIR /src
COPY --from=dependencies /packages .
COPY . .
RUN rm -f .htaccess .gitignore .gitlab-ci.yml Dockerfile && rm -rf logs/ .git/


FROM artifact.coppel.space/library/php:8.3
WORKDIR /app
COPY config.json .

COPY site.conf /etc/nginx/conf.d/default.conf
COPY --from=source /src .
