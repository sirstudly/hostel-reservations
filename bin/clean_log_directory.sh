#!/bin/sh
find /var/www/vhosts/macbackpackers.com/backoffice.scotlandstophostels.com/castlerock/logs -regextype posix-extended -regex '.*(log|gz)' -type f -mtime +60 -delete
find /var/www/vhosts/macbackpackers.com/backoffice.scotlandstophostels.com/highstreethostel/logs -regextype posix-extended -regex '.*(log|gz)' -type f -mtime +60 -delete
find /var/www/vhosts/macbackpackers.com/backoffice.scotlandstophostels.com/royalmile/logs -regextype posix-extended -regex '.*(log|gz)' -type f -mtime +60 -delete
find /var/www/vhosts/macbackpackers.com/backoffice.scotlandstophostels.com/fortwilly/logs -regextype posix-extended -regex '.*(log|gz)' -type f -mtime +30 -delete
