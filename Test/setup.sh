#!/usr/bin/env bash

set -e
set -x

BUILD_DIR="/tmp/magento2"

if [ -z $MAGENTO_VERSION ]; then MAGENTO_VERSION="2.0.8"; fi
if [ -z $MAGENTO_DB_HOST ]; then MAGENTO_DB_HOST="localhost"; fi
if [ -z $MAGENTO_DB_PORT ]; then MAGENTO_DB_PORT="3306"; fi
if [ -z $MAGENTO_DB_USER ]; then MAGENTO_DB_USER="root"; fi
if [ -z $MAGENTO_DB_PASS ]; then MAGENTO_DB_PASS=""; fi
if [ -z $MAGENTO_DB_NAME ]; then
    MAGENTO_DB_NAME="magento";
fi

MYSQLPASS=""
if [ ! -z $MAGENTO_DB_PASS ]; then MYSQLPASS="-p${MAGENTO_DB_PASS}"; fi

mkdir -p ${BUILD_DIR}

composer create-project --repository-url=https://repo.magento.com/ magento/project-community-edition=${MAGENTO_VERSION} ${BUILD_DIR}

cp -v Test/Fixtures/env.php "${BUILD_DIR}/app/etc/env.php"
cp -v Test/Fixtures/config.php "${BUILD_DIR}/app/etc/config.php"

sed -i -e "s/MAGENTO_DB_HOST/${MAGENTO_DB_HOST}/g" "${BUILD_DIR}/app/etc/env.php"
sed -i -e "s/MAGENTO_DB_PORT/${MAGENTO_DB_PORT}/g" "${BUILD_DIR}/app/etc/env.php"
sed -i -e "s/MAGENTO_DB_USER/${MAGENTO_DB_USER}/g" "${BUILD_DIR}/app/etc/env.php"
sed -i -e "s/MAGENTO_DB_PASS/${MAGENTO_DB_PASS}/g" "${BUILD_DIR}/app/etc/env.php"
sed -i -e "s/MAGENTO_DB_NAME/${MAGENTO_DB_NAME}/g" "${BUILD_DIR}/app/etc/env.php"

( cd "${BUILD_DIR}/" && composer install )

mkdir -p "${BUILD_DIR}/app/code/TIG"

if [[ ! -L "${BUILD_DIR}/app/code/TIG/Buckaroo" && ! -d "${BUILD_DIR}/app/code/TIG/Buckaroo" ]]; then
    ln -s `pwd` "${BUILD_DIR}/app/code/TIG/Buckaroo"
fi

mysql -u${MAGENTO_DB_USER} ${MYSQLPASS} -h${MAGENTO_DB_HOST} -P${MAGENTO_DB_PORT} -e "DROP DATABASE IF EXISTS \`${MAGENTO_DB_NAME}\`; CREATE DATABASE \`${MAGENTO_DB_NAME}\`;"
mysql -u${MAGENTO_DB_USER} ${MYSQLPASS} -h${MAGENTO_DB_HOST} -P${MAGENTO_DB_PORT} ${MAGENTO_DB_NAME} < Test/Fixtures/tig-buckaroo-fixture.sql

chmod 777 "${BUILD_DIR}/var/"
chmod 777 "${BUILD_DIR}/pub/"

( cd ${BUILD_DIR} && php -d memory_limit=2048M bin/magento setup:upgrade )
( cd ${BUILD_DIR} && php -d memory_limit=2048M bin/magento setup:static-content:deploy )

cd ${BUILD_DIR}

phpunit -c "${BUILD_DIR}/app/code/TIG/Buckaroo/phpunit.xml.dist"