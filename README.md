# elastix-mt-gui - Elastix MT GUI



This code is distributed under the GNU LGPL v2.0 license.


## Introduction


## Installation

Install the git package and follow the instructions on a CentOS 6. 


```bash
cd /usr/src

#System packages
yum -y install system-config-date system-config-firewall-base system-config-keyboard system-config-language system-config-network-tui system-config-users
#Packages for this implementation.
yum -y install dialog vim mc screen git nmap wget mlocate mailx 
#Packages for web server.
yum -y groupinstall "Web Server"
yum -y install mod_ssl openssl
#Packages to the database.
yum -y install mysql-server mysql-connector-odbc
#Packages for php
wget http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm -O /usr/src/epel-release-6-8.noarch.rpm
rpm -ivh /usr/src/epel-release-6-8.noarch.rpm
yum -y install php-mcrypt
yum -y install php php-cli php-common php-devel php-gd php-imap php-mbstring  php-mysql php-pdo php-pear php-pear-DB php-process php-soap php-xml
#Packages for perl
yum -y install perl-Archive-Tar perl-Archive-Zip perl-CGI perl-Convert-BinHex perl-Crypt-OpenSSL-Bignum perl-Crypt-OpenSSL-RSA perl-Date-Manip perl-Digest-HMAC perl-Digest-SHA perl-Encode-Detect perl-HTML-Parser perl-HTML-TokeParser-Simple perl-HTTP-Response-Encoding perl-IO-Multiplex perl-IO-Socket-INET6 perl-IO-Socket-SSL perl-IO-stringy perl-MIME-tools perl-Mail-DKIM perl-Mail-IMAPClient perl-Net-IP perl-Net-Server perl-Net-Telnet perl-NetAddr-IP perl-String-CRC32 perl-URI perl-Unix-Syslog perl-WWW-Mechanize perl-XML-Parser  perl-suidperl

Cloning repository
git clone https://github.com/elastixmt/elastix-mt-gui.git
```
