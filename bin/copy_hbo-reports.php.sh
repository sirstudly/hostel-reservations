#!/bin/sh
# run with sudo please!
PROJECT_ROOT=/var/www/vhosts/macbackpackers.com/backoffice.scotlandstophostels.com
cp -p $PROJECT_ROOT/common/wordpress/wp-content/plugins/hbo-reports/hbo-reports.php $PROJECT_ROOT/castlerock/wp-content/plugins/hbo-reports/
cp -p $PROJECT_ROOT/common/wordpress/wp-content/plugins/hbo-reports/hbo-reports.php $PROJECT_ROOT/highstreethostel/wp-content/plugins/hbo-reports/
cp -p $PROJECT_ROOT/common/wordpress/wp-content/plugins/hbo-reports/hbo-reports.php $PROJECT_ROOT/royalmile/wp-content/plugins/hbo-reports/
cp -p $PROJECT_ROOT/common/wordpress/wp-content/plugins/hbo-reports/hbo-reports.php $PROJECT_ROOT/fortwilly/wp-content/plugins/hbo-reports/
cp -p $PROJECT_ROOT/common/wordpress/wp-content/plugins/hbo-reports/hbo-reports.php $PROJECT_ROOT/lochside/wp-content/plugins/hbo-reports/
