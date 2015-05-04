%define modname reports

Summary: Elastix Module Reports 
Name:    elastix-reports
Version: 3.0.0
Release: 9
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
#Source0: %{modname}_%{version}-7.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix-framework >= 3.0.0-1
Prereq: asterisk

%description
Elastix Module Reports

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT/var/spool/elastix-infomodulesxml/%{name}-%{version}-%{release}/infomodules

# Files provided by all Elastix modules
#mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/apps/
bdir=%{_builddir}/%{modname}
for FOLDER0 in $(ls -A modules/)
do
		for FOLDER1 in $(ls -A $bdir/modules/$FOLDER0/)
		do
				mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/apps/$FOLDER1/
				for FOLFI in $(ls -I "web" $bdir/modules/$FOLDER0/$FOLDER1/)
				do
					if [ -d $bdir/modules/$FOLDER0/$FOLDER1/$FOLFI ]; then
						mkdir -p $RPM_BUILD_ROOT/usr/share/elastix/apps/$FOLDER1/$FOLFI
						if [ "$(ls -A $bdir/modules/$FOLDER0/$FOLDER1/$FOLFI)" != "" ]; then
							mv $bdir/modules/$FOLDER0/$FOLDER1/$FOLFI/ $RPM_BUILD_ROOT/usr/share/elastix/apps/$FOLDER1/
						fi
					elif [ -f $bdir/modules/$FOLDER0/$FOLDER1/$FOLFI ]; then
							mv $bdir/modules/$FOLDER0/$FOLDER1/$FOLFI $RPM_BUILD_ROOT/usr/share/elastix/apps/$FOLDER1/
					fi
				done
				case "$FOLDER0" in 
					frontend)
						mkdir -p $RPM_BUILD_ROOT/var/www/html/web/apps/$FOLDER1/
						if [ -d $bdir/modules/$FOLDER0/$FOLDER1/web/ ]; then
							mv $bdir/modules/$FOLDER0/$FOLDER1/web/* $RPM_BUILD_ROOT/var/www/html/web/apps/$FOLDER1/
						fi
					;;
					backend)
						mkdir -p $RPM_BUILD_ROOT/var/www/html/admin/web/apps/$FOLDER1/
						if [ -d $bdir/modules/$FOLDER0/$FOLDER1/web/ ]; then
							mv $bdir/modules/$FOLDER0/$FOLDER1/web/* $RPM_BUILD_ROOT/var/www/html/admin/web/apps/$FOLDER1/
						fi	
					;;
				esac
		done
done

# Additional (module-specific) files that can be handled by RPM
#mkdir -p $RPM_BUILD_ROOT/opt/elastix/
#mv setup/dialer

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p                             $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mkdir -p                             $RPM_BUILD_ROOT/usr/share/elastix/libs/
mv setup/paloSantoCDR.class.php      $RPM_BUILD_ROOT/usr/share/elastix/libs/
mv setup/paloSantoTrunk.class.php    $RPM_BUILD_ROOT/usr/share/elastix/libs/
mv setup/paloSantoRate.class.php     $RPM_BUILD_ROOT/usr/share/elastix/libs/
mv setup/paloSantoQueue.class.php    $RPM_BUILD_ROOT/usr/share/elastix/libs/
mv setup/                            $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%pre
mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
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
  echo "Delete Reports menus"
  elastix-menuremove $pathModule/setup/infomodules

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

%changelog
* Tue Dec  2 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Reports: change file and directory ownership in package to root
  instead of asterisk. Part of fix for Elastix bug #2062.
  SVN Rev[6791]

* Fri Nov 21 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-9
- CHANGED: reports - Build/elastix-reports.spec: update specfile with latest
  SVN history. Bump Release in specfile.

* Fri Jun 13 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-8
- CHANGED: reports - Build/elastix-reports.spec: update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6650]

* Mon Apr 28 2014 Bruno Macias <bmacias@palosanto.com> 
- FIXED: module reports, database asteriskcdrdv wasn't creating. SQLs files
  names were changed in folder db/install/asteriskcdrdb
  SVN Rev[6609]

* Wed Apr 23 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-7
- CHANGED: reports - Build/elastix-reports.spec: update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6600]

* Wed Mar 11 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Added definition for asteriskcdrdb.queue_log table for realtime
  queue logging.
  SVN Rev[6521]
- CHANGED: SQL definitions for asteriskcdrdb were moved from elastix-firstboot
  to elastix-reports.
  SVN Rev[6520]

* Wed Feb 19 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Asterisk Logs: update log parsing for changed date format resulting 
  from update to FreePBX 2.11.
  SVN Rev[6487]

* Sat Jan 18 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-6
- CHANGED: reports - Build/elastix-reports.spec: update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6392]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Reports/Apps: translation in CDR Report-> filter (spanish)
  SVN Rev[6244]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Reports/Apps: translation in CDR Report (spanish)
  SVN Rev[6243]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Apps/Reports: translation in CDRs Reports label (spanish)
  SVN Rev[6202]

* Fri Nov 22 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Reports/Apps: language help add in Asterisk Log (english -
  spanish)
  SVN Rev[6149]

* Fri Nov 22 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - Reports/Apps: language help add in cdr report (english -
  spanish)
  SVN Rev[6148]

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
  SVN Rev[6105]

* Mon Oct 07 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: build - *.spec: Update specfile with some corrections correspondig
  to the way of remove tabs in the framework for each elastix module.
  SVN Rev[5994]

* Tue Oct 01 2013 Rocio Mera <rmera@palosanto.com> 
- DELETED: Tunk - Apps/reports: Was deleted file menu.xml. This file was
  divided in a set of files that are stored in setup/infomodules
- ADDED: Tunk - Apps/reports: Was added directory infomodules. This directory
  store xml files that are used to create elastix resources
  SVN Rev[5963]

* Wed Sep 25 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: build - *.spec: Update specfile with some corrections correspondig
  to the way of identify and distribute folders to the '/usr/share/elastix/'
  path and '/var/www/html/' path.
  SVN Rev[5945]

* Tue Sep 24 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: Trunk - Apps: Was made changes im module trunk, extension,
  time_group in order to solve some minors bugs
- CHANGED: Trunk - APPS: Was made change in lib paloSantoCDR in order to
  resolve bug related with filter param
  SVN Rev[5941]

* Fri Sep 20 2013 Rocio Mera <rmera@palosanto.com> 
- CHANGED: TRUNK - APPS/PBX: Was made changes in module extensions, trunk,
  general_settings, general_settings_admin to update parameters that can be
  configured in sip and iax device
TRUNK - FRAMEWORK: Was made changes in theme elastixneo to fis somes minors
  bugs. In addition was made changes in elxpbx schema to create a new menu
  named manager. This menu is the paren menu of sysdash, organization_manager
  and user_manager
TRUNK - APPS/Reports: Was made changes in module CDR report. The funstion of
  deleted rescord register now can only be performed by superadmin. The filters
  param was explode in order to permit do more detailed searches.
TRUNK - APPS: Search can be done using asterisk filter patterns in modules
  where the filter accept any text
  SVN Rev[5922]

* Fri Sep 20 2013 Luis Abarca <labarca@palosanto.com> 
- FIXED: reports - Build/elastix-reports.spec: Fixed bad positioning of code
  that interfered with the proper installation of the package.
  SVN Rev[5921]

* Fri Sep 13 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-4
- CHANGED: reports - Build/elastix-reports.spec: update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[5886]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- ADDED: reports - setup/infomodules.xml/: Within this folder are placed the
  new xml files that will be in charge of creating the menus for each module.
  SVN Rev[5867]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: reports - modules: The modules were relocated under the new scheme
  that differentiates administrator modules and end user modules .
  SVN Rev[5866]

* Mon Sep 09 2013 Alex Villacís Lasso <a_villacis@palosanto.com> 
- FIXED: Billing Report: move filter widgets to separate row in order to avoid
  misplacement. Fixes Elastix bug #1637.
  SVN Rev[5844]

* Tue Aug 13 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: CDR Report: update code to use new filesystem layout.
  SVN Rev[5761]
- CHANGED: Channel Usage: update code to use new filesystem layout.
  SVN Rev[5757]
- CHANGED: Channel Usage: convert uses of arrLang to _tr. Sync with trunk.
  SVN Rev[5756]
- CHANGED: Asterisk Logs: update code to use new filesystem layout.
  SVN Rev[5755]
- CHANGED: Asterisk Logs: convert uses of arrLang to _tr. Sync with trunk.
  SVN Rev[5725]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - summary_by_extension/: It was corrected a configuration in
  the web folder.
  SVN Rev[5275]
- CHANGED: trunk - missed_calls/: It was corrected a configuration in the web
  folder.
  SVN Rev[5274]
- CHANGED: trunk - graphic_report/: It was corrected a configuration in the web
  folder.
  SVN Rev[5273]
- CHANGED: trunk - dest_distribution/: It was corrected a configuration in the
  web folder.
  SVN Rev[5272]
- CHANGED: trunk - channelusage/: It was corrected a configuration in the web
  folder.
  SVN Rev[5271]
- CHANGED: trunk - cdrreport/: It was corrected a configuration in the web
  folder.
  SVN Rev[5270]
- CHANGED: trunk - billing_setup/: It was corrected a configuration in the web
  folder.
  SVN Rev[5269]
- CHANGED: trunk - billing_report/: It was corrected a configuration in the web
  folder.
  SVN Rev[5268]
- CHANGED: trunk - billing_rates/: It was corrected a configuration in the web
  folder.
  SVN Rev[5267]
- CHANGED: trunk - asterisk_log/: It was corrected a configuration in the web
  folder.
  SVN Rev[5266]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: trunk - summary_by_extension/: The svn repository for module
  summary_by_extension in trunk (Elx 3) was restructured in order to accomplish
  a new schema.
  SVN Rev[5188]
- CHANGED: trunk - missed_calls/: The svn repository for module missed_calls in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5187]
- CHANGED: trunk - graphic_report/: The svn repository for module
  graphic_report in trunk (Elx 3) was restructured in order to accomplish a new
  schema.
  SVN Rev[5186]
- CHANGED: trunk - dest_distribution/: The svn repository for module
  dest_distribution in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5185]
- CHANGED: trunk - channelusage/: The svn repository for module channelusage in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5184]
- CHANGED: trunk - cdrreport/: The svn repository for module cdrreport in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5183]
- CHANGED: trunk - billing_setup/: The svn repository for module billing_setup
  in trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5182]
- CHANGED: trunk - billing_report/: The svn repository for module
  billing_report in trunk (Elx 3) was restructured in order to accomplish a new
  schema.
  SVN Rev[5181]
- CHANGED: trunk - billing_rates/: The svn repository for module billing_rates
  in trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5180]
- CHANGED: trunk - asterisk_log/: The svn repository for module asterisk_log in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5179]

* Wed Jun 26 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Summary By Extension: tweak SQL query used to report summary, 
  attempts to fix Elastix bug #1607.
  SVN Rev[5133]
- FIXED: Summary By Extension: SVN commit 4907 introduced a regression that 
  breaks filtering by a particular extension or user. Fixed. Should fix last
  part of Elastix bug #1322.
  SVN Rev[5132]

* Mon Jun 24 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Graphic Report: fix report of DAHDI extensions that did not appear as
  part of the pie chart count. Fixes Elastix bug #1606. Additionally, remove 
  dead code and use standard translator function for date handling.
  SVN Rev[5122] 

* Thu Jun 13 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Asterisk Logs: fix date parsing to avoid interpreting plain PIDs as
  dates. Extract date from a new type of message line on asterisk boot. Remove
  unnecessary database connection parameter.
  SVN Rev[5097] 

* Fri Jun 07 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Graphic Report: convert entirely from arrLang to use of _tr().
  SVN Rev[5064]
- FIXED: Graphic Report: replace static select tag in template with a select
  from paloForm, which additionally provides for future i18n. Also fixes part of
  Elastix bug #1570.
  SVN Rev[5063]

* Thu Jun 06 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Billing Rates, Graphic Report: remove duplicate definition of
  getParameter() that gets confused by Fortify as the one used by the framework.
  SVN Rev[5058]

* Mon May 27 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-3
- CHANGED: reports - Build/elastix-reports.spec: update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[5030]

* Wed May 22 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Billing Rates: remove unnecessary and risky copy of uploaded file, and
  remove unnecessary load of same file via file() which was left unused. Pointed
  out by Fortify report.
  SVN Rev[4997]

* Thu May 09 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Missed Calls: reimplement SQL query in order to substantially reduce
  the number of records that must be examined by PHP code before generating the
  report. This allows the report to work with much larger date ranges, or in
  more busy systems, without hitting a PHP execution time timeout. Fixes Elastix
  bug #1527.
  SVN Rev[4908]

* Tue May 07 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Summary by Extension: source extension must be queried in both src and
  srcchannel. Ditto with dst and dstchannel. Also unify extension filtering on
  paloSantoCDR to look up extension on channels. Fixes Elastix bug #1545. Might
  also fix Elastix bugs #567, #707, #1322.
  SVN Rev[4907]

* Tue Apr 09 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-2
- CHANGED: reports - Build/elastix-reports.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
  SVN Rev[4815]

* Tue Jan 15 2013 Luis Abarca <labarca@palosanto.com>
- FIXED: Its no more necesary to resize the popups in certain windows of
  elastix environment. Fixes Elastix BUG #1445 - item 8
  SVN Rev[4587]

* Wed Jan 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Reports: Was made changes in libs paloSantoCDR.class.php to
  add support to multitenant architecture
  SVN Rev[4539]

* Wed Jan 02 2013 Rocio Mera <rmera@palosanto.com>
- DELETED: Apps - Reports/asteriskcdrdb: was removed sql file
  2.0.4-15.sql-3.0.0-0.sql added in commit 4537
  SVN Rev[4538]

* Wed Jan 02 2013 Rocio Mera <rmera@palosanto.com>
- ADD: Apps - Reports: Was add file 2_2.0.4-15_3.0.0-0.sql. This file add
  columns orgation_domain toout fromout to  cdr table
  SVN Rev[4537]

* Fri Dec 28 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - Reports: Was added new implementation of cdrreports module.
  This module implement support to query the cdr for calltype and organization.
  Was remove support to soap until new implementations
  SVN Rev[4535]

* Fri Dec 28 2012 Rocio Mera <rmera@palosanto.com>
- DELETE: Apps - Reports: Was delete ,module cdrreport. This module will be
  replace with a new implementation
  SVN Rev[4534]

* Fri Nov 30 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Summary by Extension: do not use or add number of calls on URL. Read
  this number from the database instead. Fixes part 2 of Elastix bug #1416.
  SVN Rev[4482]

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

* Tue Sep 25 2012 Rocio Mera <rmera@palosanto.com>
- FIXED: module cdrreport, querys to asterisk database, ringgroups now are
  solved
  SVN Rev[4294]

* Thu Sep 20 2012 Luis Abarca <labarca@palosanto.com> 3.0.0-1
- CHANGED: reports - Build/elastix-reports.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
- CHANGED: In spec file changed Prereq elastix-framework to
  elastix-framework >= 3.0.0-1
  SVN Rev[4230]

* Fri Aug 31 2012 German Macas <gmacas@palosanto.com>
- CHANGED: module - billing_rates: Change text information when edit a rate
  SVN Rev[4163]

* Tue Jun 12 2012 Luis Abarca <labarca@palosanto.com> 
- CHANGED: elastix - /: The svn repository was reconstructed for better use and
  management.
  SVN Rev[3997]

* Mon May 07 2012 German Macas <gmacas@palosanto.com>
- NEW: Missed Calls Module
  SVN Rev[3932]
- CHANGED: Reports - Missed Calls: change application form Filter and spanish words in lang
  SVN Rev[3931]

* Fri Apr 27 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-4
- CHANGED: Reports - Build/elastix-reports.spec: update specfile with latest
  SVN history. Changed release in specfile
- ADDED: Build - SPEC's: The spec files were added to the corresponding modules
  and the framework.
  SVN Rev[3855]
  SVN Rev[3837]
- FIXED: Modules - Report: The Graphic Report now display the correct data of
  custom trunks.
  SVN Rev[3825]

* Fri Mar 30 2012 Bruno Macias <bmacias@palosanto.com> 2.3.0-3
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-5
- FIXED: modules - SQLs DB: se quita SQL redundante de alter table y nuevos 
  registros, esto causaba un error leve en la instalación de el/los modulos.
  SVN Rev[3797]

* Mon Mar 26 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-2
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.3.0-3
- CHANGED: Reports - Asterisk_Logs/index.php: Little better in show filters so
  don't appear the option x when is applied the default filter
  SVN Rev[3771]

* Wed Mar 07 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-1
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.3.0-1
- CHANGED: summary_by_extension index.php add control to applied filters
  SVN Rev[3715]
- CHANGED: asterisk_log index.php add control to applied filters
  SVN Rev[3713]
- CHANGED: billing_report index.php add control to applied filters
  SVN Rev[3712]
- CHANGED: cdrreport index.php add control to applied filters
  SVN Rev[3711]
- CHANGED: little change in file *.tpl to better the appearance the options
  inside the filter when the language is spanish
  SVN Rev[3645]
- CHANGED: little change in file *.tpl to better the appearance the options
  inside the filter
  SVN Rev[3639]

* Wed Feb 01 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-14
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-30
- CHANGED: file index.php to fixed the problem with the paged. 
  SVN Rev[3624]
- FIXED: Modules - Billing Report: Actually are generated two 
  reporting lines (CDRs) for each billed call, one by the assigned 
  rate and other by 'Default' rate.

* Mon Jan 29 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-13
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-28
- CHANGED: to fixed the problem with the pagineo. SVN REv[3609].
- ADDED: modules - billing_setup: Se agrega linea en el index del modulo 
  para ocultar el pagineo del modulo ya que no era usado. SVN REv[3606].

* Sat Jan 28 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-12
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-28
- CHANGED: Modules - Summary: Changes in index.php file for change 
  the column title color in the grid of Summary module. SVN Rev[3580].
- CHANGED: modules - images: icon image title was changed on some modules.
  SVN Rev[3572].
- CHANGED: modules - cdrrport: Se cambia el nombre del boton eliminar, 
  al igual que el mensaje que sale de confirmación de eliminación. 
  SVN Rev[3571].
- CHANGED: modules - icons: Se cambio de algunos módulos los iconos que 
  los representaba. SVN Rev[3563].
- CHANGED: modules - trunk/core/reports/modules/asterisk_log/themes/
  default/filter.tpl: Se modifico el archivo _filter.tpl en cuestion 
  de diseño para que los elementos dentro del filtro no esten muy 
  distanciados. SVN Rev[3556].
- CHANGED: modules - * : Cambios en ciertos mòdulos que usan grilla 
  para mostrar ciertas opciones fuera del filtro, esto debido al 
  diseño del nuevo filtro. SVN Rev[3549].
- CHANGED: Modules - Reports: Support for the new grid layout. 
  SVN Rev[3542].
- UPDATED: modules - *.tpl: Se elimino en los archivos .tpl de ciertos 
  módulos que tenian una tabla demás en su diseño de filtro que formaba 
  parte de la grilla. SVN REV[3541].


* Tue Jan 17 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-11
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-26 
- CHANGED: modules - reports/setup/db/update/asteriskcdrdb: Se cambia el create index por un procedimiento para que verifique si existe previamente el indice caso contrario lo agrega a la tabla asteriskcdrdb.cdr. El indice es IDX_UNIQUEID relacionado al campo uniqueid. SVN Rev[3506].

* Thu Jan 05 2012 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-10
- UPDATED: modules - reports/setup/db/db.info: Changed sql scripts to do not
  remove asteriskcdrdb and ignore the action to create a backup after the 
  process unistall of RPM.

* Tue Jan 03 2012 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-9
- FIXED: Modules - Reports/Billing_rates: Fixed bug where import a file 
  is not work, because only take a first row and then return as successful 
  (don't read the others rows).
       * Bug: 1134
       * Introduced by: Eduardo Cueva
       * Since: Begin to developer the new interface of billing rates

* Fri Nov 25 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-8
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-18

* Tue Nov 22 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-7
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-15
- FIXED: Billing Setup: remove nested <form> tag
  SVN Rev[3276]

* Sat Oct 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-6
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-13

* Sat Oct 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-5
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-12
- CHANGED: Modules - Summary By Extension: changed size of popup in 
  Summary By Extension. SVN Rev[3227]
- FIXED: Modules - Summary by extension: Changed the image flecha.png 
  with background blank with transparent as background.
  CHANGED: Modules - menu.xml: Changed label of summary by extension
  by summary and User Management with users.
  SVN Rev[3204]
- CHANGED: module dest_distribution, changed the style of the module
  to adapt it to the new theme elastixneo
  SVN Rev[3198]
- CHANGED: module graphic_report, changed the way to present a graphic
  in case there is no records for queues
  SVN Rev[3191]
- UPDATED: reports modules  templates files support new elastixneo theme
  SVN Rev[3154]
- UPDATED: reports modules  templates files support new elastixneo theme
  SVN Rev[3152]

* Fri Oct 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-4
- FIXED: Modules - Billing Reports: Fixed bug where trunk SIP 
  or IAX were not showed in billing reports. SVN Rev[3065]
- CHANGED: module billing_rates, the word "required field" was 
  deleted for view mode. SVN Rev[3027]
- CHANGED: module billing_setup, the asterisks in labels and word 
  "required field" were removed for view mode. SVN Rev[3020]

* Thu Sep 22 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-3
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-4
- CHANGED: module graphic_report, deleted unnecessary asterisks
  SVN Rev[2966]
- CHANGED: module dest_distribution, deleted unnecessary asterisks
  SVN Rev[2965]
- CHANGED: module billing_report, deleted unnecessary asterisks
  SVN Rev[2964]
- CHANGED: module cdrreport, deleted unnecessary asterisks
  SVN Rev[2963]

* Fri Sep 09 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-2
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-3
- CHANGED: module cdrreport, only administrators can delete CDRs
  SVN Rev[2941]

* Fri Aug 26 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-1
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-2
- CHANGED: module cdrreport, if user does not have an extension
  assigned, a message is showed
  SVN Rev[2881]

* Tue Jul 19 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-15
- CHANGED: In spec file change prereq elastix >= 2.0.4-29
- FIXED: Modules - cdrreport: Fixed bug where any user without 
  an extension assigned could see all cdr in cdrreports.
  SVN Rev[2835]
- CHANGED: module graphic_report, in case of error no graphic 
  is showed. SVN Rev[2809]
- CHANGED: module graphic_report, when there is no data to show 
  the design was changed like used in module email_stats.
  SVN Rev[2800]
- CHANGED: module dest_distribution, when there is no data to 
  show the design is changed like used in module email_stats. 
  SVN Rev[2799]
- CHANGED: module channelusage, when there is no data to show a 
  jpgraph error was displayed. Now in this case a blank image 
  with the message "Nothing to show yet" and the title of it 
  is displayed. SVN Rev[2777]

* Wed Jun 29 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-14
- FIXED: module billing_report, if user press next or last without
  pressing show button first, the user gets a blank page. Now the
  user can press next or last without pressing show button first
  to see the data on next page
  SVN Rev[2767]
- FIXED: module summary_by_extension, instead of the accent on the
  wordsm a special character was displayed. Now the accent is showed
  SVN Rev[2760]

* Fri Jun 24 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-13
- CHANGED: IN spec file change prereq elastix >= 2.0.4-25
- FIXED: module graphic_report, changed action onclick to onchange 
  on combo box. SVN Rev[2756].
- FIXED: Bug in module Dest distribution, Error to create image.
  PieGraph class do not exists, To solve the library 
  paloSantoGraphImage.lib.php had to be included. SVN Rev[2754]
- CHANGED: Module CDR Reports, change soap name 
 class SOAP_Cdr.class.php to SOAP_CDR.class.php. SVN Rev[2750]

* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-12
- CHANGED: Modules - Trunk: The ereg function was replaced by the 
  preg_match function due to that the ereg function was deprecated 
  since PHP 5.3.0. SVN Rev[2688]

* Thu May 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-11
- ADDED: CDR Report: Add support for filter by ringgroup. SVN Rev[2617].
- FIXED: CDR Report: fix typo in Spanish translation. SVN Rev[2612]
- FIXED: CDR Report: fix date comparison bug on delete scenario - 
  dates were being compared with "d M Y" format, should be compared 
  with ISO format. SVN Rev[2611]
- CHANGED: CDR Report: report now uses CDR API with parameter arrays.
  SVN Rev[2611]
- CHANGED: paloSantoCDR: add checks for invalid parameter arrays.
  SVN Rev[2610]
- FIXED: module summary_by_extension, for numbers too big the 
  leyend could overlaps the percentages. SVN Rev[2606]
- FIXED: cdrreport: Fixed bug [#821] filters don't work if the 
  action is "delete cdr". SVN Rev[2605]
- FIXED: paloSantoCDR: refuse to process any query or delete that 
  specifies a non-null date in an invalid format. SVN Rev[2604]
- ADDED: paloSantoCDR: add support for ringgroup filtering. SVN Rev[2603]
- CHANGED: CDR Report: create new method contarCDRs that receives 
  a parameter array, and modify getNumCDR to forward to contarCDRs
  SVN Rev[2599]
- CHANGED: CDR Report: make Delete_All_CDRs() use getParam() helper.
  SVN Rev[2599]

* Tue Apr 26 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-10
- CHANGED: module cdrreport, changed class name to core_CDR
  SVN Rev[2577]
- CHANGED: module cdrreport, changed name from puntosF_CDR.class.php
  to core.class.php
  SVN Rev[2569]
- NEW: new scenarios for SOAP in cdrreport
  SVN Rev[2560]
- FIXED: module cdrreport, character '}' misplaced in confirming
  message to delete cdrs
  SVN Rev[2525]
- FIXED: Reports - billing reports: Fixed style.css, a class of
  css was written bad
  SVN Rev[2508]

* Tue Apr 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED:  Reports - Billing reports: Changes in styles and 
  tpl. SVN Rev[2506]

* Wed Mar 30 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- FIXED:  Reports - Email: Button cancel don't work in action 
  edit due to on URL put parameter "edit" where that parameter 
  has the name of rate and it is wrong because this parameter 
  is only for the action EDIT. SVN Rev[2474]

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- FIXED: Reports - Cdrreports:  
  Fix bug "http://bugs.elastix.org/view.php?id=753" this 
  require commit 2445, and change function "borrarCDRs" 
  where query to execute never receive the array of values 
  for parametrization method. SVN Rev[2446]
- FIXED: reports - cdrreports:  Fixed bug 
  "http://bugs.elastix.org/view.php?id=753" where repors of 
  call from cdr reports cannot be deleted. SVN Rev[2445]
- CHANGED: module summary_by_extension, changed the column 
  names according to the bug #756. SVN Rev[2432]
- CHANGED: module billing_rates, changed the message where it 
  is one suggestion to keep or create a new rate according to the
  bug #755. SVN Rev[2431]
- CHANGED: module cdrreport, changed the popup message to "Are 
  you sure you wish to delete the displayed CDR(s)?" and the 
  delete button to "Delete the displayed CDR(s)". SVN Rev[2426]
- CHANGED: module cdrreport, changed the title from "CDRReport"
  to "CDR Report". SVN Rev[2422]

* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-10
- FIXED:  Reports - billing_rates/billing_report:  Fixed bug 
  where module billing rate does not work, the problem was the 
  actions are bad and the comparison was wrong. SVN Rev[2388]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format 
  to sql script for administer process install, update and delete
  SVN Rev[2332]
- FIXED:  Reports - Billing_report: Fixed some bug report in 
  bugs.elastix.org [694][709] and field time of duration per 
  call are in format number(s) (number(h) number(m) number(s)). 
  Example: 145s (2m 25s). SVN Rev[2327]
- ADD:  addons, agenda, reports. Add folders to contain sql 
  scrips to update, install or delete. SVN Rev[2321]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED:  menu.xml to support new tag "permissions" where has 
  all permissions of group per module and new attribute "desc" 
  into tag  "group" for add a description of group. 
  SVN Rev[2294][2299]
- CHANGED:  Put rate by default in billing rates, when the modules 
  is used for first time appear Default rate as unique rate created.
  SVN Rev[2261]
- CHANGED:  paloSantoCDR, changed to order DESC in the function 
  listarCDRs. SVN Rev[2258]

* Mon Jan 17 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGED:   Reports: Billing Module: Change billing reports 
  and billing rates for better performance, creation of new 
  rates and edit the same rates without affect the reports 
  with rates older.....[#205] SVN Rev[2244]

* Thu Dec 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED: Additionals libs, move libs from additional folder 
  to each specify module by example paloSantoCDR.class.php
   SVN Rev[2150]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-21
- CHANGE: add new prereq asterisk in spec file.
- CHANGE: cdrreport module, change format to export and get data
  New function getNumCDR in PaloSantoCDR to obtain total of 
  regiters. SVN Rev[2046]
- FIXED: Graphic Report: add rawmode=yes to all graphic URLs.
  SVN Rev[2037]
- CHANGED: Graphic Report: make use of new functionality to 
  implement expansion of trunk groups. Requires 2022 
  (getTrunkGroupsDAHDI() function), 2032 (rewrite of loadTrunks()) 
  to work properly. Fixes Elastix bug #468 for Elastix 2.0.
  SVN Rev[2035]
- CHANGED: Graphic Report: rewrite the method used to query total 
  duration/callcount by trunk:
           Removes opportunities for SQL injection
           Uses a single SELECT instead of two nested SELECTs, 
             more efficient search
           Removes unnecessary use of TO_DAYS function, enabling 
             speedup by applying indexes on cdr.calldate
           Adds capability to query for multiple trunks, required
             for trunk groups
           Fixes potential bug in which statistics for DAHDI/1 
             trunk would include DAHDI/10, DAHDI/11...
  SVN Rev[2032]
- CHANGED:  remove trunk.db in setup of reports (svn) and new 
  trunk.db in setup folder of pbx. It changes is for VOIP Provider 
  module.
  install.php of pbx was changed to support new trunk.db
  install.php of reports was changed, because the support of 
  trunk.db is in install.php of pbx by VOIP Provider. SVN Rev[2026]
- CHANGED: CDR Report: rewrite of report code. This achieves 
 the following:
           Fix (potential) vuln of non-admin user deleting CDRs 
             belonging to other users
           Improve readability of code
           Making use of _tr and load_language_module() for 
             better i18n support
           Depend on newer support for integrated CSV/XLS/PDF 
             export of full report
  Requires SVN commit 2020 to work properly. SVN Rev[2021]
- CHANGED: CDR Report: improve XHTML compatibility. SVN Rev[2019]
- FIXED:   Graphic Report: fix regression due to picking 'menu' 
  variable from $_POST for module selection - Graphic. 
  SVN Rev[2004]
- CHANGED: Graphic Report: detect availability of getParameter() 
  at runtime. SVN Rev[2004]
- CHANGED: Graphic Report: remove invalid <BODY> tag from filter 
  template. SVN Rev[2004]
- CHANGED: massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2002]
- CHANGED: Billing Report: 
           stop assigning template variable "url" directly, and 
  remove nested <form> tag. SVN Rev[1985]
           add "menu" URL variable to list of variables for grid
  SVN Rev[1989]
- CHANGED: Destination Distribution: rework functionality from 
  images/pie_dist.php into main module. This integrates graphic 
  generation into its only user so images/pie_dist.php is no 
  longer needed. SVN Rev[1980]
- CHANGED: Destination Distribution: separate querying code into its
  own function, in preparation for graphic rework. SVN Rev[1979]
- CHANGED: Channel Usage: switch to use of palosantoGraphImage.lib.php 
  for graph generation. Requires commits 1964,1969 to work properly.
  SVN Rev[1970]
- CHANGED: Summary by Extension: remove reference to 
  paloSantoGraph.class.php. SVN Rev[1968]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-20
- FIXED: make module aware of url-as-array in paloSantoGrid.
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template fetch. They are 
      not required, since the template already includes a proper <form> 
      tag enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 
      in order to work properly.
  SVN Rev[1916]
- FIXED: make module aware of url-as-array in paloSantoGrid. 
     Delegate URL construction to class paloSantoGrid instead of calling 
      construirURL directly
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template. They are not 
      required, since the template already includes a proper <form> tag 
      enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 
      in order to work properly.
  SVN Rev[1914]
- FIXED: make module aware of url-as-array in paloSantoGrid. This commit 
  shows the basic transformations required on each module to escape URL 
  variables:
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags around the returned value of fetchGrid method. 
      They are not required, since the template already includes a 
      proper <form> tag enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 
      in order to work properly.
  SVN Rev[1903]

* Thu Oct 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- FIXED:   Fixed bug by update elastix-report replace rate.db it 
  remove all data in rate.db. Problem was installer.php. SVN Rev[1864]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- CHANGED: Updated the Bulgarian language elastix. SVN Rev[1857]

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- FIXED:   variable decimalTotal undefined. This variable show time in 
  seconds > 3600 in format ( h m s )[#353] SVN Rev[1845]
- CHANGED: Updated fr.lang. SVN Rev[1825]
- ADDED:   New lang file fa.lang (Persian) SVN Rev[1823]

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- FIXED: work around empty DSN by using Elastix 2 support for fetching DSN from /etc/amportal.conf. Rev[1711]
- FIXED: Do not treat an empty recordset as an error when filling data arrays for graph. Rev[1711]

* Thu Aug 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-15
- DELETED: Remove definition to connect asteriskcdrdb, it is not necessary.

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-14
- CHANGED: Change help files in Summary by Extension.
- FIXED:   Remove images/graphReport.php and fold its functionality back into index.php for module. This bring graph details under ACL control.

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- FIXED:   Summary By extension, querys has been improved, now the data is from channel y dstchannel. Rev[1640]

* Mon Jun 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- FIXED:   Graphic Report: Fold functionality for graphic reports into main index.php, delete libs/grafic*.php, and adjust template accordingly. This places graphic reports under ACL control. In addition, fix leftover reference to phone_numbers.php used by Graphic Report.

* Thu Jun 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-11
- New fr lang was added.

* Fri Mar 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- Changed order menu.

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- Defined number order menu.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-8
- Update relase module.

* Tue Jan 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- Function getParamater was removed in each module.

* Wed Dec 30 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Rename trunk in voip provider, database trunk, new rename.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-5
- Rename trunk in voip provider, database trunk.db

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Incremental released.

* Mon Oct 19 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add accion uninstall rpm.
- Fixed minor bugs in definition words languages and messages.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.
