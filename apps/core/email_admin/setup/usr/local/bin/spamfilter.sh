#!/bin/bash
/usr/bin/spamc | /usr/sbin/sendmail -i "$@"
exit $?
