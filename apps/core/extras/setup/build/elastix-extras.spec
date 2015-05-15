%define modname extras

Summary: Elastix Extras 
Name:    elastix-%{modname}
Version: 3.0.0
Release: 3
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
#Source0: %{modname}_2.0.4-4.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix-framework >= 3.0.0-1
Requires: yum

%description
Elastix EXTRA 

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
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
						mv $bdir/modules/$FOLDER0/$FOLDER1/$FOLFI/* $RPM_BUILD_ROOT/usr/share/elastix/apps/$FOLDER1/$FOLFI/
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

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p                   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv -f setup/xmlservices/   $RPM_BUILD_ROOT/var/www/html/
mv setup/                  $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml                $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%post

# Run installer script to fix up ACLs and add module to Elastix menus.
elastix-menumerge /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/menu.xml

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php

rm -rf /tmp/new_module

%clean
rm -rf $RPM_BUILD_ROOT

%preun
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Extras menus"
  elastix-menuremove $pathModule/setup/infomodules
fi

%files
%defattr(-, root, root)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/usr/share/elastix/apps/*

%changelog
* Fri Nov 21 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-3
- CHANGED: extras - Build/elastix-extras.spec: update specfile with latest
  SVN history. Bump release in specfile.

* Fri Sep 13 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-2
- CHANGED: extras - Build/elastix-extras.spec: update specfile with latest
  SVN history. Bump release in specfile.

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- ADDED: extras - setup/infomodules.xml/: Within this folder are placed the new
  xml files that will be in charge of creating the menus for each module.
  SVN Rev[5857]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com> 
- CHANGED: extras - modules: The modules were relocated under the new scheme
  that differentiates administrator modules and end user modules .
  SVN Rev[5856]

* Wed Aug 28 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Instant Messaging: fix references to uninitialized variables.
  SVN Rev[5811]

* Tue Aug 13 2013 Jose Briones <jbriones@palosanto.com> 
- REMOVED: Module Downloads, Help files with wrong names were deleted
  SVN Rev[5730]

* Tue Aug 13 2013 Jose Briones <jbriones@palosanto.com> 
- UPDATED: The names of the Downloads module's help files were changed.
  SVN Rev[5727]

* Tue Aug 13 2013 Jose Briones <jbriones@palosanto.com> 
- ADDED: extras modules, Static pages on Donwloads menu, were added as modules.
  SVN Rev[5724]

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

* Thu Sep 20 2012 Luis Abarca <labarca@palosanto.com> 3.0.0-1
- CHANGED: In spec file changed Prereq elastix to
  elastix-framework >= 3.0.0-1
  SVN Rev[4225]

* Fri Nov 25 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-1
- CHANGED: In spec file changed Prereq elastix to
  elastix-framework >= 2.2.0-18
  SVN Rev[3832]

* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED: The split function of these modules was replaced by 
  the explode function due to that the split function was 
  deprecated since PHP 5.3.0. SVN Rev[2650]
- FIXED: a2b menus in extras/menu.xml, deleted It is not usefull.
  SVN Rev[2499]

* Tue Apr 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- FIXED: a2b menus in extras/menu.xml, deleted It is not usefull.
  SVN Rev[2499]

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGED: the information showed ih those static files had 
  some changes according to the bug #779. SVN Rev[2406]
- CHANGED:  menu.xml: all modules; new attribute "desc" into 
  tag "group" for add a description of group. SVN Rev[2299]
- CHANGED:  menu.xml in all modules was changed to support new
  tag "permissions" where it has all permissions of group per
  module. SVN Rev[2294]

* Fri Jan 28 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- Initial version.

