%define modname fax

Summary: Elastix Module Fax
Name:    elastix-%{modname}
Version: 3.0.0
Release: 8
License: GPL
Group:   Applications/System
#Source0: %{modname}_%{version}-5.tgz
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix-framework >= 3.0.0-1
Prereq: iaxmodem, hylafax
# ghostscript supplies eps2eps, ps2pdfwr, gs
Requires: ghostscript
# tiff2pdf supplied by libtiff (CentOS), libtiff-tools (Fedora)
Requires: /usr/bin/tiff2pdf

%description
Elastix Module Fax

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT/var/www/elastixdir/faxdocs
mkdir -p $RPM_BUILD_ROOT/var/www/elastixdir/tmpfaxdocs

# Files provided by all Elastix modules
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/apps/%{name}/
bdir=%{_builddir}/%{modname}
for FOLDER0 in $(ls -A modules/)
do
		for FOLDER1 in $(ls -A $bdir/modules/$FOLDER0/)
		do
			case "$FOLDER0" in 
				frontend)
					mkdir -p $RPM_BUILD_ROOT/var/www/html/web/apps/$FOLDER1/
					mv $bdir/modules/$FOLDER0/$FOLDER1/web/* $RPM_BUILD_ROOT/var/www/html/web/apps/$FOLDER1/
				;;
				backend)
					mkdir -p $RPM_BUILD_ROOT/var/www/html/admin/web/apps/$FOLDER1/
					mv $bdir/modules/$FOLDER0/$FOLDER1/web/* $RPM_BUILD_ROOT/var/www/html/admin/web/apps/$FOLDER1/	
				;;
			esac
			mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/apps/$FOLDER1/
			mv $bdir/modules/$FOLDER0/$FOLDER1/* $RPM_BUILD_ROOT/usr/share/elastix/apps/$FOLDER1/
		done
done

# Files personalities for hylafax
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/etc/
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/privileged
mkdir -p $RPM_BUILD_ROOT/etc/init
mkdir -p $RPM_BUILD_ROOT/etc/logrotate.d
mkdir -p $RPM_BUILD_ROOT/etc/cron.daily

#mv setup/hylafax/bin/includes                 $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/elastix-faxevent	      $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/faxrcvd-elastix.php      $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/faxrcvd.php              $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/notify-elastix.php       $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/notify.php               $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/etc/FaxDictionary            $RPM_BUILD_ROOT/var/spool/hylafax/etc/
mv setup/hylafax/etc/FaxDispatch              $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv setup/hylafax/etc/config                   $RPM_BUILD_ROOT/var/spool/hylafax/etc/
mv setup/hylafax/etc/setup.cache              $RPM_BUILD_ROOT/var/spool/hylafax/etc/
mv setup/usr/share/elastix/privileged/*       $RPM_BUILD_ROOT/usr/share/elastix/privileged
mv setup/etc/init/faxgetty.conf               $RPM_BUILD_ROOT/etc/init/
mv setup/etc/logrotate.d/elastixfax	      $RPM_BUILD_ROOT/etc/logrotate.d/
mv setup/etc/cron.daily/elastix_tmpfax_cleanup		$RPM_BUILD_ROOT/etc/cron.daily/
rm -rf setup/hylafax
rmdir setup/usr/share/elastix/privileged setup/usr/share/elastix setup/usr/share setup/usr
rmdir setup/etc/init setup/etc/logrotate.d setup/etc/cron.daily

chmod	 755 $RPM_BUILD_ROOT/etc/cron.daily/elastix_tmpfax_cleanup

#chmod -R 755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/includes
chmod	 755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/elastix-faxevent
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/faxrcvd.php
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/faxrcvd-elastix.php
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/notify.php
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/notify-elastix.php

# move main library of FAX. 
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/libs
mv setup/paloSantoFax.class.php               $RPM_BUILD_ROOT/usr/share/elastix/libs/

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mv setup/   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

# new for fax
mkdir -p $RPM_BUILD_ROOT/var/log/iaxmodem
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/bin
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/etc
mkdir -p $RPM_BUILD_ROOT/var/www/faxes/recvd
mkdir -p $RPM_BUILD_ROOT/var/www/faxes/sent


%pre
mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
# Habilito inicio automático de servicios necesarios
chkconfig --level 2345 hylafax on
chkconfig --level 2345 iaxmodem on

# Agrego Enlaces para Hylafax, ESTO AL PARECER LO HACE EL RPM DE HYLAFAX
ln -f -s pdf2fax.gs /var/spool/hylafax/bin/pdf2fax
ln -f -s ps2fax.gs  /var/spool/hylafax/bin/ps2fax

# Elimino archivos de fax que sobran
rm -f /etc/iaxmodem/iaxmodem-cfg.ttyIAX
rm -f /var/spool/hylafax/etc/config.ttyIAX

for i in `ls /var/spool/hylafax/etc/config.* 2>/dev/null`; do
  if [ "$i" != "/var/spool/hylafax/etc/config.sav" ]; then
    if [ "$i" != "/var/spool/hylafax/etc/config.devid" ]; then
      tilde=`echo $i | grep '~'`
      if [ "$?" -eq "1" ]; then
        if [ ! -L "$i" ]; then
          line="FaxRcvdCmd:              bin/faxrcvd.php"
          grep $line "$i" &>/dev/null
          res=$?
          if [ ! $res -eq 0 ]; then # no exists line
            echo "$line" >> $i
          fi
        fi
      fi
    fi
  fi
done

pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
# Run installer script to fix up ACLs and add module to Elastix menus.
#elastix-menumerge $pathModule/setup/infomodules
service mysqld status &>/dev/null
res=$?
if [ $res -eq 0 ]; then
	#service is up
	elastix-menumerge $pathModule/setup/infomodules	
else
	#copio el contenido de infomodules a una carpeta para su posterior ejecucion		
	if [ "$(ls -A $pathModule/setup/infomodules)" != "" ]; then
		mkdir -p /var/spool/elastix-infomodulesxml/%{name}-%{version}-%{release}/infomodules		
		mv $pathModule/setup/infomodules/* /var/spool/elastix-infomodulesxml/%{name}-%{version}-%{release}/infomodules
	fi
fi

preversion=`cat $pathModule/preversion_%{modname}.info`
rm -f $pathModule/preversion_%{modname}.info

if [ $1 -eq 1 ]; then #install
# en caso de instalacion movemos el archivo FaxDispatch
    if [ -f /var/spool/hylafax/etc/FaxDispatch ]; then
        fecha=$(date +%F.%T) 
        mv /var/spool/hylafax/etc/FaxDispatch /var/spool/hylafax/etc/FaxDispatch_$fecha
    fi
    mv $pathModule/FaxDispatch /var/spool/hylafax/etc/FaxDispatch
    chmod 644 /var/spool/hylafax/etc/FaxDispatch
    chown uucp.uucp /var/spool/hylafax/etc/FaxDispatch
# The installer database
    elastix-dbprocess "install" "$pathModule/setup/db"
elif [ $1 -eq 2 ]; then #update
    elastix-dbprocess "update"  "$pathModule/setup/db" "$preversion"
fi

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php


rm -rf /tmp/new_module

#chmod 666 /var/www/db/fax.db

%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Fax menus"
  elastix-menuremove $pathModule/setup/infomodules

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
/var/www/elastixdir/*
%defattr(-, root, root)
/usr/share/elastix/apps/*
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/var/spool/hylafax/bin/*
/var/spool/hylafax/etc/setup.cache
/etc/logrotate.d/elastixfax
%defattr(755, root, root)
/usr/share/elastix/privileged/*
/etc/cron.daily/elastix_tmpfax_cleanup
%defattr(644, root, root)
/usr/share/elastix/libs/*
/etc/init/faxgetty.conf
%defattr(775, asterisk, uucp)
/var/www/faxes/recvd
/var/www/faxes/sent
%dir
/var/log/iaxmodem
%defattr(755, asterisk, uucp)
/var/www/elastixdir/tmpfaxdocs
%defattr(-, uucp, uucp)
%config(noreplace) /var/spool/hylafax/etc/FaxDictionary
%config(noreplace) /var/spool/hylafax/etc/config

%changelog
* Thu Feb 26 2015 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: faxconfig: fix incorrect variables used in recordset when refreshing
  configuration files.
  SVN Rev[6878]

* Tue Dec  2 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Fax: change file and directory ownership in package to root
  instead of asterisk. Part of fix for Elastix bug #2062.
  SVN Rev[6789]

* Fri Nov 21 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-8
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Bump Release in specfile.

* Fri Jun 13 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-7
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Bump Release in specfile.

* Mon May 05 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: fax: fix incorrect shell syntax in postinstall scriptlet.
  SVN Rev[6622]

* Fri May 02 2014 Bruno Macias <bmacias@palosanto.com> 
- UPDATED: languages modules were updated.
  SVN Rev[6619]

* Mon Apr 28 2014 Bruno Macias <bmacias@palosanto.com> 
- FIXED: apps pbx, kamailio database now is created/
  SVN Rev[6610]

* Wed Apr 23 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-6
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6601]

* Mon Mar 10 2014 Bruno Macias <bmacias@palosanto.com> 
- CHANGED: code of the organization is the same as the domain.
  SVN Rev[6513]

* Wed Jan 29 2014 Rocio Mera <rmera@palosanto.com> 
- FIXED: TRUNK - APPS/Fax: Was fixed function editFax. Was added missing
  argument idUser at moment to call paloFax function editFaxToUser
  SVN Rev[6436]

* Tue Jan 28 2014 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - APPS/Fax: The function "showSendFax" was modified to send
  fax from the chat window.
  SVN Rev[6427]

* Thu Jan 23 2014 Rocio Mera <rmera@palosanto.com> 
- ADDED : TRUNK - APPS/Fax: Was added scroll option in .css file. Was added to
  the "send fax" option and was created a "cron" that delete the temporal files
  of the upload fax.
  SVN Rev[6403]

* Thu Jan 23 2014 Luis Abarca <labarca@palosanto.com> 
- ADDED: fax - Build/elastix-fax.spec: A new dir that contains the temporal
  messages of fax that will be sent, it has been created.
  SVN Rev[6402]

* Sat Jan 18 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-5
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6390]

* Tue Jan 07 2014 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - APPS/Fax: Were moved all the attributes of the "tooltip" to
  the .css general file.
  SVN Rev[6336]

* Wed Dec 18 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Frameworks/Apps: translations fax options (spanish)
  SVN Rev[6305]

* Fri Dec 13 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - APPS/Fax: The error manager was moved to general folder.
  SVN Rev[6283]

* Tue Dec 10 2013 Rocio Mera <rmera@palosanto.com> 
- FIXED: TRUNK - APPS/Fax: Was fixed bug in function deletefaxbyuser. This
  function delete the fax of a user identified bu his id
  SVN Rev[6266]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Apps/Email: translation in Fax Viewer labels filters
  (spanish)
  SVN Rev[6177]

* Tue Nov 19 2013 Luis Abarca <labarca@palosanto.com> 
- FIXED: build - *.spec: An error in the logic of the code was unintentionally
  placed when saving the elastix's spec files.
  SVN Rev[6125]

* Mon Nov 18 2013 Luis Abarca <labarca@palosanto.com> 
- FIXED: build - *.spec: An extra character was unintentionally placed when
  saving the elastix's spec files.
  SVN Rev[6116]

* Fri Nov 15 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: build - *.spec: Update specfiles with the new form of use
  elastix-menumerge for each elastix module.
  SVN Rev[6104]

* Mon Nov 11 2013 Luis Abarca <labarca@palosanto.com> 
- FIXED: fax - Build/elastix-fax.spec: The absence of two directories creation
  was a impediment for correct functionality of spec file. (recvd and sent)
  SVN Rev[6077]

* Tue Nov 05 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Apps/FAX: Was modified module my_fax in order to manage
  errors in the fields forms, to display error in field form is used tooltip
  bootstrap plugin.
  SVN Rev[6064]

* Mon Nov 04 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - APPS/Fax: Was changed the fax table structure. Was added
  field area_code,country_code,dev_id,port,fax_content,fax_subject also was
  renamed fields 'callerid_name' to 'clid_name' and 'callerid_number' to
  'clid_number'. This changes was done to permit the creation of fax that not
  belong to any user. Also was made changes in lib paloSantoFax and privileged
  script faxconfig
  SVN Rev[6055]

* Thu Oct 31 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Apps/FAX: Were modified variables of error message to global
  variables for to use in others modules.
  SVN Rev[6047]

* Thu Oct 24 2013 Rocio Mera <rmera@palosanto.com> 
- ADDED: TRUNK - Apps/FAX: Was added module my_fax to /fax/apps/frontend. this
  module display configuration the user's fax extension and allow modified
  SVN Rev[6035]

* Mon Oct 07 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: build - *.spec: Update specfile with some corrections correspondig
  to the way of remove tabs in the framework for each elastix module.
  SVN Rev[5994]

* Mon Oct 07 2013 Rocio Mera <rmera@palosanto.com> 
- ADDED: Trunk - Framework,Apps: Was added to framework/setup/infomodules xml
  files mysettings.xml and home.xml. This files create menu to frontend
  interface. Was also added xml menu files to apps/core/pbx,
  apps/core/email_admin, apps/core/fax, this files create menu to frontend
  interface
  SVN Rev[5992]

* Tue Oct 01 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps/fax: Was added file fax_email_template.xml to
  infomodules
  SVN Rev[5964]

* Mon Sep 30 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps: Was renamed directory infomudules to infomodules
  SVN Rev[5960]

* Mon Sep 30 2013 Rocio Mera <rmera@palosanto.com> 
- DELETED: Tunk - Apps/Fax: Was deleted file menu.xml. This file was divided in
  a set of files that are stored in setup/infomodules
- ADDED: Tunk - Apps/Fax: Was added directory infomodules. This directory store
  a xml files that are used to create elastix resources
  SVN Rev[5958]

* Mon Sep 30 2013 Rocio Mera <rmera@palosanto.com> 
- DELETED: Tunk - Apps/Fax: Was deleted file menu.xml. This file was divided in
  a set of files that are stored in setup/infomodules
- ADDED: Tunk - Apps/Fax: Was added directory infomodules. This directory store
  a xml files that are used to create elastix resources
  SVN Rev[5957]

* Wed Sep 25 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Framework: Was made change in file db.info in database
  elxpbx. Was added attribute prefix
- CHANGED: Trunk - Apps: Was made change in file db.info to elxpbx in module
  pbx, fax and email_admin
  SVN Rev[5950]

* Wed Sep 25 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Additional: Was made change in script elastix-dbprocess. If
  dbprocess fail it save the sql script that fail is saved with the name
  prefix_dbname_action in case that prefix attribute is set in db.info file. If
  prefix attribute is not set the name will be dbname_action. It changes was
  done to prevent rewrite script files when differents script take actions over
  a database.
- CHANGED: Trunk - Framework: Was made change in file db.info in database
  elxpbx. Was added attribute prefix
- CHANGED: Trunk - Apps: Was made change in file db.info to elxpbx in module
  pbx, fax and email_admin
  SVN Rev[5949]

* Wed Sep 25 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps: Was made change in module faxviewer to solve bug when
  there is not fax to show. Was made change in module trunk, general_settings
  and extensions to solve minor bugs
  SVN Rev[5948]

* Wed Sep 25 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: build - *.spec: Update specfile with some corrections correspondig
  to the way of identify and distribute folders to the '/usr/share/elastix/'
  path and '/var/www/html/' path.
  SVN Rev[5945]

* Tue Sep 24 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - APPS/FAX: Was made changes in module fax_email_template,
  faxviewer to rename field COMPANY_NAME and COMPANY_NUMBER for FAX_CID_NAME
  and FAX_CID_NUMBER. The script elastix-faxevent was also change
  SVN Rev[5939]

* Mon Sep 23 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps: Was made changes in db.info in apps pbx, email_admin
  and fax. Was setting to elxpbx databases the param ingore_backup=yes in order
  to the elastix-dbprocess does not made a backup of this database and delete
  the database elxpbx. The framework create elxpbx database
  SVN Rev[5926]

* Thu Sep 19 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: fax - Build/elastix-fax.spec: The path of module libraries were
  moved to /usr/share/elastix/libs/ .
  SVN Rev[5916]

* Thu Sep 19 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: fax - Build/elastix-fax.spec: Creating faxdocs folder is handled now
  by the spec file of this module
  SVN Rev[5904]

* Thu Sep 12 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-4
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[5873]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- ADDED: fax - setup/infomodules.xml/: Within this folder are placed the new
  xml files that will be in charge of creating the menus for each module.
  SVN Rev[5859]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: fax - modules: The modules were relocated under the new scheme that
  differentiates administrator modules and end user modules .
  SVN Rev[5858]

* Thu Sep 05 2013 Rocio Mera <rmera@palosanto.com> 
- ADDED: Trunk - Apps/Fax: Was added module faxqueue. This module permit cancel
  fax jobs. By the moment this module is just for superadmin in future release
  must be modified to permit organizations can access this module.
- DELETED: Trunk - Apps/Fax: Was deleted module faxlist.
- MOVED: Trunk - Apps/Fax: Was rename module email_template to
  fax_email_template. This module permit set the default email template used by
  organization to sent the notification_email that exist at the moment to sent
  a fax
- CHANGED: Trunk - Apps/Fax: Was made changed in modules faxviewer,
  faxmaster,faxclients, sendfax. This changes incorpore the new permissions
  schemas and the multitenant architecture
- CHANGED: Trunk - Apps/Fax: Was made changes in lib paloSantoFax. Was added
  functions to set fax_email_template by org. Was made change in function
  faxstatus
  SVN Rev[5839]

* Thu Sep 05 2013 Rocio Mera <rmera@palosanto.com> 
- ADDED: Trunk - Apps/Fax: Was added module faxqueue. This module permit cancel
  fax jobs. By the moment this module is just for superadmin in future release
  must be modified to permit organizations can access this module.
- DELETED: Trunk - Apps/Fax: Was deleted module faxlist.
- MOVED: Trunk - Apps/Fax: Was rename module email_template to
  fax_email_template. This module permit set the default email template used by
  organization to sent the notification_email that exist at the moment to sent
  a fax
- CHANGED: Trunk - Apps/Fax: Was made changed in modules faxviewer,
  faxmaster,faxclients, sendfax. This changes incorpore the new permissions
  schemas and the multitenant architecture
- CHANGED: Trunk - Apps/Fax: Was made changes in lib paloSantoFax. Was added
  functions to set fax_email_template by org. Was made change in function
  faxstatus
  SVN Rev[5838]

* Thu Sep 05 2013 Rocio Mera <rmera@palosanto.com> 
- ADDED: Trunk - Apps/Fax: Was added script elastix-faxevent to hylafx
  notifications scripts. This script replace to scripts that were in
  bin/include. This script sent email_notifications and insert a register when
  a email is sent or is received
  SVN Rev[5837]

* Wed Jul 10 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: APPS - FAX: Was made changes in libs paloSantoFax.lass.php to change
  the path that made include to elastix libs
  SVN Rev[5306]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - sendfax/: The It was corrected a configuration in the web
  folder.
  SVN Rev[5238]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxviewer/: It was corrected a configuration in the web
  folder.
  SVN Rev[5237]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxmaster/: It was corrected a configuration in the web
  folder.
  SVN Rev[5236]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxlist/: It was corrected a configuration in the web
  folder.
  SVN Rev[5235]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxclients/: It was corrected a configuration in the web
  folder.
  SVN Rev[5234]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - email_template/: It was corrected a configuration in the web
  folder.
  SVN Rev[5233]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - sendfax/: The svn repository for module sendfax in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5151]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxviewer/: The svn repository for module faxviewer in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5150]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxmaster/: The svn repository for module faxmaster in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5149]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxlist/: The svn repository for module faxlist in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5148]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - faxclients/: The svn repository for module faxclients in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5147]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - email_template/: The svn repository for module
  email_template in trunk (Elx 3) was restructured in order to accomplish a new
  schema.
  SVN Rev[5146]

* Thu Jun 20 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps: Was made chanes in elxpbx databse schema. This
  database contain data from pbx and now is integrated with framework database.
- CHANGED: Trunk - Apps: Was made change in privileged script asteriskconfig,
  email_account and faxconfig. This changes was made to incorpora the elastix
  framework database changes from sqlite to mysql
- CHANGED: Trunk - Apss: Was made change in some module of pbx. This changes
  are part of  elastix framework database changes from sqlite to mysql
  SVN Rev[5114]


* Mon May 27 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-3
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[5026]

* Fri May 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: remove several instances of command injection vulnerabilities in 
  paloSantoFax and in privileged script. Use SQL parameters in privileged 
  script. Pointed out by Fortify report.
  SVN Rev[4962]

* Tue Apr 09 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-2
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
  SVN Rev[4813]

* Fri Jan 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Modules/Fax: Was made changing in lib paloSantoFax.class.php
  to add new funtions use at the moment to delete a organization
  SVN Rev[4602]

* Thu Nov 29 2012 Rocio Mera <rmera@palosanto.com>
- FIXED: Apps - Fax/SendFax: Was fixed bug introduce in commit 4477. Was
  removed a print_r() function
  SVN Rev[4480]

* Thu Nov 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changed in module ivr to add support to
  reproduce the recordings in the dialplan
  SVN Rev[4477]

* Fri Nov 09 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Send Fax: check whether text to send as fax is entirely ASCII, and 
  attempt to convert to ISO-8859-15 if not, before converting to PostScript
  directly. Fixes Elastix bug #446.
  SVN Rev[4419]

* Tue Nov  6 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Send Fax: partial cleanup:
  Do not silently ignore failure to submit a fax job, and display error instead.
  Remove useless code that could potentially error out the module.
  Remove needless copy of temporary file followed by manual delete. Use the
  temporary uploaded file directly.
  Remove file type validation based on file extension. It is easy to beat, also
  prevents legitimate text files from being uploaded, and sendfax already has
  to figure out file type in order to apply conversion.
  SVN Rev[4412]

* Fri Oct 26 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Fax Viewer: at check status routine, session variable might be 
  invalid and trigger PHP warnings. Initialize local copy as array and copy 
  session variable only after checking it is too an array.
  SVN Rev[4384]

* Thu Oct 18 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Fax: add Requires: ghostscript, /usr/bin/tiff2pdf to specfile. This
  fixes inability to display received fax in Fedora 17 for Raspberry Pi.
  SVN Rev[4369]

* Wed Oct 17 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- Framework,Modules: remove temporary file preversion_MODULE.info under 
  /usr/share/elastix/module_installer/MODULE_VERSION/ which otherwise prevents
  proper cleanup of /usr/share/elastix/module_installer/MODULE_VERSION/ on 
  RPM update. Part of the fix for Elastix bug #1398.
- Framework,Modules: switch as many files and directories as possible under
  /var/www/html to root.root instead of asterisk.asterisk. Partial fix for 
  Elastix bug #1399.
- Framework,Modules: clean up specfiles by removing directories under 
  /usr/share/elastix/module_installer/MODULE_VERSION/setup/ that wind up empty
  because all of their files get moved to other places.
- Endpoint Configurator: install new configurator properly instead of leaving
  it at module_installer/MODULE/setup
  SVN Rev[4347]

* Wed Sep 26 2012 Rocio Mera <rmera@palosanto.com>
- FIXED: Conflicts between files provided by hylafax and elastix-fax rpm.
  SVN Rev[4311]

* Wed Sep 26 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Fax: Spec fiel was modified to solve conflicts FaxDispatch
  file with hylafax
  SVN Rev[4310]

* Wed Sep 26 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Fax/elastix-fax.spec: Was necesary modified spec file to move
  file faxgetty.conf to /etc/init directory
  SVN Rev[4308]

* Wed Sep 26 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Fax: Was modified file hylifax/bin/includes/functions.php.
  This file use a privileged script to insert recived fax in table fax_docs
  SVN Rev[4303]

* Thu Sep 25 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: fax - Build/elastix-fax.spec: Was added in spec 
  FaxDispatch file. It necesary is file be moved to 
  /var/spool/hylafax/etc
  SVN Rev[4298]
  
* Thu Sep 20 2012 Luis Abarca <labarca@palosanto.com> 3.0.0-1
- CHANGED: fax - Build/elastix-fax.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
- CHANGED: In spec file changed Prereq elastix-framework to
  elastix-framework >= 3.0.0-1
  SVN Rev[4226]

* Thu Aug 30 2012 German Macas <gmacas@palosanto.com>
- CHANGED: modules - faxlist: Fixed process to check Fax status
  SVN Rev[4159]

* Wed Aug 29 2012 German Macas <gmacas@palosanto.com>
- CHANGED : modules - faxlist - sendfax - faxviewer: Add option to check faxes
  status in faxlist, fixed messagges when send a fax, show status failed or OK
  of sent faxes in faxviewer
  SVN Rev[4156]

* Fri Jul 27 2012 Rocio Mera <rmera@palosanto.com>
- FIXED: Apps - Fax/Modules: Was fixed a bug in module faxviewer introduced in
  commit 4068
  SVN Rev[4080]

* Wed Jul 18 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - Fax/Faxlist: Was added file filter.tpl. this file it is
  necesary to commit 4068
  SVN Rev[4069]

* Wed Jul 18 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Fax: Was modified modules faxlist, sendfax and faxviewer to
  made compatible con multitenant model. Lib paloSantoFax.class.php and script
  to send and recive fax inside Setup/Hylafax was changed too
  SVN Rev[4068]

* Fri Jul 6 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Fax/faxconfig: Script faxconfig was changed to support
  multitenant
  SVN Rev[4052]

* Fri Jul 6 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Fax - Setup/paloSantoFax.class.php: Where changed lib
  paloSantoFax.class.php to support multitenant
  SVN Rev[4044]

* Thu Jun 28 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Remove stray print_r.
  SVN Rev[4017]

* Wed May 30 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Modules - Fax Viewer: relax unnecessarily-restrictive validation type
  on "Company Fax" filter option. Fixes Elastix bug #1281. SVN Rev[3946]

* Mon May 28 2012 German Macas <gmacas@palosanto.com> 2.3.0-4
- CHANGED: modules - sendfax: Add messages of sending fax process with ajax 
  on Send Fax application form. SVN Rev[3937]

* Wed May 02 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-3
- FIXED: Modules - faxlist: Duplicate name column "Name Caller ID" was fixed.
  SVN Rev[3915]
- ADDED: Build - SPEC's: The spec files were added to the corresponding modules
  and the framework.
  SVN Rev[3851]
  SVN Rev[3833]

* Fri Mar 09 2012 Alberto Santos <asantos@palosanto.com> 2.3.0-2
- CHANGED: In spec file changed prereq elastix-framework >= 2.3.0-2
- FIXED: modules -FAX, se corrige bug que no se muestra lpos faxes
  a utiluzar en en modulo sendfax
  SVN Rev[3728]

* Wed Mar 07 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-1
- CHANGED: In spec file changed Prereq elastix to  
  elastix-framework >= 2.3.0-1
- FIXED: modules - faxlist: Se corrige bug de pagineo en el modulo de faxlist.
  Tambien se definen correctamente ciertas traducciones.
  SVN Rev[3714]
- CHANGED: faxviewer index.php add control to applied filters
  SVN Rev[3701]
- FIXED: modules - faxmaster/index.php: The email hyphen is now allowed in Fax
  Master Configuration.
  SVN Rev[3679]
- CHANGED: little change in file *.tpl to better the appearance the options
  inside the filter when the language is spanish
  SVN Rev[3645]
- CHANGED: little change in file *.tpl to better the appearance the options
  inside the filter
  SVN Rev[3639]
- FIXED: modules - faxlist/index.php: Problems with the paged are now solved.
  SVN Rev[3628] 

* Wed Feb 1 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-8
- CHANGED: In spec file changed Prereq elastix to
  elastix-framework >= 2.2.0-30
- FIXED: modules - faxlist/index.php: Problems with the 
  paged are now solved. SVN Rev[3621].

* Fri Jan 27 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-7
- CHANGED: In spec file changed Prereq elastix to 
  elastix-framework >= 2.2.0-28
- CHANGED: modules - images: icon image title was changed on 
  some modules. SVN Rev[3572].
- CHANGED: modules - icons: Se cambio de algunos módulos los 
  iconos que los representaba. SVN Rev[3563].
- CHANGED: modules - * : Cambios en ciertos mòdulos que usan grilla
  para mostrar ciertas opciones fuera del filtro, esto debido al 
  diseño del nuevo filtro. SVN Rev[3549].
- UPDATED: modules - *.tpl: Se elimino en los archivos .tpl de ciertos 
  módulos una tabla demás en su diseño del filtro que formaba parte 
  de la grilla. SVN Rev[3541]. 

* Fri Nov 25 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-6
- CHANGED: In spec file changed Prereq elastix to
  elastix-framework >= 2.2.0-18
- CHANGED: In spec file, fix ownership and permissions on 
  recvd and sent directories for sudo-less fax notification scripts
- CHANGED: Fax: remove sudo chmod invocations from createFolder.
  With this, uucp no longer requires sudo privileges. For this
  to work, /var/www/faxes/recvd|sent directories need to have
  ownership 0775 asterisk.uucp
  SVN Rev[3376]
- CHANGED: Fax Viewer: fix typo in fax-not-found path when
  deleting faxes.
  SVN Rev[3370]
- FIXED: Send Fax: add -D switch to sendfax invocation to have
  desired behavior of notifying all sent faxes.
  SVN Rev[3369]
- FIXED: Send Fax: escape parameters to shell command for sendfax.
  SVN Rev[3368]
- FIXED: Fax: escape backslash that should be copied literally
  into config file as an escape in a configuration file.
  SVN Rev[3367]
- CHANGED: Fax Viewer: the two instances of fax deletion 
  (web interface and SOAP call) need to delete the fax file 
  along with the database information. Combine the two operations 
  into a single method that also handles the transaction. 
  This simplifies the fax API and the deletion logic, handles 
  faxes by ID instead of document name, and allows to make the 
  database connection object private. SVN Rev[3363]
- CHANGED: Fax Viewer: remove two functions that are no longer 
  used. This removes yet another instance of sudo chown.
  SVN Rev[3361]
- CHANGED: Fax Viewer: complete rewrite
     Replace non-standard paging method via xajax with standard 
        pagination using paloSantoGrid
     Replace delete operation via xajax with ordinary POST
     Handle file-not-found condition in fax document download
     Use application/pdf instead of application/octec-stream in 
        fax document download
     Use _tr for internationalization instead of $arrLang
     Stop calling testFile, since this call no longer works due 
        to access-denied on /var/spool/hylafax/docq/
  SVN Rev[3360]
- CHANGED: Fax Viewer: synchronize index.php as much as possible 
  between 1.6 and trunk. SVN Rev[3358]
- FIXED: Fax Visor: use SQL query parameters in all database 
  operations. SVN Rev[3357]
- FIXED: Fax Viewer (SOAP): handle requests with missing fields 
  by assumming NULL. SVN Rev[3356]
- CHANGED: Fax Viewer: remove stray echo left over from debugging.
  SVN Rev[3355]
- CHANGED: Fax Master: make use of 'faxconfig' to set fax master 
  email instead of sudo. SVN Rev[3354]
- CHANGED: Fax: add support in privileged helper for refreshing 
  FaxMaster setting in /etc/postfix/virtual. SVN Rev[3352]
- CHANGED: Fax Clients: make use of 'faxconfig' to query/set fax 
  clients instead of sudo chown. SVN Rev[3348]
- CHANGED: Fax: add support in privileged helper for querying and 
  setting hosts allowed to send fax. SVN Rev[3345]
- CHANGED: Fax: make use of 'faxconfig' in fax library instead of 
  sudo chown. SVN Rev[3340]

* Tue Nov 22 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-5
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-15
- CHANGED: Fax: use SQL query parameters in all database manipulation 
  methods. SVN Rev[3338]
- CHANGED: Fax: removed dead code. SVN Rev[3338]
- FIXED: Modules - Send Fax: Fixed validation of type of files 
  pdf, tiff, tif and txt when the name of files is in upper case.
  SVN Rev[3332]
- ADDED: Fax: Introduce 'faxconfig' privileged helper. SVN Rev[3331]
- CHANGED: module faxmaster, added the class "button" to the button
  SVN Rev[3325]
- CHANGED: module faxclients, changed the value and location 
  of the button. SVN Rev[3324]
- CHANGED: module faxmaster, the asterisks and word "Required field" 
  were removed. SVN Rev[3323]
- CHANGED: Fax: mark internal methods of paloFax class as private
  SVN Rev[3318]
- CHANGED: module faxviewer, changed style of table. SVN Rev[3218]

* Sat Oct 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-4
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-13

* Sat Oct 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-3
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-12
- CHANGED: module faxviewer, changed style of table. 
  SVN Rev[3218][3187]
- UPDATED: fax modules  templates files support new elastixneo 
  theme. SVN Rev[3148]
- UPDATED: sendfax  templates files support new elastixneo theme
  SVN Rev[3145]
- UPDATED: fax new  templates files support new elastixneo theme
  SVN Rev[3144]

* Tue Sep 27 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-2
- CHANGED: module email_template, eliminated asterisks in fields
  and the word "required field" in view mode
  SVN Rev[2996]

* Fri Sep 09 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-1
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-3
- CHANGED: module faxnew, in view mode the asterisks and word
  required were removed
  SVN Rev[2950]
- FIXED: modules - fax: Slow down on Hylafax because a chmod -R 777
  on a huge fax folder. For more details this bug:
  http://bugs.elastix.org/view.php?id=971 
  SVN Rev[2944]

* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- CHANGED: Modules - Trunk: The ereg function was replaced by 
  the preg_match function due to that the ereg function was 
  deprecated since PHP 5.3.0. SVN Rev[2688]
- CHANGED: The split function of these modules was replaced by 
  the explode function due to that the split function was 
  deprecated since PHP 5.3.0. SVN Rev[2650]

* Tue Apr 26 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-6
- CHANGED: module faxviewer, changed class name to core_Fax
  SVN Rev[2578]
- CHANGED: module faxviewer, changed name from puntosF_Fax.class.php
  to core.class.php
  SVN Rev[2570]
- NEW: new scenarios for SOAP in faxviewer
  SVN Rev[2557]
- CHANGED: file db.info, changed installation_force to ignore_backup
  SVN Rev[2491]
- CHANGED: In Spec file, changed the prereq of elastix to 2.0.4-19

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED: Fax - setup hylafax:  Change the text of email 
  notification from sending a Fax. SVN Rev[2459]
- CHANGED: module email_template, changed some information in 
  the view according to the bug #744. SVN Rev[2430]
- CHANGED: module faxlist and faxnew, changed the word 
  "destination email" to "associated email". SVN Rev[2412][2413]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format 
  to sql script for administer process install, update and delete
  SVN Rev[2332]
- CHANGED: changed the db.info of fax to the format used in 
  elastix-dbprocess. SVN Rev[2316]

* Thu Dec 30 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- FIXED: Framework/Fax: Commits 2088/2089 accidentally reverted 
  commit 1697, thus reintroducing the 
  unable-to-restart-webserver-after-configuring-fax bug. 
  Add the fix again. SVN Rev[2192]

* Mon Dec 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED:  Spec File has the actions post and install from elastix.spec
  about hylafax.
- CHANGED:  Change includes in files function.php 
  (hylafax/bin/include) where the include has a lib phpmailer old, 
  now this lib was in /var/www/html/libs. SVN Rev[2104]
- CHANGED: Module faxnew, Fixed Hard to see Bug  (H2C Bug), on 
  paloSantoFax.class.php _deleteLinesFromInittab  MUST be called 
  using $devId instead $idFax. Code Improvement, 
  class paloSantoFax.class.php, a new function called  restartFax() 
  was created. www.bugs.elastix.org [#607]. SVN Rev[2088]
- NEW:       additional paloSantoFax.class.php, better organization
  in Spec. SVN Rev[2082]
- CHANGED:   Change path to read or find pdf file sended or 
  received in faxviewer, this cahneg was done due to path 
  where fax files could be seen by url "http://IPSERVER/faxes"
  SVN Rev[2077]
- NEW:       New folder hylafax. SVN Rev[2074]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- CHANGED:   Add new Prereq in spec file about iaxmodem, hylafax  
- CHANGED:   massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2002]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- FIXED:     SendFax Module, label "Notification Sucessfull" does not
  exist in lang files. SVN Rev[1951]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- REMOVED: removed stray debug code that wrote to /tmp. SVN Rev[1909]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- CHANGED:   Updated the Bulgarian language elastix. SVN Rev[1857]

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palsoanto.com> 2.0.0-15
- CHANGED:   Updated fr.lang. SVN Rev[1825]
- NEW:       New lang file fa.lang (Persian). SVN Rev[1823]

* Mon Sep 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-14
- FIXED:     Corrected the message that say: Fax has been sended correctly, now says: Fax has been sent correctly. SVN Rev[1753], Bug[#518]

* Tue Sep 14 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- CHANGED:   Valid types of extensions to upload files and show message for incorrect files or files sended. Rev[1735]

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- CHANGED:   Change help files in send fax, fax viewer and email template. Rev[1679]
-            Show label (types of files supported) in send fax module

* Thu Jun 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-11
- Fixed bug where cannot edit a fax information. Link incorrect to faxvisor and not faxviewer

* Mon Mar 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- Fixed bug, permission 666 to database fax.db

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- Defined number order menu.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-8
- Update release module.

* Tue Jan 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- function getParameter removed in each module.

* Wed Dec 30 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Fixed bug name module, the name is sendfax and not send_fax.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-5
- New module send fax.

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Increment released.

* Sat Oct 17 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add accion uninstall rpm.
- Rename module faxvisor by faxview.
- Changed of words for a better definition of menus and messages.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.
