%define modname system

Summary: Elastix Module System 
Name:    elastix-%{modname}
Version: 3.0.0
Release: 8
License: GPL
Group:   Applications/System
#Source0: %{modname}_%{version}-2.tgz
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix-framework >= 3.0.0-1
Prereq: elastix-fax >= 3.0.0-1 
Prereq: php-soap
Prereq: dahdi
Conflicts: elastix-agenda < 2.2.0-1
Requires: dhcp

%description
Elastix Module System

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/libs/
mkdir -p    $RPM_BUILD_ROOT/var/www/backup
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/privileged
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/apps/
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

mv setup/paloSantoNetwork.class.php      $RPM_BUILD_ROOT/usr/share/elastix/libs/
mv setup/automatic_backup.php            $RPM_BUILD_ROOT/var/www/backup/
mv setup/usr/share/elastix/privileged/*  $RPM_BUILD_ROOT/usr/share/elastix/privileged

rmdir setup/usr/share/elastix/privileged setup/usr/share/elastix setup/usr/share

# Additional (module-specific) files that can be handled by RPM
#mkdir -p $RPM_BUILD_ROOT/opt/elastix/
#mv setup/dialer

# ** Dahdi files **#
mkdir -p $RPM_BUILD_ROOT/etc/dahdi
mkdir -p $RPM_BUILD_ROOT/usr/sbin/

# ** switch_wanpipe_media file ** #
mv setup/usr/sbin/switch_wanpipe_media        $RPM_BUILD_ROOT/usr/sbin/
rmdir setup/usr/sbin

# ** The following selects oslec as default echo canceller ** #
echo "echo_can oslec" > $RPM_BUILD_ROOT/etc/dahdi/genconf_parameters
echo "bri_sig_style bri" >> $RPM_BUILD_ROOT/etc/dahdi/genconf_parameters

rmdir setup/usr

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv setup/   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%pre
mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"

# Run installer script to fix up ACLs and add module to Elastix menus.

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

pathSQLiteDB="/var/www/db"
mkdir -p $pathSQLiteDB
preversion=`cat $pathModule/preversion_%{modname}.info`
rm $pathModule/preversion_%{modname}.info

if [ $1 -eq 1 ]; then #install
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

%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete System menus"
  elastix-menuremove "%{modname}"
# this is necesary because system put to main menus 
  elastix-menuremove "manager" 

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(644, root, root)
/usr/share/elastix/libs/*
%defattr(-, root, root)
%{_localstatedir}/www/html/*
/usr/share/elastix/apps/*
/usr/share/elastix/module_installer/*
/var/www/backup/automatic_backup.php
%defattr(755, root, root)
/usr/sbin/switch_wanpipe_media
/usr/share/elastix/privileged/*
%config(noreplace) /etc/dahdi/genconf_parameters

%changelog
* Tue Dec  2 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: System: change file and directory ownership in package to root
  instead of asterisk. Part of fix for Elastix bug #2062.
  SVN Rev[6793]

* Fri Nov 21 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-9
- CHANGED: system - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile.

* Thu Oct 16 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-8
- CHANGED: system - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[6757]

* Thu Aug 28 2014 Alex Villacís Lasso <a_villacis@palosanto.com> 
- CHANGED: Revert commits 6705 through 6707. This needs to be done another way.
  SVN Rev[6708]

* Wed Aug 27 2014 Alex Villacís Lasso <a_villacis@palosanto.com> 
- CHANGED: Network Configuration: since rtpproxy is to be removed, do not
  bother restarting it anymore.
  SVN Rev[6706]

* Fri Jun 20 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: Network Configuration: ignore IP and mask fields when using DHCP.
  SVN Rev[6656]
- FIXED: Network Configuration: fix typo in translation string in error path.
  SVN Rev[6655]

* Fri Jun 13 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-7
- CHANGED: system - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[6650]

* Mon May 05 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Network Configuration: reconfigure and restart kamailio/rtpproxy after
  performing changes to network configuration or interfaces.
  SVN Rev[6624]

* Fri Apr 25 2014 Bruno Macias <bmacias@palosanto.com> 
- UPDATED: framework, paloSantoPBX.class, updated SQL.
  SVN Rev[6606]

* Wed Apr 23 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-6
- CHANGED: system - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[6600]

* Wed Mar 05 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: Backup/Restore: switch the MOH directory from mohmp3 to moh (the 
  Asterisk default). Update all code accordingly.
  SVN Rev[6495]

* Mon Feb 24 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: DHCP Server: fix redirect that assumes index.php is located at document
  root. This is a noop in Elastix 2 but fixes an invalid redirect after DHCP
  parameter updates in Elastix 3.
  SVN Rev[6490]

* Mon Feb 17 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: System: disable xdebug before creating a SoapClient in order to
  work around xdebug generating fatal errors for SOAP exceptions on creation.
  SVN Rev[6478]

* Wed Jan 22 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Network: Fedora requires the /etc/hostname file to be created/updated
  when changing the system hostname.
  SVN Rev[6399]

* Sat Jan 18 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-5
- CHANGED: system - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[6394]

* Thu Dec 26 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: Dashboard: replace deprecated .live and .die with .on and .off
  SVN Rev[6327]

* Thu Dec 05 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Packages: filter out inactive repostories before checking for package
  availability and freshness.
  SVN Rev[6257]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Apps/System: language translation add in Packages
  labels(spanish)
  SVN Rev[6174]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Apps/System: language translation add in Repositories
  labels(spanish)
  SVN Rev[6173]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Apps/System: language translation add in dhcp client list
  labels(spanish)
  SVN Rev[6172]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK -APPS/System: Was made change in module themes_system in
  function smartyRefresh to make use of function update_theme which is
  implemented in lib misc.lib.php
  SVN Rev[6168]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Frameworks/Manager: language translation add in Dashboard
  Applet Admin  label(spanish)
  SVN Rev[6165]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - PBX/Apps: language translation add in Dashboard 
  label(spanish)
  SVN Rev[6164]

* Wed Nov 20 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - System/Apps: language help add in currency (english -
  spanish)
  SVN Rev[6135]

* Wed Nov 20 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - System/Apps: language help add in Themes (english - spanish)
  SVN Rev[6134]

* Wed Nov 20 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - System/Apps: language help add in Language (english -
  spanish)
  SVN Rev[6133]

* Wed Nov 20 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - System/Apps: language help add in Assign IP Address to host
  (english - spanish)
  SVN Rev[6132]

* Tue Nov 19 2013 Luis Abarca <labarca@palosanto.com> 
- FIXED: build - *.spec: An error in the logic of the code was unintentionally
  placed when saving the elastix's spec files.
  SVN Rev[6125]

* Mon Nov 18 2013 Luis Abarca <labarca@palosanto.com> 
- FIXED: build - *.spec: An extra character was unintentionally placed when
  saving the elastix's spec files.
  SVN Rev[6116]

* Mon Nov 18 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Hardware Detector: with recent DAHDI versions, the configuration parser
  misparses "EC: OSLEC - INACTIVE" as having an echo canceller called
  "OSLEC - INACTIVE", which then shows up as "none" in the GUI. Fixed. Fixes 
  Elastix bug #1777.
  SVN Rev[6112]

* Fri Nov 15 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: build - *.spec: Update specfiles with the new form of use
  elastix-menumerge for each elastix module.
  SVN Rev[6104]

* Fri Nov 15 2013 Alex Villacís Lasso <a_villacis@palosanto.com> 
- CHANGED: DHCP Server: comment out the Polycom-specific options from the DHCP
  configuration template.
  SVN Rev[6100]

* Fri Nov 15 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: DHCP Server: comment out the Polycom-specific options from the DHCP
  configuration template.
  SVN Rev[6098]

* Thu Nov 14 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Framework/Apps: was added language help in
  system->Network_parameters (spanish - english)
  SVN Rev[6094]

* Thu Nov 07 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: for Asterisk 11, the astdb database can not be swapped
  while asterisk is running. It is now required to shutdown and restart asterisk
  around the database restores.
  SVN Rev[6071]
- FIXED: Backup/Restore: the privileged script tried to build and check a path
  using an not-always-assigned variable. Fixed. Additionally check whether the
  path globs actually exist before backing up. Part of the fix for Elastix
  bug #1764 item 3. Fixes Elastix bug #1747.
  SVN Rev[6070]

* Sat Oct 05 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: Dashboard: prefer system-installed magpierss to bundled magpierss if
  available. This is preparation for unbundling of magpierss library. Eventually
  elastix-framework should remove magpierss and elastix-system should have an
  additional Requires: php-magpierss.
  SVN Rev[5990]

* Fri Oct 04 2013 Rocio Mera <rmera@palosanto.com> 
- MOVED: Trunk - Apps/System: Was move module userlist to framework/manager
  SVN Rev[5979]

* Fri Oct 04 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps/System: Xml files that bellow to modules that are
  placed by framework was moved to framework/setup/infomodules
  SVN Rev[5978]

* Fri Oct 04 2013 Rocio Mera <rmera@palosanto.com> 
- MOVED: Trunk - Apps/System: Was moved from framework modules theme_system ,
  language, registration to apps/core/system
  SVN Rev[5975]

* Mon Sep 30 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps: Was renamed directory infomudules to infomodules
  SVN Rev[5960]

* Mon Sep 30 2013 Rocio Mera <rmera@palosanto.com> 
- DELETED: Tunk - Apps/System: Was deleted file menu.xml. This file was divided
  in a set of files that are stored in setup/infomodules
- ADDED: Tunk - Apps/System: Was added directory infomodules. This directory
  store a xml files that are used to create elastix resources
  SVN Rev[5956]

* Wed Sep 25 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: build - *.spec: Update specfile with some corrections correspondig
  to the way of identify and distribute folders to the '/usr/share/elastix/'
  path and '/var/www/html/' path.
  SVN Rev[5945]

* Tue Sep 24 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - APPS/System: Was made change in module system to rename
  field CID name and Cid number to FAX CID NAME and Fax CID number
  SVN Rev[5940]

* Mon Sep 12 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-4
- CHANGED: system - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[5874]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- ADDED: system - setup/infomodules.xml/: Within this folder are placed the new
  xml files that will be in charge of creating the menus for each module.
  SVN Rev[5871]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: system - modules: The modules were relocated under the new scheme
  that differentiates administrator modules and end user modules .
  SVN Rev[5870]

* Wed Sep 11 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - APPS/System: Was made changes in module userlist
  SVN Rev[5849]

* Wed Aug 28 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps/System: Was made changes in some modules in order to
  adap this modules to new permissions schemas
  SVN Rev[5813]

* Mon Aug 26 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Apps - System/userlist: Was made changes in module userlist and
  shutdown to adapt this modules to the new permission schemas
  SVN Rev[5808]

* Fri Aug 23 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps/System: Was made changes in modules applet_admin,
  shutdown to addapt this modules to the new permissions schemas
  SVN Rev[5802]

* Tue Aug 13 2013 Washington Reyes <wreyes@palosanto.com> 
- CHANGED: APPS - core/system/modules/userlist/index.php: code upgrade
  SVN Rev[5721]

* Fri Aug  9 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: further to choosing between astdb and astdb.sqlite3,
  astdb.sqlite3 must be deleted before running astdb2sqlite3.
  SVN Rev[5717] 

* Thu Aug  8 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: file restore can now be a list of file options, so use
  selected option instead of possible list in chown and chgrp.
- FIXED: Backup/Restore: sometimes the PATH is not accesible from scripts
  invoked from backupengine, fixed by hardcoding a path in putenv().
  SVN Rev[5640]

* Wed Aug  7 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: Dashboard: RSS parser in News applet wants to output iso-8859-1. Force
  the encoding to utf-8 instead.
  SVN Rev[5580]

* Thu Aug  1 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: Backup/Restore: attempt to backup and restore astdb.sqlite3 from
  Asterisk 11, as well as handling the case of an Asterisk 1.8 backup being
  restored in Asterisk 11.
  SVN Rev[5478]

* Tue Jul 30 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - System/Module: Was made changes in module userlits to adap
  it to the directory organization changes
  SVN Rev[5452]

* Fri Jul 19 2013 Washington Reyes <wreyes@palosanto.com> 
- CHANGED: APPS - Core/System/Modules/packages: code upgrade
  SVN Rev[5383]
- CHANGED: APPS - Core/System/Modules/repositories: code upgrade
  SVN Rev[5382]
- CHANGED: APPS - Core/System/Modules/hardware_detector: code upgrade
  SVN Rev[5377]
- CHANGED: APPS - Core/System/Modules/shutdown: code upgrade
  SVN Rev[5376]
- CHANGED: APPS - Core/System/Modules/dhcp_by_mac: code upgrade
  SVN Rev[5375]
- CHANGED: APPS - Core/System/Modules/dhcp_clientlist: code upgrade
  SVN Rev[5374]
- CHANGED: APPS - Core/System/Modules/dhcp_server: code upgrade
  SVN Rev[5373]
- CHANGED: APPS - Core/System/Modules/dhcp_server: code upgrade
  SVN Rev[5370]
- CHANGED: APPS - Core/System/Modules/network_parameters: code upgrade
  SVN Rev[5369]

* Thu Jul 18 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- DELETED: Dashboard: remove applets obsoleted by usermode interface.
  SVN Rev[5359]
- CHANGED: Dashboard: complete rewrite. The dashboard module has been completely
  rewritten in order to clean up the code and in order to modularize the applets
  and introduce the capability for a third-party addon to define a custom
  dashboard applet. A lot of cruft was removed, several potential SQL injection
  vulnerabilities were closed, and all applets were reviewed in the process of
  reorganization. Some highlights:
  All applets: most of the HTML embedded in the source code was moved to templates.
  SystemResources: a long-standing design flaw was fixed. Previously the CPU 
  gauge would only show the CPU load for a small interval while the applet was
  being rendered, with the result that it would frequently show 100% of use, and
  it would not refresh automatically. Now it refreshes every 5 seconds. In order
  to reduce CPU load due to redrawing of the gauge, the client side now uses an
  HTML5 Canvas to redraw the gauge in the browser.
  HardDrives: It was found that systems with large amounts of data in mail or
  recordings directories would spend an inordinate amount of time running du 
  in order to report the disk usage by directory. Now this report is only run by
  explicit request and displays a warning that fetching the information will 
  impact system performance. Also, some i18n work for Spanish.
  PerformanceGraphic: i18n work.
  CommunicationActivity: i18n work, and the network activity updates in real time.
  TelephonyHardware: i18n work. SQL injections closed. The webservice client now
  reports failures properly when the webservice cannot write the registration.

  Elastix 3 note: this gets the dashboard port mostly working. Communication
  Activity is known to be faulty in some places and requires a rewrite using
  realtime DB. Some applets are obsoleted by usermode interface.
  SVN Rev[5343]

* Wed Jul 17 2013 Washington Reyes <wreyes@palosanto.com>
- CHANGED: APPS - Core/System/Modules/time_Config: code upgrade
  SVN Rev[5335]

* Mon Jul 15 2013 Washington Reyes <wreyes@palosanto.com> 
- CHANGED: APPS - Core/System/Modules/Currency: code upgrade
  SVN Rev[5315]ç
- CHANGED: APPS - Core/System/Modules/Currency: code upgrade
  SVN Rev[5314]

* Wed Jul 10 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: APPS - system: Was made changes in lib
  paloSantoDataApplets.class.php in order to modify wrong inclusion
  paloSantpDB.class.php
  SVN Rev[5307]

* Fri Jul 05 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGES: APPS - PBX: Was made changes in modules dashboard, applet_admin to
  add elastix restructuration directory.
  SVN Rev[5300]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - userlist/: It was corrected a configuration in the web
  folder.
  SVN Rev[5295]
- CHANGED: trunk - time_config/: It was corrected a configuration in the web
  folder.
  SVN Rev[5294]
- CHANGED: trunk - shutdown/: It was corrected a configuration in the web
  folder.
  SVN Rev[5293]
- CHANGED: trunk - repositories/: It was corrected a configuration in the web
  folder.
  SVN Rev[5292]
- CHANGED: trunk - packages/: It was corrected a configuration in the web
  folder.
  SVN Rev[5291]
- CHANGED: trunk - network_parameters/: It was corrected a configuration in the
  web folder.
  SVN Rev[5290]
- CHANGED: trunk - hardware_detector/: It was corrected a configuration in the
  web folder.
  SVN Rev[5289]
- CHANGED: trunk - dhcp_server/: It was corrected a configuration in the web
  folder.
  SVN Rev[5288]
- CHANGED: trunk - dhcp_clientlist/: It was corrected a configuration in the
  web folder.
  SVN Rev[5287]
- CHANGED: trunk - dhcp_by_mac/: It was corrected a configuration in the web
  folder.
  SVN Rev[5286]
- CHANGED: trunk - dashboard/: It was corrected a configuration in the web
  folder.
  SVN Rev[5285]
- CHANGED: trunk - currency/: It was corrected a configuration in the web
  folder.
  SVN Rev[5284]
- CHANGED: trunk - applet_admin/: It was corrected a configuration in the web
  folder.
  SVN Rev[5283] 
- CHANGED: trunk - backup_restore/: The svn repository for module
  backup_restore in trunk (Elx 3) was restructured in order to accomplish a new
  schema.
  SVN Rev[5214]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - userlist/: The svn repository for module userlist in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5208]
- CHANGED: trunk - time_config/: The svn repository for module time_config in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5207]
- CHANGED: trunk - shutdown/: The svn repository for module shutdown in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5206]
- CHANGED: trunk - repositories/: The svn repository for module repositories in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5205]
- CHANGED: trunk - packages/: The svn repository for module packages in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5204]
- CHANGED: trunk - network_parameters/: The svn repository for module
  network_parameters in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5203]
- CHANGED: trunk - hardware_detector/: The svn repository for module
  hardware_detector in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5202]
- CHANGED: trunk - dhcp_server/: The svn repository for module dhcp_server in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5201]
- CHANGED: trunk - dhcp_clientlist/: The svn repository for module
  dhcp_clientlist in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5200]
- CHANGED: trunk - dhcp_by_mac/: The svn repository for module dhcp_by_mac in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5199]
- CHANGED: trunk - dashboard/: The svn repository for module dashboard in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5198] 
- CHANGED: trunk - currency/: The svn repository for module currency in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5197]
- CHANGED: trunk - applet_admin/: The svn repository for module applet_admin in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5196]

* Fri Jun 21 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Packages: remove stray debugging statements.
  SVN Rev[5116]

* Thu Jun 20 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps: Was made chanes in elxpbx databse schema. This
  database contain data from pbx and now is integrated with framework database.
- CHANGED: Trunk - Apps: Was made change in privileged script asteriskconfig,
  email_account and faxconfig. This changes was made to incorpora the elastix
  framework database changes from sqlite to mysql
- CHANGED: Trunk - Apss: Was made change in some module of pbx. This changes
  are part of  elastix framework database changes from sqlite to mysql
  SVN Rev[5114]


* Mon Jun 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: fix botched detail window for package version mismatch.
  Convert all of the module to use _tr() and load_language_module().
  SVN Rev[5106]

* Wed Jun 12 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Backup/Restore: use internal glob instead of exec'd shell piping to
  list backup files. Modernize pagination. Convert more uses of $arrLang to 
  _tr(). Remove a large code duplication at automatic backup handling. Use the
  -O option of tar to extract XML to stdout instead of using temporary files.
  SVN Rev[5093] 
- CHANGED: Backup/Restore: partial rewrite of FTP implementation to clean up the
  API and improve error reporting and handling. Remove a style in custom CSS 
  that conflicts with a framework style. Remove dead code. Convert some uses of
  $arrLang to _tr(). Fixes Elastix bug #1586.
  SVN Rev[5092]

* Thu Jun 06 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Time Config: remove duplicate definition of getParameter() that gets
  confused by Fortify as the one used by the framework.
  SVN Rev[5059]

* Wed Jun 05 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Packages: partial rewrite. Reorganize strategy for installed packages
  to run rpm in a pipe (hopefully in parallel) and to keep open the database
  connections to the various repository files. Do not invoke an external command
  to check whether each package is installed when one invocation is enough. Make
  a distinction between same-named packages for different architectures. Do not
  display anything but the latest version of a not-installed package. All of
  this results in a noticeable speedup (from 8 seconds to 3 for the basic 
  listing).
  SVN Rev[5055] 

* Mon Jun 03 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: fix regression in restore functionality due to missing
  quotation marks for string expression.
  SVN Rev[5051]

* Fri May 31 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Date/Time: fix incorrect formatting of date for ticking clock.
  SVN Rev[5048]

* Wed May 29 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Dashboard: fix overlapping text in System Resources applet on blackmin.
  SVN Rev[5046]
- CHANGED: Dashboard: to check whether a service is enabled, the code now runs
  a glob and caches the results instead of spawning a ls and a grep. Also add
  support for systemd services.
  SVN Rev[5045]
- CHANGED: Dashboard: request several applets concurrently instead of 
  sequentially.
  SVN Rev[5044]
- CHANGED: Dashboard: set up an HTML version of the gauges used in the Hard
  Disks and System Resources applets, and use them if the uelastix flag is set.
  This is required to skip generation of the corresponding graphic, which is
  costly on ARM uElastix.
  SVN Rev[5042]

* Tue May 28 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Dashboard: the Hard Disks applet does not call the method 
  getSysInfo() anymore, but uses popen() on two commands in an attempt to run
  them in parallel. This saves some unnecessary file opens in /proc as well as
  an unneeded usleep().
  SVN Rev[5038]
- CHANGED: Dashboard: the System Resources applet does not call the method
  getSysInfo() anymore, but discrete functions. This saves two unnecessary 
  process spawns and at least one disk hit for unused free space information.
  SVN Rev[5036]

* Tue May 28 2013 Alex Villacís Lasso <a_villacis@palosanto.com> 
- CHANGED: Dashboard: synchronize as much as possible between 2.4 and trunk for
  easier analysys.
  SVN Rev[5035]

* Mon May 27 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-3
- CHANGED: system - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[5032]

* Tue May 21 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Hardware Detector: move hardware_detector script to the directory
  /usr/share/elastix/privileged/ as was done in Elastix 2.
  SVN Rev[4979]

* Mon May 20 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: fix some potential code injection vulnerabilities.
  Pointed out by Fortify report.
  SVN Rev[4978]

* Fri May 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Packages: fix several code injection and SQL injection vulnerabilities.
  Pointed out by Fortify report.
  SVN Rev[4971]

* Thu May  2 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Currency: remove dead code. Elastix 3 requires an reimplementation
  using the organization properties table.
  SVN Rev[4878]
- CHANGED: DHCP Client List: remove unnecessary call to filesize() on lease list
  file. It causes problems on a zero size lease list.
  SVN Rev[4877]
- CHANGED: DHCP Server: since DHCP server configuration is done entirely via the
  privileged script, the database parameter for the constructor to PaloSantoDHCP
  is unnecessary and has been removed.
  SVN Rev[4876]

* Wed Apr 24 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Applet Admin: use supplied module_name instead of getting variable
  from session. The package elastix-framework needs a Conflicts with previous
  versions of elastix-system.
  SVN Rev[4857]

* Thu Apr 18 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Userlist: remove XSS bug through user-supplied orgname/username/address.
  SVN Rev[4848]

* Mon Apr 15 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: work around a jQueryUI Droppable bug in which an <ul>
  list of initial height 0 will not trigger drop event if dragged element 
  triggers a resize of the list. Fixes Elastix bug #1526.
  SVN Rev[4846]
- CHANGED: Backup/Restore: replace remote filelist filter implementation with 
  a more compact version using regexps, with note about assumed workaround in
  previous implementation.
  SVN Rev[4844]

* Tue Apr 09 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-2
- CHANGED: system - Build/elastix-system.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
  SVN Rev[4817]

* Thu Jan 31 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: some restore operations overwrite known passwords, such
  as the root mysql password and the ami manager password. If passwords are
  changed between a backup and a restore on the same system, or a backup is 
  restored on a system with different passwords, the freepbx interface will 
  break due to password mismatch. Fix by restoring passwords from elastix.conf.
  Fixes Elastix bug #1462.
  SVN Rev[4660]
- FIXED: Backup/Restore: Due to unintended bug-for-bug compatibility with the
  previous backup/restore implementation, the backupengine script restored 
  fax.db with chmod 644, which resulted in fax notifications breaking for any 
  fax sent or received after a restore that included fax information. Fixed.
  This fixes part 8 of Elastix bug #1461.
  SVN Rev[4657]

* Tue Jan 29 2013 Rocio Mera <rmera@palosanto.com>
- CHANGE: modules: dashboard: Add options to active o inactive services when
  reboot system in Process Status Applet
  SVN Rev[4656]

* Tue Jan 29 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - System: In priviliged script backupengine was made changed to
  add validations in case that no exist a file that pretend to backup.
  SVN Rev[4653]

* Tue Jan 29 2013 Rocio Mera <rmera@palosanto.com>
- FIXED: Apps - System: Was made changed in priviliged script backupengine in
  order to fix bug 1445 item 7. At the moment to restore mailboxs the message
  showed as unread
  SVN Rev[4649]

* Tue Jan 22 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: system - backup_restore/backup.tpl , backup_restore/index.php: The
  popup that apears when a restore of a previous backup its about to be
  performed and a difference exists in the version of a package, now follows
  the schema of unified style for popups in Elastix.
  SVN Rev[4607]

* Fri Jan 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - System: Was modified module userlist in order to add
  restriction to the max numbers of users account that can exist by
  organization
  SVN Rev[4599]

* Wed Jan 16 2013 German Macas <gmacas@palosanto.com>
- CHANGE: modules - packages - festival -antispam: Change grid view and add
  option to Update packages in Package module - Fixed bug in StickyNote
  checkbox in festival and antispam modules
  SVN Rev[4588]


* Tue Dec 04 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Network Parameters: allow optional quote before dhcp keyword.
  SVN Rev[4500]

* Fri Nov 30 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: mailbox quotas need to be repaired after a mailbox 
  restore. Otherwise the quotas of the restored mailboxes always appear as 0.
  Fixes part 10a of Elastix bug #1416.
  SVN Rev[4489] 

* Thu Nov 29 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: since the privileged script conversion, the backupengine
  script writes an XML manifest with untranslated strings. This conflicts with
  the 'not installed' case in the GUI, which uses translated strings for 
  comparison. Now both are consistently using non-translated strings. Fixes part
  8 of Elastix bug #1416.
  SVN Rev[4479]

* Thu Nov 22 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: backupengine restore would fail to insert the key
  virtual_mailbox_domains if the key is missing prior to a restore that includes
  virtual domains. Fixed.
  SVN Rev[4453]

* Mon Oct 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - System/Modules: Was fixed type of did that superadmin can
  create
  SVN Rev[4391]

* Mon Oct 22 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Backup/Restore: actually use root mysql password instead of hardcoded
  default when reading schemata. Print PDO exception message in case of failure
  to read schemata. Pass around level tag to display on error message.
  SVN Rev[4378]  

* Wed Oct 17 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Network: (trival) fix typo in DNS template. Fixes Elastix bug #1400.
  SVN Rev[4376] 

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
  SVN Rev[4347]

* Tue Oct 16 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: implemented new helper script 'ryum' and use it to replace 
  invocations of 'sudo yum' with the helper script.
  SVN Rev[4342]

* Mon Oct 08 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Hardware Detector: implement switching on/off of CRC4 checksum for E1
  spans, including support for modification of Wanpipe spans. Partial fix for
  Elastix bug #1380.
  SVN Rev[4330]
- CHANGED: Hardware Detector: Elastix 3 no longer ships /usr/sbin/amportal which
  is part of FreePBX. Check whether /usr/sbin/amportal exists in 
  hardware_detector and use asterisk script instead if necessary.
  SVN Rev[4329]

* Thu Sep 20 2012 Luis Abarca <labarca@palosanto.com> 3.0.0-1
- CHANGED: system - Build/elastix-system.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
- CHANGED: In spec file changed Prereq elastix-framework to
  elastix-framework >= 3.0.0-1
  SVN Rev[4232]

* Fri Sep 07 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Backup/Restore: fix up Spanish translation.
  SVN Rev[4190]
- CHANGED: Backup/Restore: remove unneeded sudo chown on /var/www/backup on file
  delete. With this, Backup/Restore is officially sudo-free.
  SVN Rev[4189]
- FIXED: Backup/Restore: fixed backupengine to correctly expand backup
  components only for full words.
- CHANGED: Backup/Restore: task of granting correct ownership to backup tarball
  is now shifted to backupengine.
- FIXED: Backup/Restore: backward-compatibility fix of encoding freePBX package
  name as 'freepbx'.
- CHANGED: Backup/Restore: reimplement pre/post operations on email databases to
  not depend on paloEmail class.
- CHANGED: Backup/Restore: reimplement GUI backup and restore operations on top
  of backupengine. This removes four dependencies on other libraries and removes
  a lot of instances of sudo chown.
  SVN Rev[4188]
- FIXED: DHCP Server: option domain-name-servers should be written once with a
  comma-separated list of DNS servers, instead of repeating the option for
  multiple servers as done before. Fixes Elastix bug #1363.
  SVN Rev[4186]

* Thu Sep 06 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Backup/Restore: extend 'backupengine' privileged script to install
  a new crontab for automatic backup. Reimplement automatic backup 
  administration using this capability. This removes four instances of sudo 
  chown.
  SVN Rev[4185]
- CHANGED: Backup/Restore: reimplement automatic_backup.php as a wrapper around
  a direct call to backupengine. This removes one duplication of the backup
  implementation.
  SVN Rev[4184]  
- ADDED: Backup/Restore: new privileged script 'backupengine' that reimplements
  backup/restore functionality.
  SVN Rev[4183]

* Wed Aug 15 2012 German Macas <gmacas@palosanto.com>
- CHANGE: modules - packages: Add option to uninstall packages and change
  deprecated function
  SVN Rev[4108]

* Mon Aug 06 2012 German Macas <gmacas@palosanto.com>
- Fixed bug 0001318, bug 0001338: fixed in Asterisk File Editor return last
  query in Back link, fixed Popups, position and design, add in Dashboard
  Applet Admin option to check all
  SVN Rev[4092]

* Fri Jul 20 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: DHCP Server (dhcpconfig): implement new option --phonesonly. If 
  specified, the DHCP server will be configured to answer only to MAC addresses
  from known phone vendor prefixes. This potentially enables the Elastix DHCP
  server and another DHCP server to coexist in the same LAN.
  SVN Rev[4077]

* Tue Jul 17 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: DHCP Server (dhcpconfig): restrict DHCP option 160 to MAC addresses
  of Polycom phones. This prevents a conflict with LG-ERICSSON phones which also
  request option 160 but use it for a different purpose.
  SVN Rev[4066]

* Tue Jul 17 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: DHCP Server (dhcpconfig): Define and use DHCP option 160 that allows
  Polycom phones to find and use the integrated TFTP server instead of the
  factory-default of FTP. Also make the options names a bit more readable.
  SVN Rev[4064]

* Mon Jul 16 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: DHCP Server (dhcpconfig): Define and use DHCP option 150 that allows
  Cisco phones to find the integrated TFTP server.
  SVN Rev[4063]

* Fri Jun 29 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Add Requires: dhcp to specfile
  SVN Rev[4030]

* Fri Jun 15 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- Remove openfire dependency, moved to elastix-im.
- Remove wanpipe-util dependency. The hardware_detector script is supposed to
  detect that wanpipe-util is installed and disable Sangoma detection if not.

* Tue Jun 12 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: DHCP Server: Several fixes for Fedora 17 compatibility:
  1) dhcpconfig need to explicitly load the default timezone for PHP
  2) dhcpd.conf is at a different place in Fedora and access is denied to 
     asterisk by default, so a new privileged action dumpconfig was created
  3) Be aware of location change between CentOS and Fedora 17 when writing
     configuration file
  4) In Fedora 17, dhcpd no longer writes a pidfile, so running status must be
     queried with service dhcpd status
  5) Use ip addr show instead of ifconfig because of output format change
  SVN Rev[4008]
- CHANGED: PaloSantoNetwork: make some functions static
  SVN Rev[3995]

* Mon Jun 11 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: PaloSantoNetwork: rewrite network parameter query to stop using 
  ifconfig which changed output format between CentOS and Fedora 17. Add
  documentation on the output format of network query methods.
  SVN Rev[3984]

* Thu Jun 07 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: PaloSantoNetwork: mark some methods as private
- CHANGED: PaloSantoNetwork: more efficient implementation for IP manipulation 
  methods.
  SVN Rev[3974]

* Wed Jun 06 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Dashboard: Reorganize some of the code for system status. Before this
  commit, each gauge graph had to run the system probe anew in order to build 
  the display. This, despite having the correct value superimposed on the graph
  from the HTML output. This could sometimes lead to the gauge displaying a 
  different value from the one in HTML. Now the gauge merely displays a value 
  supplied by the URL, which is the same value used in HTML. This cuts three 
  system probes. It also allows to remove some now-dead code. Additionally the 
  paloSantoSysInfo class was changed to only do the system probe on an explicit
  call, instead of every time an object is built.
  SVN Rev[3961]

* Fri Jun 01 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Dashboard: rework service state query so that it works in both CentOS
  and Fedora. In particular, Fedora denies access to /var/run/asterisk/httpd.pid
  from asterisk user, so httpd needs to be queried by command name, not pid.
  Also, query uses filesystem functions instead of external commands - faster.
  SVN Rev[3955]

* Mon May 07 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-9
- UPDATED: Specfile was updated to release 9
- CHANGED: modules - hardware_detector: fixed popup in blackmin theme
  SVN Rev[3930]
- UPDATED: Modules - System/setup/build: update changelog specfile with the
  latest commit from svn
  SVN Rev[3924]

* Wed May 02 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-8
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-9
- UPDATED: Modules - hardware_detector: Update style table for content popup.
  SVN Rev[3918]

* Fri Apr 27 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-7
- CHANGED: System - Build/elastix-system.spec: update specfile with latest
  SVN history. Changed release in specfile
- CHANGED: modules - hardware_detector:change the modal popup form of
  Configuration of Span and Span Parameter  to ShowModalPopUp form, to keep the
  same PopUp form in the system.
  SVN Rev[3874]
- CHANGED: modules - dashboard: add _register.tpl file that contains the
  application form of Register Card to show in the ModalPopUp form.
  SVN Rev[3869]
- CHANGED: modules - dashboard: Change the application form of Register Card to
  ShowModalPopUp form, to mantain the same popup format in the system.
  SVN Rev[3868]
- ADDED: Build - SPEC's: The spec files were added to the corresponding modules
  and the framework.
  SVN Rev[3857]
  SVN Rev[3839]

* Mon Apr 02 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-6
- CHANGED: System - Package: Changed message that appear when repositories
  aren't update in english and spanish
  SVN Rev[3811] - [3809] - [3808]

* Thu Mar 29 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-5
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-5
- CHANGED: System - Packages: Changed action updateREpositories and install 
  repositories. Before this action use xajax, now are implemented with json
  SVN Rev[3805].

* Mon Mar 26 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-3
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-3
- CHANGED: System - Package/index.php: Little better in show filters so don't
  appear the option x when is applied the default filter
  SVN Rev[3773]

* Fri Mar 09 2012 Alberto Santos <asantos@palosanto.com> 2.3.0-2
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-2
- FIXED: module - dashboard/libs/paloSantoSysInfo.class.php: Se
  corrige problema de estado de fax, no instalado, se mejora funcion
  que pregunta estado del servicio.
  SVN Rev[3735]
- FIXED: Modules - Dashboard: Not displayed correctly the information
  of the New, problem with the encode of the special character
  SVN Rev[3729]

* Wed Mar 07 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-1
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-1
- CHANGED:packages new.tpl delet action UpdateRepository inside the filter
  SVN Rev[3719]
- CHANGED:packages index.php add control to applied filters and move outside
  the filter the option RepositoriesUpdates
  SVN Rev[3718]
- FIXED: modules - faxlist: Se corrige bug de pagineo en el modulo de faxlist.
  Tambien se definen correctamente ciertas traducciones.
  SVN Rev[3714]
- CHANGED: repositories index.php add control to applied filters
  SVN Rev[3698]
- FIXED: backup_restore index.php fixed problem with action Automatic_Backup
  introduced in commit 3684, delete the file themes/filter.tpl
  SVN Rev[3694]
- FIXED: Backup/Restore: fix use of unexpectedly initialized variable as
  combobox definition.
  SVN Rev[3693]
- CHANGED: dhcp_by_mac change to add control to filter
  SVN Rev[3692]
- CHANGED: modules - repositories: Se agrega controles a los filtros, del
  modulo repositorios
  SVN Rev[3691]
- CHANGED: backup_restore index.php change to put new function outside of
  filter
  SVN Rev[3684]
- CHANGED: repositories index.php change to add new function outside of filter
  SVN Rev[3683]
- UPDATED: modules - hardware_detector: Se define ancho de la tabla que formar
  parte de un puerto, en chrome se mostraba mal los puertos.
  SVN Rev[3663]
- CHANGED: Dashboard little change in file 1_style.css add top-border in option
  inside the proccess_status
  SVN Rev[3646]
- CHANGED: System - dashboard little change in paloSantoDataApplets.class.php
  to better the appearance in information about comunication_activity
  SVN Rev[3644]
- CHANGED: little change in file *.tpl to better the appearance the options
  inside the filter
  SVN Rev[3639]
- CHANGED: file index.php to fixed the problem with the paged
  SVN Rev[3626]

* Wed Feb 01 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-21
- FIXED: modules - modules/packages: Problems with the paged 
  are now solved. SVN Rev[3619]
- FIXED: modules - modules/repositories: Problems with the paged 
  are now solved. SVN Rev[3618]

* Mon Jan 30 2012 Alberto Santos <asantos@palosanto.com> 2.2.0-20
- CHANGED: In spec file, changed prereq elastix-framework >= 2.2.0-29
- CHANGED: Changed the word 'Apply Filter' by 'More Option'
  SVN Rev[3602]
- ADDED: modules - network_parameters: Se anade linea para ocultar
  la paginación de la grilla ya que no estaba en uso.
  SVN Rev[3597]
- FIXED: modules - dashboard: Se corrige problema de crear directorio
  cache para las cosultas rss de elastix.org, para resolverlo se
  definio un directorio /tmp/rss-cache que puede ser escrito por apache
  SVN Rev[3595]
- CHANGED: modules - userlist/index.php Little chande in message that
  appear when try deleting the admin user
  SVN Rev[3594]
- CHANGED:  - modules repositories/themes/default/js/javascript.js
  Added Commercial Addons to Default repositories
  SVN Rev[3591]

* Fri Jan 27 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-19
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-28
- CHANGED: modules - images: icon image title was changed on 
  some modules. SVN Rev[3572].
- CHANGED: modules - icons: Se cambio de algunos módulos los iconos
  que los representaba. SVN Rev[3563].
- CHANGED: modules - trunk/core/system/modules/repositories/: Se 
  modifico los archivos themes/default/js/javascript.js, index.php, 
  PalosantoRepositories.class.php para que la funcion defaultValues() 
  tenga soporte cuando se cambie los repositories a centos6. 
  SVN Rev[3562].
- CHANGED: modules - * : Cambios en ciertos mòdulos que usan grilla para
  mostrar ciertas opciones fuera del filtro, esto debido al diseño del 
  nuevo filtro. SVN Rev[3549].
- CHANGED: Modules - System: Support for the new grid layout. 
  SVN Rev[3543].
- UPDATED: modules - *.tpl: Se elimino en los archivos .tpl de ciertos 
  módulos que tenian una tabla demás en su diseño de filtro que formaba 
  parte de la grilla. SVN Rev[3541].


* Tue Jan 17 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-18
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-26
- CHANGED: system, library paloSantoNetwork.class.php, added new functions to calculate the network of an ip  address. SVN Rev[3522]
- CHANGED: Modules - System/Hardware_detector: Changes in javascript to better the message of pop-up. 
  This changes require the commit SVN Rev[3514]. SVN Rev[3518]
- DELETED: Modules - System/Hardware_detector: Removed jquery1.blockUI.js because this js already 
  exist as part of framework. SVN Rev[3518]
- CHANGED: Modules - system/modules/dashboard/themes: Changes in styles of dashboard 
  (1_style.css, 1_javascript.js) to remove a border black and better the design of applet 
  "Processes Status" in the drop-down menu (actions to start, restart, stop process). SVN Rev[3504]


* Tue Dec 20 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-17
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-23
- FIXED: Backup/Restore: fix invalid javascript syntax for object literal 
  in sortable declaration. SVN Rev[3454]
- FIXED: Hardware Detector: fix invalid javascript syntax for object 
  literal in draggable declaration. SVN Rev[3453]

* Fri Nov 25 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-16
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-18
- FIXED: Backup/Restore: prevent abuse of backup download for arbitrary
  file download. Also check whether file actually exists.
  SVN Rev[3343]
- FIXED: Backup/Restore: use rawmode on tar download, remove incorrect
  report at end of tar download
  SVN Rev[3341]
- CHANGED: Fax: make use of 'faxconfig' in fax library instead of sudo chown
  CHANGED: Backup/Restore: refresh fax configuration on restore using 
  standard fax API. SVN Rev[3340]

* Tue Nov 22 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-15
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-15
- ADDED: In spec file, added prereq elastix-fax >= 2.2.0-4 due to
  the module backup_restore
- FIXED: Dashboard: allow popup menu for process control to close
  when clicking on drop-down arrow. Fixes Elastix bug #1073.
  SVN Rev[3333]
- CHANGED: menu.xml, changed the order of userlist to 41
  SVN Rev[3329]
- FIXED: Dashboard: use grayed-out image for service that is not
  installed. Fixes Elastix bug #1075.
  SVN Rev[3322]
- CHANGED: module currency, the asterisks and word "Required field"
  were removed
  SVN Rev[3320]
- CHANGED: module dhcp_by_mac, the asterisks were removed in view
  forms, also the word "Required field"
  SVN Rev[3319]
- CHANGED:  System - Hardware detector:  Changed javascript to show
  in "Configuration of Span" the spans in 2 columns
  SVN Rev[3279]
- FIXED: DHCP Client List: /var/lib/dhcpd/dhcpd.leases may have 
  comments which must be skipped.
  SVN Rev[3273]
- FIXED: DHCP Client List: remove nested <form> tag
  SVN Rev[3272]
- FIXED: Dashboard: The privileged script hdmodelreport cannot
  use hdparm -I because doing it on a non-disk block device may
  make scaaaary error messages appear on syslog. Reorganize to
  use hdparm -i instead. Fixes Elastix bug #1055.
  SVN Rev[3266]
- FIXED: Dashboard: /usr/bin/stat reports major/minor number
  in hexadecimal. Fix accordingly.
  SVN Rev[3259]
- UPDATE: Module dashboard delete default applet communication activity.
  SVN Rev[3242]

* Sat Oct 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-14
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-13
- CHANGED: Module dashboard delete default applet communication 
  activity. SVN Rev[3241]
- ADDED: Modules - Dashboard : Added new sql script to update 
  dashboard.db. SVN Rev[3230]

* Sat Oct 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-13
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-12
- CHANGED: In spec file, changed %defattr(-, root, root) to
  %defattr(-, root, root)
- CHANGED: Modules - System: Changed menu.xml to move Applet Admin 
  from Preference to Dashboard. SVN Rev[3226]
- CHANGED: module backup_restore, added style border-radius
  SVN Rev[3223]
- CHANGED: module dashboard, added border radius to the styles
  SVN Rev[3221]
- CHANGED: Dashboard: System: display "Uptime" in Spanish translation.
  SVN Rev[3212]
- CHANGED: Dashboard: Hard disks: switch graph type to plot3d2
  SVN Rev[3211]
- CHANGED: Dashboard: Hard disks: attempt to report hard disk model
  for softraid devices.
  SVN Rev[3207]
- CHANGED: Dashboard: Hard disks: attempt to report hard disk model
  for LVM mappings. Does not work (yet) for softraid devices.
  SVN Rev[3206]
- FIXED: Modules - Summary by extension: Changed the image flecha.png
  with background blank with transparent as background.
  CHANGED: Modules - menu.xml: Changed label of summary by extension
  by summary and User Management with users.
  SVN Rev[3204]
- CHANGED: Dashboard: Hard disks: attempt to report hard disk model
  for physical partitions. Does not work (yet) for LVM mappings.
  SVN Rev[3203]
- FIXED: Dashboard: Hard disks: changed implementation of disk usage
  query to use privileged script, required for /var/spool/imap
  SVN Rev[3202]
- FIXED: Modules - Backup/Restore: Fixed template in backup restore.
  Columns Other has a bad position compare with others columns in
  Chrome. Support by elastixNeo Theme.
  SVN Rev[3200]
- FIXED: Dashboard: Hard disks: new layout with used space for
  selected directories.
  SVN Rev[3197]
- FIXED: Dashboard: process states must be capitalized. Also 
  internationalized properly (Spanish).
  FIXED: Dashboard: process names internationalized properly (Spanish).
  SVN Rev[3195]
- CHANGED: Dashboard: System Resources: define single class for 
  percentages on gauges
  CHANGED: Dashboard: System Resources: use one decimal, not two,
  for percentages
  CHANGED: Dashboard: System Resources: remove space between value
  and percent sign
  SVN Rev[3194]
- CHANGED: Dashboard: update icons
  SVN Rev[3193]
- CHANGED: Dashboard: allow closing of process control menu by 
  clicking anywhere in the document.
  SVN Rev[3192]
- FIXED: Dashboard: change to commit 3142 - value displayed for CPU 
  Usage was really intended to be CPU Speed but was using the wrong counter.
  SVN Rev[3190]
- FIXED: Dashboard: System resources applet - values must also have 
  nowrap specification.
  SVN Rev[3189]
- CHANGED: Dashboard: implement process control menu in System Process applet.
  SVN Rev[3188]
- CHANGED: system menu.xml, module applet_admin now can be accessed
  by users of group extension and operator by default
  SVN Rev[3184]
- FIXED: Dashboard: fixed path for background image in CSS.
  SVN Rev[3180]
- CHANGED: module hardware_detector, in theme elastixneo a style of
  "border:none;"is set for the tds that have a class "filterForm"
  SVN Rev[3179]
- CHANGED: Dashboard: new layout for process list with icons
  SVN Rev[3177]
- CHANGED: Dashboard: new Twitter icon, required for previous commit.
  SVN Rev[3175]
- CHANGED: Dashboard: new layout for RSS feed, now includes news date 
  and Twitter link.
  SVN Rev[3174]
- CHANGED: Dashboard: add numeric percents as direct values on top of gauges.
  SVN Rev[3146]
- FIXED: Dashboard: CpuUsage is a percent value, not a megahertz value.
  SVN Rev[3142]
- CHANGED: module currency, the module title is now handled by the framework
  SVN Rev[3131]
- CHANGED: module time_config, the module title is now handled by the framework
  SVN Rev[3129]
- CHANGED: module backup_restore, icon with image 1x1.gif was removed
  SVN Rev[3127]
- CHANGED: module backup_restore, the module title is now handled by
  the framework
  SVN Rev[3126]
- CHANGED: module hardware_detector, the module title is now handled
  by the framework
  SVN Rev[3125]
- CHANGED: module shutdown, the module title is now handled by the framework
  SVN Rev[3124]
- CHANGED: module userlist, the module title is now handled by the framework
  SVN Rev[3123]
- FIXED: Modules - Dashboard: Change the background-color of clase .porter
  to #f6f6f6 to theme elastixNeo
  SVN Rev[3121]
- CHANGED: module dhcp_by_mac, the module title is now handled by the framework
  SVN Rev[3119]
- CHANGED: module dhcp_clientlist, the module title is now handled by
  the framework
  SVN Rev[3118]
- CHANGED: module dhcp_server, the module title is now handled by the framework
  SVN Rev[3117]
- CHANGED: module network_parameters, the module title is now handled
  by the framework
  SVN Rev[3116]
- CHANGED: module applet_admin, now the module title is handled by the framework
  SVN Rev[3115]
- CHANGED: module dashboard, changed the title of module to "dashboard"
  SVN Rev[3114]
- CHANGED: module dashboard, changed the location of leyend for graphic
  in applet "Performance Graphic"
  SVN Rev[3108]
- CHANGED: module dashboard, the module dashboard was redesigned
  SVN Rev[3106]
- FIXED: Modules - Userlist: Add validation when the administrator user
  change your password account , this action  must not to destroy the
  current session
  SVN Rev[3103]
- CHANGED: Dashboard: allow image width and height to be specified 
  independently.
  SVN Rev[3099]
- CHANGED: Dashboard: add extra parameters to force fixed size in image tag.
  SVN Rev[3092]

* Thu Oct 13 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-12
- FIXED: Hardware Detector: do not offer an echo canceller option
  for the D-Channel of a BRI span.
  SVN Rev[3080]
- FIXED: Hardware Detector: Commit 3067 introduces a regression - ISDN
  PRI spans no longer offer a "Span Parameters" option. Fix by
  changing media type test.
  SVN Rev[3079]

* Mon Oct 10 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-11
- CHANGED: module hardware_detector,changed the word "ISDN"
  to "PRI" or "BRI"
  SVN Rev[3067]

* Fri Oct 07 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-10
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-8
- FIXED: module hardware_detector, in some cards the word
  "HDLCFCS" identify a hardware controlled
  SVN Rev[3064]
- Hardware Detector: add --hw_dtmf=YES to wancfg_dahdi invocation 
  to enable hardware DTMF detection. Fixes Elastix bug #967.
  SVN Rev[3062]
- CHANGED: module dhcp_by_mac, deleted the folder js because
  it is no longer necessary
  SVN Rev[3033]
- CHANGED: module dashboard, changed the regular expression
  to parse the extensions and trunks
  SVN Rev[3019]

* Wed Sep 28 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-9
- FIXED: module dhcp_by_mac, when user press enter in filter 
  text box, the page is redirected to the form for button
  "Assign ip Address". Now it shows the results of the filter
  SVN Rev[3004]

* Tue Sep 27 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-8
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-5
- CHANGED: changed the password "elastix456" of AMI to the
  password set in /etc/elastix.conf
  SVN Rev[2995]

* Thu Sep 22 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-7
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-4
- FIXED: Hardware Detector: remove --smg parameter from 
  wancfg_dahdi invocation. This allows recent wanpipe-utils
  to configure Sangoma BRI cards as DAHDI cards, since wanpipe-3.5.23.
  SVN Rev[2974]
- ADDED:  System - Currency: Add new currency (Swiss Franc)
  SVN Rev[2958]

* Wed Sep 07 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-6
- ADDED: In spec file, added conflicts elastix-agenda < 2.2.0-1
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-3
- FIXED: system - dashboard: Links to access calendar events 
  do not work, this bug was solved adding new variable into
  the url like event_date. This changes require commit SVN[2955].
  SVN Rev[2956]
- CHANGED: module userlist, in view mode the asterisks and word
  required were removed
  SVN Rev[2946]
- FIXED: modules - dashboard: fixed bug in applet Faxes from 
  dashboard module the link to download a fax do not work.
  SVN Rev[2943]
- FIXED: module hardware_detector, the popup for configuration of
  span and span parameters appeared on the top of the page. Now
  these popups appear near to the link
  SVN Rev[2932]

* Fri Aug 26 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-5
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-2
- FIXED: module dashboard, some iax trunks was not displayed on
  applet communication activity, according to mantis bug #962
  SVN Rev[2923]
- FIXED: Hardware Detector: add relaxdtmf=yes as a default parameter
  for chan_dahdi.conf . Required for proper Sangoma analog FXS support.
  SVN Rev[2920]
- CHANGED: module dashboard, added a validation in case the email
  password is incorrect
  SVN Rev[2919]
- CHANGED: module dashboard, the ajax requests for applets data is
  now done one by one, this increase the velocity when changes to
  other tab
  SVN Rev[2896]

* Fri Aug 05 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-4
- CHANGED: module dashboard, popup in applet telephony hardware
  was appering anywhere. Now it appears near to the applet
  SVN Rev[2876]
- FIXED: Hardware Detector: Some drivers, such as opvxg400, do not
  accept an echocanceller setting on a control channel, not even 
  'none'. Therefore, the echocanceller is instead left to its 
  default value (which is assumed to be 'none')
  SVN Rev[2875]

* Thu Aug 04 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-3
- CHANGED: In Spec file, moved file switch_wanpipe_media to /usr/sbin

* Wed Aug 03 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-2
- FIXED: Network Parameters: use smarty assign for error message
  instead of raw echo.
  SVN Rev[2870]
- FIXED: Network Parameters: relax hostname validation in order 
  to accept localhost.localdomain.
  SVN Rev[2869]
- FIXED: Network Parameters: DNS 2 can be blank, so use --dns2 
  only with nonempty parameter
  SVN Rev[2868]

* Tue Aug 02 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-1
- FIXED: module repositories, the active repos from other type 
  (main or others) were deactivated. Now these active repos remain active
  SVN Rev[2864]
- CHANGED: SQL script, userlist-profile in system, mejor definición 
  del formato.
  SVN Rev[2857]
- CHANGED: In Spec file changed prereq elastix >= 2.2.0-1

* Fri Jul 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-18
- FIXED: System - Backup Restore: Fixed bug where a backup of 
  mailbox of emails cannot be restored. SVN Rev[2850]
- CHANGED: Module dashboard, RSS URL was changed. SVN Rev[2849]
- CHANGED: module repositories, changed the word "type" to "Repo"
  SVN Rev[2830]
- CHANGED: Hardware Detector: implement modification of span 
  parameters (priority, framing, coding, LBO) for ISDN digital 
  spans. SVN Rev[2824]
- CHANGED: Hardware Detector: implement interface for switching 
  ISDN media type (E1 or T1) for Sangoma digital cards. SVN Rev[2824]
- CHANGED: Hardware Detector: fix a few incorrect English translations
  SVN Rev[2824]
- FIXED: Hardware Detector: dahdiconfig - tighten up parameter 
  validation for span parameters. SVN Rev[2824]
- CHANGED: Hardware Detector: remove unexplained initializing of
  tables with data that is always removed on hardware detection.
  SVN Rev[2823]
- CHANGED: Hardware Detector: dahdiconfig must only write crc4 
  for E1 spans. SVN Rev[2819]
- ADDED: module repositories, added some translations to other 
  languages. SVN Rev[2804]
- FIXED: module packages, the packages were not searched in repos 
  extras and epel due to a wrong database name. The problem was 
  fixed and now it is also searched in repos extras and epel.
  SVN Rev[2802]
- CHANGED: module repositories, the repositories were divided into 
  three categories "main", "others" and "all". SVN Rev[2801]
- CHANGED: module backup_restore, changed the place of the buttons 
  save and cancel. SVN Rev[2797]
- CHANGED: module userlist, now a user can have an empty extension.
  SVN Rev[2791]
- ADDED: module userlist, added a new action to edit a user extension
  from other module. SVN Rev[2787]
- CHANGED: System: fix license declaration on all helpers. SVN Rev[2781]
- FIXED: module userlist, fixed security hole when editing a user
  SVN Rev[2780]
- CHANGED: module channelusage, when there is no data to show 
  a jpgraph error was displayed. Now in this case a blank image 
  with the message "Nothing to show yet" and the title of it is 
  displayed. SVN Rev[2778]
- CHANGED: Hardware Detector: make use of dahdiconfig helper for 
  echocanceller and span configuration. As a side effect, fix 
  unnecessary repeating of dahdi restart when modifying echocanceller 
  for a span. SVN Rev[2775]
- CHANGED: Hardware Detector: fix misspelling of KB1 as KBL. SVN Rev[2775]
- ADDED: Hardware Detector: Introduce 'dahdiconfig' privileged 
  helper. This makes use of the elastix-helper framework introduced 
  in commit 2683. SVN Rev[2771]

* Wed Jun 29 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-17
- FIXED: module packages, initializa the variable $filtroGrep in
  function getPackagesInstalados
  SVN Rev[2768]
- FIXED: module packages, when the state is installed, the search
  looks for a release match also. Now the match is only by the
  package name
  SVN Rev[2761]

* Fri Jun 24 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-16
- CHANGED: IN spec file change prereq elastix >= 2.0.4-25
- FIXED: database network.db, the first 20 registers of table 
  dhcp_conf were deleted because these data were inappropriate.
  SVN Rev[2745]
- FIXED: Hardware Detector: fix database fill using wrong field 
  from configuration. SVN Rev[2744]
- CHANGED: Hardware Detector: refactored code into two methods 
  for clarity. SVN Rev[2744]
- FIXED: System - Hardware_detector: missing image images/pci.png 
  in hardware detector module. SVN Rev[2743]
- CHANGED: System - Backup_restore:   Function used to create 
  emails accounts were changes because before are in misc.lib.php  
  and now are in palosantoEmail.class.php. 
  This commit require SVN Rev[2738]. SVN Rev[2741]
- CHANGED: System - Hardware Detector: Missing images pci.png 
  this doesn't appear in the title of modules. SVN Rev[2724]
- NEW:     Security - Change Password: New module Change Password 
  allow to change the passwords of freePBX and enable or disables 
  the access per browser to the freePBX non-Embedded. SVN Rev[2724]

* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-15
- CHANGED: System, changed order of update child's, now the first 
  is repositories and the second is packages. SVN Rev[2723]
- CHANGED: System/Hardware Detector: (trivial) mark some methods 
  as private. SVN Rev[2716]
- CHANGED: System/Repositories: re-implement module library using 
  repoconfig helper.  Requires commit 2683,2706. SVN Rev[2715]
- CHANGED: System/Repositories: replace uses of deprecated ereg 
  with preg_match. SVN Rev[2715]
- CHANGED: System/Repositories: (trivial) mark two methods as 
  private. SVN Rev[2715]
- ADDED: System/Repositories: Introduce 'repoconfig' privileged 
  helper. This makes use of the elastix-helper framework introduced
  in commit 2683. SVN Rev[2714]
- CHANGED: System/DHCP by MAC: rewrite module library around the 
  concept of network.db database directing changes to 
  /etc/dhcpd.conf, not the other way around as before. Also make 
  use of "dhcpconfig --refresh" helper. Requires commit 2683,2706.
  SVN Rev[2713]
- FIXED: System/DHCP by MAC: fix long-standing bug in which 
  /etc/dhcpd.conf with no host specifications resulted in inability 
  to create first host specification, as side effect of reworking 
  module library. SVN Rev[2713]
- CHANGED: System/DHCP by MAC: make use of _tr() for translations 
  instead of $arrLang. SVN Rev[2713]
- ADDED: System/DHCP by MAC: provide Spanish translation. SVN Rev[2713]
- CHANGED: System/DHCP by MAC: add --refresh option for dhcpconfig
  SVN Rev[2712]
- ADDED: System/DHCP Server: Rework dhcpconfig in preparation 
  for alternate configuration of dhcpd.conf file. SVN Rev[2711]
- CHANGED: System/DHCP by MAC: remove dead code and mark two 
  methods as private in class paloSantoDHCP_Configuration.
  SVN Rev[2710]
- CHANGED: System/Date Time: re-implement modification of date 
  parameters using dateconfig helper. Requires commit 2683,2706.
  SVN Rev[2707]
- ADDED: System/Date Time: Introduce 'dateconfig' privileged 
  helper. This makes use of the elastix-helper framework 
  introduced in commit 2683. SVN Rev[2705]
- CHANGED: System/Date Time: (trivial) Sync module to be 
  identical in 1.6 and 2.0. SVN Rev[2704]
- CHANGED: System/DHCP Server: re-implement modification of 
  network parameters using dhcpconfig helper. Requires commit 
  2683. Also, remove dead code resulting from the switch. 
  SVN Rev[2702]
- ADDED: System/DHCP Server: Introduce 'dhcpconfig' privileged 
  helper. This makes use of the elastix-helper framework 
  introduced in commit 2683. SVN Rev[2701]
- CHANGED: System/DHCP Server: No sudo required for service dhcpd 
  status. SVN Rev[2700]
- CHANGED: System/DHCP Server: Mark two template functions as 
  private. SVN Rev[2700]
- CHANGED: System/DHCP Server: Prevent access to undefined indexes 
  on DHCP IPs not in network for current interfaces. SVN Rev[2700]
- DELETED: System/DHCP Server: Remove sysmanip.lib.php. This 
  library has never worked due to requiring /sg/bin/sudo 
  which does not exist in Elastix. Additionally the result of 
  the only method called is never used as is, and the only 
  values used are assigned from different sources. SVN Rev[2698]
- CHANGED: System/DHCP Server: (trivial) Sync index.php to be 
  identical in 1.6 and 2.0. SVN Rev[2697]
- CHANGED: System/Network Parameters: re-implement modification 
  of network parameters using netconfig helper. Requires commit
  2683. Also, remove dead code resulting from the switch. 
  SVN Rev[2694]
- FIXED: System/Network Parameters: netconfig - several settings 
  should always exist even if they were previously absent. 
  SVN Rev[2693]
- CHANGED: System/Network Parameters: Invocation of netconfig 
  --hostname should also modify /etc/hosts. SVN Rev[2692]
- ADDED: System/Network Parameters: Introduce 'netconfig' 
  privileged helper. This makes use of the elastix-helper 
  framework introduced in commit 2683. SVN Rev[2689]
- CHANGED: Modules - Trunk: The ereg function was replaced by 
  the preg_match function due to that the ereg function was 
  deprecated since PHP 5.3.0. SVN Rev[2688]

* Tue May 31 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-14
- CHANGED: The split function of these modules was replaced by
  the explode function due to that the split function was
  deprecated since PHP 5.3.0. SVN Rev[2668][2650]
- CHANGED: Module Time Config, se cambio de lugar al módulo time 
  config, paso de framework a modules/core/system. SVN Rev[2666]
- FIXED: module dashboard, the applet communication activity was 
  not displaying the correct number of trunks. Now it displays 
  the number of trunks sip and iax. SVN Rev[2624]

* Tue May 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-13
- FIXED: module packages, fixed pagination. SVN Rev[2615]
- FIXED: System - dashboard: Fixed bug [#846] from bugs.elastix.org, 
  where script update from version 10 to version 11 is wrong and 
  should be from 10 to 13. SVN Rev[2613]
- FIXED: module network_parameters, text required field was 
  displayed in the view. Now its only displayed when it is edit mode
  SVN Rev[2600]

* Wed Apr 27 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-12
- FIXED: module user_list, security hole, a non administrator user
  can access to the information of other users. Now he can only
  access to his own information
  SVN Rev[2547]
- ADDED: updated sql file for database dashboard.db, added a new
  column called username to the table activated_applet_by_user
  SVN Rev[2543]
- CHANGED: module applet_admin, now the activated applets are
  showed depending on user
  SVN Rev[2542]
- CHANGED: module dashboard, now the applets are showed according
  to the applets activated by the specific login user
  SVN Rev[2541]
- CHANGED: In Spec file, changed prereq of elastix to 2.0.4-19

* Tue Apr 12 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-11
- FIXED:     System - hardware_detector:  Fixed bug where to 
  install a mISDN hardware this required to do a yum install
  mISDN but this process do not have misdnportinfo file which 
  is in the mISDNuser package and mISDN not required mISDNuser 
  but mISDNuser required mISDN. SVN Rev[2535]
- CHANGED:   System - Hardware_detector:  New icons and images
  to improve the style of hardware detector module. SVN Rev[2534]
  FIXED:     System - Hardware_detector:  Fixed bug where 
  channels of "Channelbank Xorcom" don't show correctly the 
  states of Spans(channels). For more details:  
  http://bugs.elastix.org/view.php?id=808. SVN Rev[2534]
- FIXED: fix broken wanpipe hardware detection by adding /usr/sbin
  to path in hardware_detector. Otherwise the command 
  /usr/sbin/wanrouter is not found. SVN Rev[2533]

* Tue Apr 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-10
- CHANGED: module userlist, eliminated the field Retype Webmail 
  Password. SVN Rev[2515]
- CHANGED: module hardware_detector, changed the words 
  "Channel detected and not used" to 
  "Channel detected and not in service" and 
  "Channel detected and in use" to 
  "Channel detected and in service". SVN Rev[2510]

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED: module shutdown, unnecessary word "Required field". 
  That word is not longer showed. SVN Rev[2462]
- FIXED:  system - network_parameters:  Fixed Bug 
  "http://bugs.elastix.org/view.php?id=723" where if an user 
  put the parameter GATEWAY in 
  /etc/sysconfig/network-scripts/ifcfg-dev 
  (ifcfg-dev === ifcfg-eth0 or ifcfg-eth1) the GATEWAY by default
  will be read in this files and not from /etc/sysconfig/network 
  because this files have a major priority that network file. 
  SVN Rev[2450]
- FIXED: Fixed Bug "http://bugs.elastix.org/view.php?id=735" 
  where appear a message "Couldn't connect to server" when a FTP
  server was not entered in the form. SVN Rev[2444]
- ADDED: module repositories, added a new button called default 
  which puts the marks checks to the default installation state.
  SVN Rev[2416]
- CHANGED: module userlist, now the field name is not required 
  to create a new user. SVN Rev[2410]

* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-10
- CHANGED: update of dashboard.db, changed the code of applets 
  and added a new column called icon. SVN Rev[2379]
- CHANGED: module dashboard, it validates the case that there 
  is not internet access or the elastix web server is down.
  SVN Rev[2372]
- CHANGED: Module Dashboard, Rename style, javascript and tpls
  files, now are 1_style.css, 1_javascript.js and applets.tpl.
  SVN Rev[2369]
- CHANGED: Module Dashboard, add language word "loading", 
  delete files jquery* now action will be in jquery elastix 
  framework. SVN Rev[2368]
- CHANGED: module dashboard, the applets data is loaded using 
  the request function, the index code is now more generic, 
  also a loading image is showed while the applet data is being 
  loaded. SVN Rev[2367]
- CHANGED: module packages, the columns installed and options 
  were merged into one column called status. SVN Rev[2342]
- CHANGED: module shutdown, changed the informative message 
  to shutdown the machine by web interface. SVN Rev[2340]
- CHANGED: module dhcp_server, changed the label "Start range 
  of IPs" to "Starting IP Address", "End range of IPs" to 
  "Ending IP Address" and "IP Address Lease Time" to 
  "Lease Time". SVN Rev[2338]
- ADDED: module system, added a file .sql in the folder update 
  for changing the name from Hard Drivers to Hard Drives to the 
  id = 3. SVN Rev[2335]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format 
  to sql script for administer process install, update and delete
  SVN Rev[2332]
- CHANGED:   Put lines transaccion and commit in script 
  db/install/acl/1_userlist-profile.sql. SVN Rev[2320]
- CHANGED: changed the db.info of fax to the format used in 
  elastix-dbprocess. SVN Rev[2316]
- CHANGED: created the folders install, delete and update and 
  added in install the sql scripts also the file db.info has
  the new format that is used in elastix-dbprocess. SVN Rev[2314]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED:  In Spec file, add functionality to support create or
  update databases using script.sql with elastix-dbprocess
- CHANGED:  In Spec file, add lines to create tables of profiles
  in acl.db
- NEW:      System - setup: New file db.info, this file has 
  information about the database that is used in process to 
  install, update or delete. SVN Rev[2309]
- CHANGED:  menu.xml to support new tag "permissions" where has 
  all permissions of group per module and new attribute "desc" 
  into tag  "group" for add a description of group. 
  SVN Rev[2294][2299]
- CHANGED: module currency,added the Russian Ruble. SVN Rev[2281]
- NEW:     new tabs or folders in trunk/core to create a new 
  rpms im and extras it is for better organization. SVN Rev[2266]
- ADDED:   module currency, added currency Great British Pound
  SVN Rev[2264]

* Mon Jan 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:  New styles to hardware_detector module, and change 
  function to update echo canceller parameters by ajax. 
  SVN Rev[2202]

* Wed Dec 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- FIXED: Fixed bug [#646] in bugs.elastix.org about FTP Backup 
  de backup_restore where do not work backup in ftp servers on 
  windows, the solution was to show the list of ftp with "-la ." 
  before was ".".

* Tue Dec 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGE: module dhcp_server, file /etc/dhcpd.conf is written 
  with value of nis-domain and domain-name equal to 
  asterisk.local . Now is written with the real host-name of 
  the server. SVN Rev[2161]

* Thu Dec 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED: Additionals libs, move libs from additional folder 
  to each specify module. By example paloSantoNetwork.class.php
  SVN Rev[2150]
- CHANGE: currency module, changed the symbol of the salvadorian
  colon. SVN Rev[2144]

* Mon Dec 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-40
- CHANGED: In spec file add lines in post and install about openfire
  and hardware_detector from elastix.spec 
- FIXED: fix typo in null redirection. SVN Rev[2125]
- FIXED: move dahdi_genconf modules after second attempt to 
  shutdown dahdi, and fix comment. SVN Rev[2125]
- CHANGED:  Added option (-s: disable dahdi_genconf). 
  By default dahdi_genconf is true to execute but the option -s 
  with hardware_detector do not execute "dahdi_genconf modules"
  SVN Rev[2123]
- ADDED:    New file hardware_detector in setup folder of system, 
  it was move from additionals. SVN Rev[2110]
- FIXED:  Fix bug http://bugs.elastix.org/view.php?id=610 in 
  module currency where some label of languages are not exist. 
  This soluction is for both elastix. SVN Rev[2098]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-39
- CHANGED: Add new Prereq openfire, wanpipe-util, dahdi
- CHANGED: massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2002]
- FIXED: Packages: fixed syntax error in hr language. SVN Rev[2001]
- CHANGED: Backup/Restore: stop assigning template variable "url"
  directly, and remove nested <form> tag. SVN Rev[1193]
- CHANGED: Repositories: stop assigning template variable "url" 
  directly, and remove nested <form> tag. SVN Rev[1986]
- CHANGED: Dashboard: switch to use of palosantoGraphImage.lib.php 
  for graph generation. Requires commits 1964,1969 to work properly.
  SVN Rev[1973]
- CHANGED: Shutdown: remove link that cannot be accessed anyway 
  during shutdown or reboot. SVN Rev[1971]
- FIXED:  Fixed bug in dhcp server where appear html code 
  "<b>Time of client refreshment</b>" the tags are put in tpl file
  SVN Rev[1959]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-38
- Fixed bug in dhcp server where appear html code "<b>.</b>" 
  it occur because now html is scaped in palosantoForm. SVN Rev[1957]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-37
- CHANGED:  Harware detector module better design and functionality
  [#334] SVN Rev[1954]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-36
- FIXED: make module aware of url-as-array in paloSantoGrid.
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template. They are not 
      required, since the template already includes a proper <form> tag 
      enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 in 
      order to work properly.
  SVN Rev[1913]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-35
- CHANGED: Updated the Bulgarian language elastix. SVN Rev[1857]

* Tue Oct 26 2010 Eduardo Cueva <eceuva@palosanto.com> 2.0.0-34
- CHANGED: Move line elastix-menumerge at beginning the "%post" in spec file. 
  It is for the process to update.

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-33
- FIXED:   Fixed security bug with audio.php and popup.php where an user can be
  download files system without authentication. 
  See in http://bugs.elastix.org/view.php?id=552 [#522] SVN Rev[1830]
- CHANGED:  Updated fr.lang. SVN Rev[1825]
- ADDES:    New lang file, fa.lang (Persian). SVN Rev[1823]
- FIXED:  fixed bug 359 about permissions in userlist module. 
  See in http://bugs.elastix.org/view.php?id=359. SVN Rev[1816]

* Mon Sep 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-32
- FIXED: clean up any stale group membership before assigning membership for new user. Part of fix for Elastix bug #515. SVN Rev[1759]

* Tue Sep 14 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-31
- CHANGED:    Move css file to css folder in hardware_detector in the same way to js files to support framework. Rev[1737]
- FIXED: Do NOT recursively change owner of /tftpboot to root:root after a backup or restore. This causes failures to configure future endpoint provisionings because the web interface fails to write to files now owned by root (permission denied). Instead, just restore permission to /tftpboot, not to its contents. Rev[1730] 

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-30
- CHANGED: Prereq elastix-2.0.0-34

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-29
- FIXED: Fixed bug #420 from bugs.elastix.org. No Trunks was showed. Rev[1708]

* Tue Aug 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-28
- FIXED: Work around PHP bug (forget to close httpd file descriptors on PHP fork()) for the case of dhcpd restart. Requires SVN commit #1696. Rev[1702]
- FIXED: Removed spurious references to /sg/bin/ directory from StickGate. Rev[1702]

* Thu Aug 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-27
- FIXED:  Add function existSchemaDB() lib backup/restore. bugs.elastix.org [437]. Rev[1685]

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-26
- CHANGED: Change help files in Dashboard, DHCP Client List, Hardware Detector, Backup/Restore, Currency, Applet Admin. Rev[1679]
- FIXED:   Fix bug about automatic backup Rev[1679] [#400] bugs.elastix.org.

* Thu Jul 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-25
- CHANGED: Some labes and titles were improved.

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-24
- CHANGED: Change file automatic_backup in backup/restore to /var/www/backup/ (fixed security bug). Rev[1646]
-          Textfields and its names have been improved for being easier to understand. Rev[1640].
- FIXED:   Summary By extension, querys has been improved, now the data is from channel y dstchannel. Rev[1640]
-          Fixed security bug(hardware_detector). Rev[1650]
-                Script is not authenticated session
-                Script settings does not validate or Datacard past
-                Script has no protection against SQL injection (Datacard)
-                Script manages hardware parameters in /etc/dahdi/system.conf, and registration cards

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-23
- FIXED: Update RPM not changed the menu link vtigercrm.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-22
- CHANGED: Link vtigercrm was changed. The script vtigercrmWrapper.php was deleted now is obsolte.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-21
- CHANGED: String connection database root, in module backup restore.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-20
- CHANGED: String connection database asteriskuser, asterisk in module dashboard.

* Wed Jul 21 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- NEW:     Include framework jquery 1.3.2 and jquery-ui 1.7.2 in js folder to mount files css and js (dashboard).
- CHANGED: index.php from dashboard was added a new line to not include the last framework jquery because there are some problems with the last version.
-          Support jquery to the framework, now the framework load js an d css from module backup_restore. 
-          Support format themes/default/css and themes/default/js

* Thu Jul 01 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- CHANGED:  Dashboard : The regular expressions were improved for show the numbers of trunks in applet COMMUNICATION ACTIVITY 
* Mon Jun 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- CHANGED: Dashboard module, change how to show parameter network traffic in COMMUNICATION ACTIVITY. New word in lang.

* Mon Jun 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- CHANGED: Links for RSS in module dashboard to reference new webside elasti.org. 

* Mon Jun  7 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-15
- Change files languages, some var were corrected.

* Fri Apr 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-14
- Change the style of Dashboard module, here the burble can move with the applet content, it is not static.
- Hide the icon of configure or register card in hardware detector....

* Fri Mar 26 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- Fixed bug in module network parameters, now defined localhost and localhost.localdomain to 127.0.0.1.

* Fri Mar 19 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- Change info to web services (webservice.elastix.org) 

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-11
- Support save position applets.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- New applet register hardware telephony in module dashboard.
- Register hardware telephony in hardware detector.

* Tue Jan 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- Function getParamenter was removed in each module.
- Backup-retore validation version backup and restore.
- Hardware detector improved support for config signaling and framed.

* Wed Dec 30 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-8
- Fixed regular expresion in module dhcp by mac.
- Hide bar of admin applet in module dashboard.
- New module applet admin for manages, this in preferences.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- Comment string trunk register, the value is not correct.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Improved look and perfomance module dashboard, actions drap and drop.
- Fixed bug hardware detector module, images not found.

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-5
- Improved module backup/restore, now have automatic backups.
- New module dhcp client list.
- New module dhcp by mac.

* Fri Oct 23 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Improved module hardware detector.
- New action, FTP Backup in module backup/restore.

* Sat Oct 17 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add accion uninstall rpm.
- Validation login when a user administrator, now user will see the main menu sysinfo.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.
