%define modname pbx

Summary: Elastix Module PBX
Name:    elastix-%{modname}
Version: 3.0.0
Release: 13
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
#Source0: %{modname}_%{version}-20.tgz
Source1: conf-call-recorded.wav
Source2: conf-has-not-started.wav
Source3: conf-will-end-in.wav
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix-framework >= 3.0.0-5
Prereq: elastix-system >= 3.0.0-1
Prereq: tftp-server, vsftpd
Prereq: asterisk >= 1.8
Prereq: mysql-connector-odbc
Prereq: asterisk-odbc
Requires: festival >= 1.95
Requires: asterisk, asterisk-chan-allogsm, asterisk-chan-extra, asterisk-dahdi, asterisk-festival, asterisk-odbc, asterisk-voicemail
Requires: kamailio >= 4.1.4-1, kamailio-unixodbc, kamailio-mysql, kamailio-utils, kamailio-presence

%description
Elastix Module PBX

%prep


%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Asterisk files
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/moh
#mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/mohmp3

mkdir -p $RPM_BUILD_ROOT/etc/cron.daily

# ** /bin path ** #
mkdir -p $RPM_BUILD_ROOT/bin

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

# ** files ftp ** #
#mkdir -p $RPM_BUILD_ROOT/var/ftp/config

# ** /tftpboot path ** #
mkdir -p $RPM_BUILD_ROOT/tftpboot

# ** /asterisk path ** #
mkdir -p $RPM_BUILD_ROOT/etc/asterisk/
mkdir -p $RPM_BUILD_ROOT/etc/asterisk.elastix/

mkdir -p $RPM_BUILD_ROOT/etc/kamailio.elastix/

# ** service festival ** #
mkdir -p $RPM_BUILD_ROOT/etc/init.d/
mkdir -p $RPM_BUILD_ROOT/var/log/festival/

# ** ElastixDir ** #
mkdir -p $RPM_BUILD_ROOT/var/www/elastixdir/
mv setup/elastixdir/*      $RPM_BUILD_ROOT/var/www/elastixdir/
rmdir setup/elastixdir

# **Recordings use by Conference Module
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/sounds/
cp %{SOURCE1}           $RPM_BUILD_ROOT/var/lib/asterisk/sounds/
cp %{SOURCE2}           $RPM_BUILD_ROOT/var/lib/asterisk/sounds/
cp %{SOURCE3}           $RPM_BUILD_ROOT/var/lib/asterisk/sounds/

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p      $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mkdir -p      $RPM_BUILD_ROOT/usr/share/elastix/privileged/

# crons config
mv setup/etc/cron.daily/asterisk_cleanup      $RPM_BUILD_ROOT/etc/cron.daily/
chmod 755 $RPM_BUILD_ROOT/etc/cron.daily/*
rmdir setup/etc/cron.daily/

# ** asterisk.reload file ** #
mv setup/bin/asterisk.reload                  $RPM_BUILD_ROOT/bin/
chmod 755 $RPM_BUILD_ROOT/bin/asterisk.reload
rmdir setup/bin

# ** files asterisk for agi-bin and mohmp3 ** #
mv setup/asterisk/agi-bin/*                   $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/
chmod 755 $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/*
mv setup/asterisk/mohmp3/*                    $RPM_BUILD_ROOT/var/lib/asterisk/moh/

# Moviendo archivos festival y sip_notify_custom_elastix.conf
chmod +x setup/etc/asterisk/sip_notify_custom_elastix.conf
chmod +x setup/etc/init.d/festival
mv setup/etc/asterisk/sip_notify_custom_elastix.conf      $RPM_BUILD_ROOT/etc/asterisk/
mv setup/asterisk/astetc/*                                $RPM_BUILD_ROOT/etc/asterisk.elastix/
mv setup/kamailio/kamailioetc/*                           $RPM_BUILD_ROOT/etc/kamailio.elastix/
mv setup/etc/init.d/festival                              $RPM_BUILD_ROOT/etc/init.d/
mv setup/usr/share/elastix/privileged/*                   $RPM_BUILD_ROOT/usr/share/elastix/privileged/
mv setup/usr/share/elastix/asteriskconfig/                $RPM_BUILD_ROOT/usr/share/elastix/
rmdir setup/etc/init.d
rmdir setup/etc/asterisk
rmdir setup/usr/share/elastix/privileged
rmdir setup/asterisk/*
rmdir setup/asterisk

# Archivos tftp and ftp
mv setup/etc/xinetd.d/tftp                     $RPM_BUILD_ROOT/usr/share/elastix/
rmdir setup/etc/xinetd.d

# ** files tftpboot for endpoints configurator ** #
unzip setup/tftpboot/P0S3-08-8-00.zip  -d     $RPM_BUILD_ROOT/tftpboot/
mv setup/tftpboot/*                           $RPM_BUILD_ROOT/tftpboot/

mkdir -p                             $RPM_BUILD_ROOT/usr/share/elastix/libs/
mv setup/paloSantoIM.class.php      $RPM_BUILD_ROOT/usr/share/elastix/libs/
rmdir setup/usr/share/elastix setup/usr/share setup/usr/bin setup/usr

mv setup/     $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
#mv menu.xml   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/


%pre
# TODO: TAREA DE POST-INSTALACIÓN
#useradd -d /var/ftp -M -s /sbin/nologin ftpuser

# Try to fix mess left behind by previous packages.
if [ -e /etc/vsftpd.user_list ] ; then
    echo "   NOTICE: broken vsftpd detected, will try to fix..."
    cp /etc/vsftpd.user_list /tmp/
fi

mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
# Tareas de TFTP
chmod 777 /tftpboot/

# TODO: TAREA DE POST-INSTALACIÓN
# Tareas de VSFTPD
#chkconfig --level 2345 vsftpd on
#chmod 777 /var/ftp/config

# TODO: TAREA DE POST-INSTALACIÓN
# Reemplazo archivos de otros paquetes: tftp, vsftp
cat /usr/share/elastix/tftp   > /etc/xinetd.d/tftp


###################################################################

varwriter=0
# verifico si se incluye a sip_notify_custom_elastix.conf
if [ -f "/etc/asterisk/sip_notify_custom.conf" ]; then
    echo "/etc/asterisk/sip_notify_custom.conf exists, verifying the inclusion of sip_notify_custom_elastix.conf"
    grep "#include sip_notify_custom_elastix.conf" /etc/asterisk/sip_notify_custom.conf &> /dev/null
    if [ $? -eq 1 ]; then
	echo "including sip_notify_custom_elastix.conf..."
	echo "#include sip_notify_custom_elastix.conf" > /tmp/custom_elastix.conf
	cat /etc/asterisk/sip_notify_custom.conf >> /tmp/custom_elastix.conf
	cat /tmp/custom_elastix.conf > /etc/asterisk/sip_notify_custom.conf
	rm -rf /tmp/custom_elastix.conf
    else
	echo "sip_notify_custom_elastix.conf is already included"
    fi
else
    echo "creating file /etc/asterisk/sip_notify_custom.conf"
    touch /etc/asterisk/sip_notify_custom.conf
    echo "#include sip_notify_custom_elastix.conf" > /etc/asterisk/sip_notify_custom.conf
fi

varwriter=1
chown -R asterisk.asterisk /etc/asterisk

if [ $varwriter -eq 1  ]; then
    service asterisk status &>/dev/null
    res2=$?
    if [ $res2 -eq 0  ]; then #service is up
         service asterisk reload
    fi
fi

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
rm -f $pathModule/preversion_%{modname}.info

if [ $1 -eq 1 ]; then #install
  # The installer database
  elastix-dbprocess "install" "$pathModule/setup/db"

  # Ruta a módulos es incorrecta en 64 bits. Se corrige a partir de ruta de Asterisk.
  RUTAREAL=`grep astmoddir /etc/asterisk/asterisk.conf | sed 's|^.* \(/.\+\)$|\1|' -`
  sed --in-place "s|/usr/lib/asterisk/modules|$RUTAREAL|g" /etc/asterisk.elastix/asterisk.conf

  # Cambio carpeta de archivos de configuración de Asterisk
  mv -f /etc/asterisk.elastix/* /etc/asterisk/

  # Corregir la arquitectura en kamailio.cfg
  if [ `uname -m` != 'x86_64' ] ; then
    sed -i 's|/usr/lib64|/usr/lib|' /etc/kamailio.elastix/kamailio.cfg
  fi

  mv -f /etc/kamailio.elastix/* /etc/kamailio/
elif [ $1 -eq 2 ]; then #update
  # The installer database
   elastix-dbprocess "update" "$pathModule/setup/db" "$preversion"
fi

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php
rm -rf /tmp/new_module

# Detect need to fix up vsftpd configuration
if [ -e /tmp/vsftpd.user_list ] ; then
    echo "   NOTICE: fixing up vsftpd configuration..."
    # userlist_deny=NO
    sed --in-place "s,userlist_deny=NO,#userlist_deny=NO,g" /etc/vsftpd/vsftpd.conf
    rm -f /tmp/vsftpd.user_list
fi

# The following files must exist (even if empty) for asterisk 1.6.x to work correctly.
# This does not belong in %%install because these files are dynamically created.
touch /etc/asterisk/manager_additional.conf
touch /etc/asterisk/features_general_additional.conf
touch /etc/asterisk/dahdi-channels.conf
touch /etc/asterisk/musiconhold_custom.conf
touch /etc/asterisk/chan_dahdi_additional.conf
#touch /etc/asterisk/features_applicationmap_additional.conf
touch /etc/asterisk/features_general_custom.conf
touch /etc/asterisk/features_applicationmap_custom.conf
touch /etc/asterisk/features_map_custom.conf
touch /etc/asterisk/extensions_globals.conf
touch /etc/asterisk/sip_notify_additional.conf
touch /etc/asterisk/sip_notify_custom.conf
touch /etc/asterisk/sip_general_custom.conf
touch /etc/asterisk/sip_register.conf
touch /etc/asterisk/sip_custom.conf
touch /etc/asterisk/iax_general_custom.conf
touch /etc/asterisk/iax_register.conf
touch /etc/asterisk/iax_custom.conf
touch /etc/asterisk/meetme_custom.conf
touch /etc/asterisk/queues_custom.conf
touch /etc/asterisk/vm_general_custom.conf
#


chown -R asterisk.asterisk /etc/asterisk/*

# Fix once and for all the issue of recordings/MOH failing because
# of Access Denied errors.
if [ ! -e /var/lib/asterisk/sounds/custom/ ] ; then
    mkdir -p /var/lib/asterisk/sounds/custom/
    chown -R asterisk.asterisk /var/lib/asterisk/sounds/custom/
fi

if [ ! -e /var/lib/asterisk/sounds/tss/ ] ; then
    mkdir -p /var/lib/asterisk/sounds/tss/
    chown -R asterisk.asterisk /var/lib/asterisk/sounds/tss/
fi

# Copy any unaccounted files from moh to mohmp3
#mkdir -p /var/lib/asterisk/mohmp3/none
mkdir -p /var/lib/asterisk/moh/none
#for i in /var/lib/asterisk/moh/* ; do
#    if [ -e $i ] ; then
#        BN=`basename "$i"`
#        if [ ! -e "/var/lib/asterisk/mohmp3/$BN" ] ; then
#            cp $i /var/lib/asterisk/mohmp3/
#        fi
#    fi
#done

%clean
rm -rf $RPM_BUILD_ROOT

%preun
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm; delete
  pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
  echo "Delete System menus"
  elastix-menuremove $pathModule/setup/infomodules

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
/etc/asterisk/sip_notify_custom_elastix.conf
/etc/asterisk.elastix/*
/var/www/elastixdir/address_book_images
/var/www/elastixdir/asteriskconf/generic_extensions.conf
%config(noreplace) /var/www/elastixdir/asteriskconf/elastix_pbx.conf
/var/lib/asterisk/*
/var/lib/asterisk/agi-bin
/var/lib/asterisk/agi-bin/*
/var/log/festival
/var/lib/asterisk/sounds
/var/lib/asterisk/sounds/conf-call-recorded.wav
/var/lib/asterisk/sounds/conf-has-not-started.wav
/var/lib/asterisk/sounds/conf-will-end-in.wav
#/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/setup/extensions_override_elastix.conf
%defattr(-, root, root)
/usr/share/elastix/module_installer/*
/tftpboot/*
/usr/share/elastix/tftp
%{_localstatedir}/www/html/*
/usr/share/elastix/apps/*
%defattr(644, root, root)
/etc/kamailio.elastix/*
/usr/share/elastix/libs/*
%defattr(755, root, root)
/etc/init.d/festival
/bin/asterisk.reload
/usr/share/elastix/privileged/*
/usr/share/elastix/asteriskconfig/*
/etc/cron.daily/asterisk_cleanup

%changelog
* Thu Jul  2 2015 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Time Conditions: rewrite incorrect priority decision on dialplan build.
  Fixes Elastix bug #1921.
  SVN Rev[7105]
- FIXED: General Settings Admin: rewrite of SIP settings saving code to correct
  mistaken writing of localnetip value as localnetmask. Fixes Elastix bug #2274.
  SVN Rev[7104]

* Tue Jun 30 2015 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: PBX: use %config(noreplace) for elastix_pbx.conf. Fixes Elastix bug
  #2276.
  SVN Rev[7103]
- FIXED: PBX: expand table columns containing an organization code. Part of fix
  for Elastix bug #2110.
  SVN Rev[7102]

* Mon Dec  8 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Extensions: fix use of uninitialized variable for CID in extension
  creation.
  SVN Rev[6802]
- CHANGED: Music On Hold: organization admins are not allowed to create custom
  MoH classes. Fixes Elastix bug #2069.
  SVN Rev[6800]
- FIXED: Music On Hold: allow dot in full MoH class name, which necessarily
  includes a domain name as prefix.
  SVN Rev[6799]

* Thu Dec  4 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Other Destinations: disallow newlines as part of description OR
  extension. Part of fix for Elastix bug #2063.
  SVN Rev[6798]
- FIXED: Shortcut Apps: disallow newlines as part of app name OR extension. Part
  of fix for Elastix bug #2063.
  SVN Rev[6797]
* Wed Dec  3 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Outbound Route: disallow newlines as part of route name. Part of
  fix for Elastix bug #2063.
  SVN Rev[6796]
- FIXED: Announcement: disallow newlines as part of description text. Part of
  fix for Elastix bug #2063.
  SVN Rev[6795]
- CHANGED: Announcement: reimplement dialplan creation function.
  SVN Rev[6794]

* Tue Dec  2 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: PBX: change file and directory ownership in package to root
  instead of asterisk. Part of fix for Elastix bug #2062.
  SVN Rev[6790]

* Mon Dec  1 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: RingGroup: filter out remainders of DOS-type newlines in textareas that
  get written into the dialplan. Fixes Elastix bug #1875.
  SVN Rev[6780]

* Fri Nov 21 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-13
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.

* Mon Nov 10 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-12
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6772]

* Fri Nov  7 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Trunks: fix incorrect API usage for method
  paloSantoOrganization::getOrganization().
  SVN Rev[6769]

* Mon Oct 27 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-11
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6765]

* Mon Oct 27 2014 Luis Abarca <labarca@palosanto.com>
- ADDED: trunk - core/pbx-libs_scripts: Making the proper statements about
  creation of dialplans based on FreePBX code in the header of files.
  SVN Rev[6763]

* Thu Oct 16 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-10
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6757]

* Fri Aug 29 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: restrict allowed protocols for SIP accounts to udp.
  SVN Rev[6709]

* Thu Aug 28 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: Revert commits 6705 through 6707. This needs to be done another way.
  SVN Rev[6708]

* Wed Aug 27 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: remove use of rtpproxy, as it does not work correctly with
  WebRTC.
  SVN Rev[6705]

* Wed Aug 27 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: sip.conf: rtpproxy does not work correctly for WebRTC, so it will be
  removed. Instead of binding SIP to localhost:5080, asterisk will now deny
  access to any source other than localhost.
- CHANGED: add realtime columns required for WebRTC accounts, and fill required
  values for IM accounts, now repurposed for WebRTC.
  SVN Rev[6704]

* Tue Aug 26 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Trunks: previously, when creating or updating a SIP/IAX trunk, the
  deny/permit fields were assigned 0.0.0.0/0.0.0.0 if they were left empty. This
  behavior is incorrect and prevents setting up a trunk that inherits the global
  deny/permit setting for the technology. Fix this by setting to NULL instead.
  SVN Rev[6703]

* Tue Aug 19 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: the Asterisk NOTIFY for BLF has Contact: header set to
  someextension@127.0.0.1:5080. This breaks some phones. Fix this by substituting
  the contact value back to someextension@domain.com.
  SVN Rev[6686]

* Thu Aug 14 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: presence_xml.force_active must be 1 in order to be able to
  work with SIP client that announce presence but do not use XCAP to authorize
  notifying their presence.
  SVN Rev[6677]

* Mon Aug  4 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: some phones require the 200 OK from a BLF subscription to
  arrive before the NOTIFY for the first update of said subscription. Since
  Kamailio is multiprocess, each packet may be handled by two different
  processes, and get sent out of order. Fix this by remembering the Call-ID
  header value for the SUBSCRIBE and dropping NOTIFY packets until the reply to
  the SUBSCRIBE has been transmitted.
  SVN Rev[6671]

* Fri Aug  1 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: do not mangle To header when routing a BLF SUBSCRIBE to
  asterisk, since the mangled header is then used as an XML attribute in
  subsequent NOTIFY packets, which breaks BLF in some phones.
  SVN Rev[6670]

* Thu Jul 31 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: fix route[NATDETECT] to run fix_nated_contact() for both
  REGISTER and SUBSCRIBE when source is a websocket.
  SVN Rev[6669]

* Wed Jul 30 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: attempt to store Jitsi status icon using the URL supplied
  by Blink.
  SVN Rev[6667]

* Mon Jul 28 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: revert change of BLOB columns through the kamailio database.
  The root cause of decoding errors was in the db_unixodbc module in kamailio,
  which was locally patched in kamailio-4.1.4-1.
  SVN Rev[6664]

* Mon Jul 14 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: Disable topology hiding for now - does not work correctly
  with NOTIFY routing for BLF. Route SUBSCRIBE(Event==message-summary) to
  asterisk for correct voicemail operation. Disable MSRP authentication, as
  Kamailio authentication functions are unsuitable for it.
  SVN Rev[6663]

* Mon Jun 30 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: queues: introduce field queue_member.member_order and modify keys to
  include this field. This hack force MySQL to use member_order as a sort
  parameter for the realtime query used in queue loads. This is part of a fix
  for undefined ordering of queue members that messes up linear strategy.
  SVN Rev[6661]

* Thu Jun 26 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: WIP in MSRP routing
  SVN Rev[6660]

* Tue Jun 24 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: fix regression on contact alias for NAT.
- CHANGED: now INVITEs with SDP of media=message are routed by Kamailio instead
  of being relayed to Asterisk. This is required to properly handle MSRP.
  SVN Rev[6659]

* Mon Jun 23 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: merge processing for ordinary NAT and websocket into a
  single code block, to avoid calling both add_contact_alias() and
  set_contact_alias() on the same websocket request.
  SVN Rev[6658]

* Fri Jun 20 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: TOASTERISK routing should handle the case where auth_check
  function allowed through a credential with username USERNAME@DOMAIN and
  IP realm, and use appropriate segments for domain mangling.
  SVN Rev[6657]

* Mon Jun 16 2014 Bruno Macias <bmacias@palosanto.com>
- UPDATED: module extesions, Updated sort.
  SVN Rev[6651]

* Fri Jun 13 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-9
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6650]

* Thu Jun 12 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: use LOG_LOCAL6 for logging of messages in Kamailio
  configuration. This change, in addition to suitable logrotate/rsyslog
  configuration, is required to move log messages to a separate file.
  SVN Rev[6647]

* Fri Jun 06 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: load topoh module to work around alleged inability of some
  phones (Audiocodes) to handle 127.0.0.1 in a Via header.
  SVN Rev[6644]

* Wed Jun 04 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-8
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6641]
- FIXED: kamailio: enable SQL quoting for ODBC, which is disabled by default.
- CHANGED: kamailio: add partial support for XCAP documents (storage).
  SVN Rev[6640]

* Fri May 30 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: switch all BLOB columns that (commonly?) store text to
  a corresponding TEXT type. This fixes issues with Kamailio receiving hexdumps
  through ODBC instead of character data when fetching XCAP.
  SVN Rev[6637]

* Tue May 27 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: separate selection of required rtpproxy from actual
  application of selection. This allows consolidation of the same choosing logic
  for both rtpproxy and gateway detection. Comment out unused forwarding block.
  Do not handle SUBSCRIBE "Dialog" events, and instead route them to Asterisk,
  in order to fix phone BLF support.
  SVN Rev[6636]

* Fri May 16 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: kamailio: for the IP authorization scenario, do not just overwrite the
  From: user for trunk identification. Instead, save it to a temporary variable
  that is used in a later uac_replace_from call. This allows restoring the From:
  user in the response, fixing interaction with stricter SIP peers that reject
  responses with From: users leaked from the database.
- FIXED: kamailio: check on database whether routed request user is actually
  a global trunk. This fixes the scenario where the From: user and authorization
  user match for a trunk, which incorrectly triggered the domain mangling logic.
  SVN Rev[6627]

* Fri May 02 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: kamailio: add P-Asserted-Identity header to INVITE for the scenario of
  incoming calls with IP authentication. This restores the Caller-ID that was
  overwritten in the From header in an attempt to force asterisk to recognize
  the incoming trunk without an authentication username/password. In order for
  this to work, the trunk MUST be configured with trustrpid=yes in the asterisk
  side.
  SVN Rev[6620]

* Fri May 02 2014 Bruno Macias <bmacias@palosanto.com>
- UPDATED: languages modules were updated.
  SVN Rev[6619]

* Wed Apr 30 2014 Bruno Macias <bmacias@palosanto.com>
- FIXED: app pbx, Option zero on module ivr now is accepted.
  SVN Rev[6614]

* Wed Apr 30 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: kamailio: specify default registration timeout value to match asterisk
  default. This fixes issue with Aastra phones not specifying a registration
  timeout, and then asterisk revokes registration while kamailio keeps it
  active.
  SVN Rev[6613]

* Tue Apr 29 2014 Luis Abarca <labarca@palosanto.com>
- ADDED: pbx - Build/elastix-pbx.spec: Some requirements were added in order to
  correct some overwritten files.
  SVN Rev[6612]

* Mon Apr 28 2014 Bruno Macias <bmacias@palosanto.com>
- FIXED: apps pbx, privileged/asteriskconfig created main dir not exists on
  /var/spool/asterisk.
  SVN Rev[6611]

* Mon Apr 28 2014 Bruno Macias <bmacias@palosanto.com>
- FIXED: apps pbx, kamailio database now is created/
  SVN Rev[6610]

* Mon Apr 28 2014 Bruno Macias <bmacias@palosanto.com>
- FIXED: module reports, database asteriskcdrdv wasn't creating. SQLs files
  names were changed in folder db/install/asteriskcdrdb
  SVN Rev[6609]

* Fri Apr 25 2014 Bruno Macias <bmacias@palosanto.com>
- UPDATED: framework, paloSantoPBX.class, updated SQL.
  SVN Rev[6606]

* Thu Apr 24 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: kamailio: restore scrubbing of credentials on INVITEs. Fixes failure to
  recognize SIP peer (regression).
  SVN Rev[6605]

* Wed Apr 23 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-7
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6600]

* Wed Apr 23 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: elastix-pbx: fix RPM requires specification for kamailio-unixodbc
  SVN Rev[6598]

* Wed Apr 23 2014 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: elastix-pbx: fix RPM file specification for kamailio
  SVN Rev[6597]

* Tue Apr 22 2014 Bruno Macias <bmacias@palosanto.com>
- FIXED: module trunk, table trunk_organization now have a new column, SQL
  expression was changed.
  SVN Rev[6596]

* Tue Apr 22 2014 Bruno Macias <bmacias@palosanto.com>
- CHANGED: module ANI, column ani_prefix now is varchar.
  SVN Rev[6595]

* Tue Apr 22 2014 Bruno Macias <bmacias@palosanto.com>
- UPDATED: module ani, updated language en and es.
  SVN Rev[6594]

* Tue Apr 22 2014 Bruno Macias <bmacias@palosanto.com>
  NEW: module ANI, new module ANI.
  SVN Rev[6593]

* Thu Apr 17 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: trunks: update kamailio.address table as needed for trunks
  SVN Rev[6592]
- CHANGED: kamailio: enable IP authentication. Fix user authentication so that
  INVITEs actually check the authentication, instead of just checking that an
  user is specified. Attempt to load an authentication user if request has been
  authenticated through IP and no authentication user is specifed, so that
  Asterisk can identify the trunk.
  SVN Rev[6590]
- CHANGED: asterisk: disable asterisk HTTP support since kamailio now handles wss
  SVN Rev[6585]
- CHANGED: kamailio: update SQL to change default websocket WSS port for Kamailio
  SVN Rev[6584]
- CHANGED: kamailio: add SSL support for SIP and websockets in Kamailio
  configuration files.
  SVN Rev[6583]

* Tue Apr 15 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: change keepalive mechanism to work around disconnection
  timeout under Firefox.
  SVN Rev[6582]

* Wed Apr 09 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: add preliminary websocket support, non-SSL.
  SVN Rev[6581]

* Mon Mar 31 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: fix architecture directory to ensure kamailio modules are
  loaded properly.
  SVN Rev[6565]

* Wed Mar 26 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: guard against missing authuser by requiring all external
  SIP requests to be authenticated, not just REGISTER. This prevents an INVITE
  from an unregistered client from being routed without authentication.
  SVN Rev[6560]

* Thu Mar 20 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Trunks: delegate transformation of form fields into SQL fields to the
  relevant voip objects (paloSIP and paloIAX) instead of reimplementing the
  transformation. This makes use of the _getFieldValuesSQL() method made public
  by a previous commit. The purpose of this delegation is to reuse the password
  mapping (secret->sippasswd, name->kamailioname) for Kamailio. Map SIP property
 'sippasswd' into 'secret' on property read, in order for it to be preserved
  across updates. Some minimal code cleanup.
  SVN Rev[6554]

* Wed Mar 19 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: incoming SIP requests for which the From username differs
  from the authentication username are assumed to be requests from SIP trunks
  which are exempt from domain mangling.
  SVN Rev[6549]

* Mon Mar 17 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: expand elxpbx.subscriber view to allow authentication for
  incoming SIP trunks. Create a table for global domains/IP addresses.
  SVN Rev[6549]
- CHANGED: kamailio: relax check that enforced From: username to match
  authentication username, in order to allow incoming SIP trunks to work.
  SVN Rev[6548]
- CHANGED: kamailio: relax checks that prevent rtpproxy from being negotiated
  when asterisk routes a SIP call through Kamailio for an outgoing SIP trunk.
  SVN Rev[6543]

* Sat Mar 15 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: define elxpbx.subscriber view for kamailio authentication.
  SVN Rev[6541]

* Thu Mar 13 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: kamailio: improve kamailio mangling of domain accounts. Instead of
  performing the mangling manually, we can rely on uac module to do it for us.
  Create required database view for mapping.
  SVN Rev[6535]

* Wed Mar 12 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: asteriskconfig: add support for pluggable dialplan generators. With
  this support, an Elastix 3 addon may insert itself into the dialplan generation
  process without asteriskconfig having to know about this addon.
  SVN Rev[6533]

* Tue Mar 11 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: add schema and grant for Kamailio database. Add Requires. Add
  Kamailio configuration file.
  SVN Rev[6530]
- CHANGED: disable queue logging to /var/log/asterisk/queue_log, and enable it
  on asteriskcdrdb.queue_log table.
  SVN Rev[6522]

* Mon Mar 10 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: add sip parameters to make asterisk contact Kamailio for outgoing SIP
  trunks. Add new columns sippasswd and kamailioname to elxpbx.sip table.
- CHANGED: asteriskconfig: reload kamailio on dialplan reload.
  SVN Rev[6518]

* Sat Mar 08 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: asteriskconfig: collapse a series of method calls on various dialplan
  classes into a loop that exploits the commonalities. Also include a call to
  load_default_timezone().
  SVN Rev[6506]

* Fri Mar 07 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: sip.conf: restrict Asterisk to listen on localhost only for SIP.
  SVN Rev[6504]
- CHANGED: Start of Kamailio integration into Elastix. Create column
  sip.sippasswd on database elxpbx. All instances of MD5 hashing in elastix-pbx
  are reverted to plaintext passwords written to sippasswd.
  SVN Rev[6503]

* Thu Mar 06 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: asteriskconfig: fixed typo in privileged script.
  SVN Rev[6502]

* Wed Mar 05 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- ADDED: asterisk.elastix: added cdr.conf for the sole purpose of disabling
  flat-file CDR accounting. Part of fix for Elastix bug #1871.
  SVN Rev[6499]
- CHANGED: asterisk.elastix: modified configuration files as follows:
  res_odbc.conf: all passwords removed, now relies on passwords in
  /etc/odbc.ini, and added new section asteriskcdrdb.
  cdr_mysql.conf: removed.
  cdr_adaptive_odbc.conf: added. References asteriskcdrdb from res_odbc.conf.
  This relies on cdr_mysql being not-present or disabled (see Elastix bug #1871,
  and #1886).
  Part of changes required for addressing Elastix bug #1872.
  SVN Rev[6498]

- CHANGED: MOH: switch the MOH directory from mohmp3 to moh (the Asterisk
  default). Update all code accordingly.
  SVN Rev[6494]

* Mon Feb 10 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Extension Batch: re-enable account password strength check which was
  disabled without explanation on last rewrite
  SVN Rev[6469]

* Thu Jan 23 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Festival: introduce check for systemd-style status report in order to
  detect whether festival is running.
  SVN Rev[6415]

* Wed Jan 22 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Festival: check alternate route for festival.scm in Fedora in addition
  to the one checked in CentOS.
  SVN Rev[6400]

* Sat Jan 18 2014 Luis Abarca <labarca@palosanto.com> 3.0.0-6
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[6391]

* Thu Jan 09 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Fix cronjob that removes temporary TTS files by checking whether tts
  directory exists. Also simplify by using the native -delete action of find
  instead of piping to xargs and rm.
  SVN Rev[6353]

* Fri Jan 03 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Festival: update jquery.ibutton.js to 1.0.03, fix potential
  incompatibilities with jQuery 1.9+
  SVN Rev[6329]

* Wed Dec 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Frameworks/Apps: translations Settings options (spanish)
  SVN Rev[6304]

* Fri Dec 13 2013 Rocio Mera <rmera@palosanto.com>
CHANGED : TRUNK - APPS/Pbx: The error manager was moved to general folder.
  SVN Rev[6282]

* Thu Dec 12 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - APPS/PBX: Was made change in elxpbx sql schema in table im.
  Was added constraint 'FOREIGN KEY (device) REFERENCES sip(name) ON DELETE
  CASCADE'
  SVN Rev[6276]

* Tue Dec 10 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in alert message reload (spanish)
  SVN Rev[6273]

* Tue Dec 10 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in alert message reload (spanish)
  SVN Rev[6272]

* Tue Dec 10 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in alert message reload (spanish)
  SVN Rev[6271]

* Tue Dec 10 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Time conditions-> error message
  (spanish)
  SVN Rev[6270]

* Tue Dec 10 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in PBX configuration-> Device help
  (spanish)
  SVN Rev[6269]

* Tue Dec 10 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in PBX configuration-> Device
  (spanish)
  SVN Rev[6268]

* Wed Dec 04 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in General Settings Admin menu option
  (spanish)
  SVN Rev[6256]

* Wed Dec 04 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Frameworks/Apps: translation in Ring Group filters (spanish)
  SVN Rev[6255]

* Wed Dec 04 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Frameworks/Apps: translation in trunks filters (spanish)
  SVN Rev[6254]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Monitoring filters (spanish)
  SVN Rev[6242]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Conference filters (spanish)
  SVN Rev[6241]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Recordings (spanish)
  SVN Rev[6239]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Music On Hold (spanish)
  SVN Rev[6237]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in General Settings (spanish)
  SVN Rev[6236]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in General Settings Admin yes/no
  (spanish)
  SVN Rev[6234]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Ring Group options (spanish)
  SVN Rev[6233]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Queues options (spanish)
  SVN Rev[6232]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Extensions options (spanish)
  SVN Rev[6231]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in IVR options (spanish)
  SVN Rev[6230]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Inbound Route options (spanish)
  SVN Rev[6229]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Outbound Route options (spanish)
  SVN Rev[6228]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in DID options (spanish)
  SVN Rev[6227]

* Tue Dec 03 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Trunks (spanish)
  SVN Rev[6226]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in General Settings->sip settings
  (spanish)
  SVN Rev[6225]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in General Settings (spanish)
  SVN Rev[6224]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in General Settings Admin options
  (spanish)
  SVN Rev[6223]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Time Conditions options (spanish)
  SVN Rev[6222]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Ring Group select options (spanish)
  SVN Rev[6221]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Queues select opctions (spanish)
  SVN Rev[6220]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Queues (spanish)
  SVN Rev[6218]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Extensions options (spanish)
  SVN Rev[6217]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Inbound Route Title (spanish)
  SVN Rev[6216]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Inbound Route module (spanish)
  SVN Rev[6215]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation filters search Outbound Route
  (spanish)
  SVN Rev[6214]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation filters search did (spanish)
  SVN Rev[6213]

* Mon Dec 02 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation filters for trunks in column (spanish)
  SVN Rev[6212]

* Fri Nov 29 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation General Configurations Adm: Voicemail
  Settings (spanish)
  SVN Rev[6211]

* Fri Nov 29 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation General Configurations Adm: IAX
  Settings (spanish)
  SVN Rev[6210]

* Fri Nov 29 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation General Configurations Adm: General
  Settings and SIP Settings (spanish)
  SVN Rev[6209]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: translation in Features Code Title (spanish)
  SVN Rev[6208]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in File Editor label (spanish)
  SVN Rev[6201]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Monitoring column name (spanish)
  SVN Rev[6200]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - APPS/PBX: Was made changes in sql file of elxpbx database to
  fix some bugs
  SVN Rev[6199]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - APPS/PBX: Was made changes in sql file of elxpbx database to
  fix some bugs
  SVN Rev[6198]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Conference Labels (spanish)
  SVN Rev[6197]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Recording Labels (spanish)
  SVN Rev[6195]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Music On Hold Labels (spanish)
  SVN Rev[6194]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Extensions label - title (spanish)
  SVN Rev[6192]

* Thu Nov 28 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Outbound Route labels(spanish)
  SVN Rev[6191]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in General Settings Admin labels
  filters (spanish)
  SVN Rev[6190]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - APPS/PBX: Was made change in schema elxpbx to set defautl
  value in field host = 'dynamic' in tables sip_settings, iax_settings , sip,
  iax
  SVN Rev[6189]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Queues labels filters (spanish)
  SVN Rev[6187]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - APPS/PBX: Was changed file extensions_general to add context
  im-sip
  SVN Rev[6186]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Extensions labels filters (spanish)
  SVN Rev[6183]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in IVR labels filters (spanish)
  SVN Rev[6182]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Inbound Route labels filters
  (spanish)
  SVN Rev[6181]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: translation in Outbound Route labels filters
  (spanish)
  SVN Rev[6180]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/Email: translation in Did labels filters (spanish)
  SVN Rev[6179]

* Wed Nov 27 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/Email: translation in Trunks labels filters (spanish)
  SVN Rev[6178]

* Tue Nov 26 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in Time Conditions
  labels(spanish)
  SVN Rev[6163]

* Tue Nov 26 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in Ring labels(spanish)
  SVN Rev[6162]

* Tue Nov 26 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in extensions
  labels(spanish)
  SVN Rev[6161]

* Mon Nov 25 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in Text to wav
  labels(spanish)
  SVN Rev[6156]

* Mon Nov 25 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in General Settings
  labels(spanish)
  SVN Rev[6155]

* Mon Nov 25 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in features code
  labels(spanish)
  SVN Rev[6153]

* Mon Nov 25 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in IVR menu (spanish)
  SVN Rev[6152]

* Mon Nov 25 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in trunks (spanish)
  SVN Rev[6151]

* Mon Nov 25 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language translation add in DID (spanish)
  SVN Rev[6150]

* Fri Nov 22 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language help add in did (english - spanish)
  SVN Rev[6147]

* Thu Nov 21 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language help add in festival (english - spanish)
  SVN Rev[6141]

* Thu Nov 21 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language help add in text to wav (english -
  spanish)
  SVN Rev[6140]

* Thu Nov 21 2013 Rocio Mera <rmera@palosanto.com>
- MOVED: TRUNK - Apps/PBX: Was moved file elxpbx/update/3_3.0.0-3_3.0.0-4.sql
  from wrong path to elxpbx/update/version_sql/3_3.0.0-3_3.0.0-4.sql
  SVN Rev[6137]

* Wed Nov 20 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - PBX/Apps: language help add in general setting (english -
  spanish)
  SVN Rev[6136]

* Tue Nov 19 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in Conference (english -
  spanish)
  SVN Rev[6130]

* Tue Nov 19 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in Music On Hold (english -
  spanish)
  SVN Rev[6129]

* Tue Nov 19 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in Features Code (english -
  spanish)
  SVN Rev[6128]

* Tue Nov 19 2013 Luis Abarca <labarca@palosanto.com>
- FIXED: build - *.spec: An error in the logic of the code was unintentionally
  placed when saving the elastix's spec files.
  SVN Rev[6125]

* Mon Nov 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in Recordings (english -
  spanish)
  SVN Rev[6123]

* Mon Nov 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in Time Conditions (english -
  spanish)
  SVN Rev[6122]

* Mon Nov 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in Ring Groups (english -
  spanish)
  SVN Rev[6121]

* Mon Nov 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in queues (english - spanish)
  SVN Rev[6120]

* Mon Nov 18 2013 Luis Abarca <labarca@palosanto.com>
- FIXED: build - *.spec: An extra character was unintentionally placed when
  saving the elastix's spec files.
  SVN Rev[6116]

* Mon Nov 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in extension (english - spanish)
  SVN Rev[6115]

* Mon Nov 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add IVR (english - spanish)
  SVN Rev[6113]

* Fri Nov 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in trunks (spanish)
  SVN Rev[6108]

* Fri Nov 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in inbound_route (spanish -
  english)
  SVN Rev[6107]

* Fri Nov 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in outbound_route (spanish -
  english)
  SVN Rev[6106]

* Fri Nov 15 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: build - *.spec: Update specfiles with the new form of use
  elastix-menumerge for each elastix module.
  SVN Rev[6104]

* Fri Nov 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in did (spanish - english)
  SVN Rev[6103]

* Fri Nov 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: language help add in trunks (spanish - english)
  SVN Rev[6102]

* Thu Nov 14 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK -APPS/PBX: Was made change in module extensions to incorporate
  changes that was needed to by the creation of a extra peer to chat for users
  SVN Rev[6096]

* Thu Nov 14 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: Was added in elxpbx schemas tables http_ast and
  elx_chat_config that are used in elastix_chat. Also was fixed wrong sql
  sentence
  SVN Rev[6091]

* Mon Nov 11 2013 Luis Abarca <labarca@palosanto.com>
- FIXED: pbx - Build/elastix-pbx.spec: The menu.xml file and the
  extensions_override_elastix.conf file are no used anymore in Elastix MTE.
  SVN Rev[6078]

* Tue Nov 05 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: Was modified module my_extension in order to
  manage errors in the fields forms, to display error in field form is used
  tooltip bootstrap plugin.
  SVN Rev[6063]

* Mon Nov 04 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Trunk -APPS/PBX: Was made changes in module extensions to delete
  option that permited edit secret in extensions that belong to a user. Also
  added new parameters to config.
- ADDED: Trunk - APPS/PBX: Was added file http.conf to /etc/aterisk. This file
  is used to add support to web socket
  SVN Rev[6059]

* Thu Oct 31 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Apps/PBX: Were modified variables of error message to global
  variables for to use in others modules.
  SVN Rev[6046]

* Thu Oct 24 2013 Rocio Mera <rmera@palosanto.com>
  Delete: TRUNK - Apps/PBX: Was removed libraries bootstrap because it belong
  now to elastix framework
  SVN Rev[6036]

* Wed Oct 23 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- DELETED: The code for the experimental PHP-based parallel endpoint configurator
  has been removed. This functionality is now provided by the New Endpoint
  Configurator.
  SVN Rev[6030]

* Wed Oct 16 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: Inbound Route: fix typo
  SVN Rev[6017]

* Tue Oct 15 2013 Rocio Mera <rmera@palosanto.com>
- DELETE: TRUNK - Apps/PBX: Module my_extension, Was deleted jquery-ui.css file
  because already exist inside common files
  SVN Rev[6012]

* Tue Oct 15 2013 Rocio Mera <rmera@palosanto.com>
- ADDED: TRUNK - Apps/PBX: Was added module my_extension to /pbx/apps/frontend.
  this module display user extension configuration and allow set voicemail,
  call forward and don't disturb parameters
  SVN Rev[6011]

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

* Mon Sep 30 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Trunk - Apps/PBX: Was fixed file infomodules pbxconfig. Bad order_no
  field
  SVN Rev[5962]

* Mon Sep 30 2013 Rocio Mera <rmera@palosanto.com>
- DELETED: Tunk - Apps/PBX: Was deleted file menu.xml. This file was divided in
  a set of files that are stored in setup/infomodules
- ADDED: Tunk - Apps/PBX: Was added directory infomodules. This directory store
  a xml files that are used to create elastix resources
  SVN Rev[5959]

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
- CHANGED: TRUNK - APPS: Was made change in module general_settings to delete
  parameter useragent
  SVN Rev[5942]

* Tue Sep 24 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Trunk - Apps: Was made changes im module trunk, extension,
  time_group in order to solve some minors bugs
- CHANGED: Trunk - APPS: Was made change in lib paloSantoCDR in order to
  resolve bug related with filter param
  SVN Rev[5941]

* Mon Sep 23 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Trunk - Apps: Was made changes in db.info in apps pbx, email_admin
  and fax. Was setting to elxpbx databases the param ingore_backup=yes in order
  to the elastix-dbprocess does not made a backup of this database and delete
  the database elxpbx. The framework create elxpbx database
  SVN Rev[5926]

* Fri Sep 20 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: TRUNK - Framework: Was fixed duplicate entry in tables sip and
  sip_settings
  SVN Rev[5925]

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

* Thu Sep 19 2013 Luis Abarca <labarca@palosanto.com>
- REMOVED: pbx - scripts,users_images: These folders must be provided by the
  email_admin module.
  SVN Rev[5903]

* Thu Sep 19 2013 Luis Abarca <labarca@palosanto.com>
- REMOVED: pbx - setup/elastixdir/faxdocs: This folder must be provided by the
  fax module.
  SVN Rev[5902]

* Tue Sep 17 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Trunk - APPS/PBX: Was made changes in modules  monitoring,
  asterisk_cli, file_editor, text_to_wav  to adapt this module to the new
  permissions schemas and to the directory restructuration
- CHANGED: Trunk - Apps/PPBX: Was made changes in module queues conference to
  solve som bugs related to call recordings
- CHANGED: Trunk - APPS/PBX: Was made changes in privileged script
  asteriskcpnfig and festival . File festival.smc change its location from
  /usr/share/festival to /usr/share/festival/lib. In script asteriskconfig was
  solve a bug that existed in dialplan generation
  SVN Rev[5893]

* Thu Sep 12 2013 Rocio Mera <rmera@palosanto.com>
- ADDED: TRUNK - APPS/PBX: Was added to pbx/modules/backend module that was
  delted in commit 5877. This module has been adapted to work with the new
  group permission schema. In addition some bugs have been fixed
  SVN Rev[5878]

* Thu Sep 12 2013 Rocio Mera <rmera@palosanto.com>
- DELETED: TRUNK - APPS/PBX: Was temporaly removed some modules from pbx to be
  replaced with a new implementation
  SVN Rev[5877]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com>
- ADDED: pbx - setup/infomodules.xml/: Within this folder are placed the new
  xml files that will be in charge of creating the menus for each module.
  SVN Rev[5865]

* Wed Sep 11 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: pbx - modules: The modules were relocated under the new scheme that
  differentiates administrator modules and end user modules .
  SVN Rev[5864]

* Fri Aug 23 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Trunk - Apps/PBX: Was made changes in modules features_code and
  general_settings to quit restriction in function that set the default general
  settings and features code
  SVN Rev[5803]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/festival: code upgrade
  SVN Rev[5753]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/text_to_wav: code upgrade
  SVN Rev[5752]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/file_editor: code upgrade
  SVN Rev[5751]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/asterisk_cli: code upgrade
  SVN Rev[5750]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/extensions_batch: code upgrade
  SVN Rev[5749]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/recordings: code upgrade
  SVN Rev[5748]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/musiconhold: code upgrade
  SVN Rev[5747]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/time_conditions: code upgrade
  SVN Rev[5746]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/ring_group: code upgrade
  SVN Rev[5745]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/time_group: code upgrade
  SVN Rev[5744]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/general_settings: code upgrade
  SVN Rev[5743]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/features_code: code upgrade
  SVN Rev[5742]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/queues: code upgrade
  SVN Rev[5741]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/ivr: code upgrade
  SVN Rev[5740]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/inbound_route: code upgrade
  SVN Rev[5739]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/outbound_route: code upgrade
  SVN Rev[5738]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/extensions: code upgrade
  SVN Rev[5737]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/general_settings_admin: code upgrade
  SVN Rev[5735]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/did: code upgrade
  SVN Rev[5734]

* Tue Aug 13 2013 Daniel Paez <dpaez@palosanto.com>
- CHANGED: APPS - core/pbx/modules/trunks: code upgrade
  SVN Rev[5732]

* Thu Aug  1 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Conference: remove bogus reference to PST/PDT timezone in conference
  start time. Fixes Elastix bug #1650.
  SVN Rev[5479]

* Mon Jul 22 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Extensions Batch: fix invalid SQL query that gets executed when an
  extension requires a direct DID.
  SVN Rev[5387]

* Wed Jul 17 2013 Luis Abarca <labarca@palosanto.com>
- ADDED: pbx - JS: Restored JS files missing in this module.
  SVN Rev[5339]

* Wed Jul 17 2013 Luis Abarca <labarca@palosanto.com>
- ADDED: pbx - CSS: Restored CSS files missing in this module.
  SVN Rev[5338]

* Wed Jul 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: phpagi-asmanager.php: revert SVN commit 4252. This is not the proper
  place to run load_default_timezone(). The proper place is the script including
  this library.
  SVN Rev[5332]
- DELETED: Asterisk CLI: remove unused folder i18n
  SVN Rev[5319]

* Fri Jul 05 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- FIXED: General Settings: fix syntax errors.
  SVN Rev[5303]

* Fri Jul 05 2013 Rocio Mera <rmera@palosanto.com>
- CHANGES: APPS - PBX: Was made changes in modules did features_code
  general_settings to add elastix restructuration directory. Also was made
  changes in privaliged script asteriskconfig
  SVN Rev[5299]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - voicemail/: It was corrected a configuration in the web
  folder.
  SVN Rev[5265]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - trunks/: It was corrected a configuration in the web folder.
  SVN Rev[5264]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - time_group/: It was corrected a configuration in the web
  folder.
  SVN Rev[5263]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - time_conditions/: It was corrected a configuration in the
  web folder.
  SVN Rev[5262]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - text_to_wav/: It was corrected a configuration in the web
  folder.
  SVN Rev[5261]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - ring_group/: It was corrected a configuration in the web
  folder.
  SVN Rev[5260]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - recordings/: It was corrected a configuration in the web
  folder.
  SVN Rev[5259]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - queues/: It was corrected a configuration in the web folder.
  SVN Rev[5258]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - outbound_route/: It was corrected a configuration in the web
  folder.
  SVN Rev[5257]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - musiconhold/: It was corrected a configuration in the web
  folder.
  SVN Rev[5256]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - monitoring/: It was corrected a configuration in the web
  folder.
  SVN Rev[5255]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - ivr/: It was corrected a configuration in the web folder.
  SVN Rev[5254]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - inbound_route/: It was corrected a configuration in the web
  folder.
  SVN Rev[5253]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - general_settings_admin/: It was corrected a configuration in
  the web folder.
  SVN Rev[5252]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - general_settings/: It was corrected a configuration in the
  web folder.
  SVN Rev[5251]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - file_editor/: It was corrected a configuration in the web
  folder.
  SVN Rev[5250]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - festival/: It was corrected a configuration in the web
  folder.
  SVN Rev[5249]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - features_code/: It was corrected a configuration in the web
  folder.
  SVN Rev[5248]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - extensions_batch/: It was corrected a configuration in the
  web folder.
  SVN Rev[5247]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - extensions/: It was corrected a configuration in the web
  folder.
  SVN Rev[5246]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - endpoints_batch/: It was corrected a configuration in the
  web folder.
  SVN Rev[5245]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - endpoint_configurator/: It was corrected a configuration in
  the web folder.
  SVN Rev[5244]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - did/: It was corrected a configuration in the web folder.
  SVN Rev[5243]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - control_panel/: It was corrected a configuration in the web
  folder.
  SVN Rev[5242]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - conference/: It was corrected a configuration in the web
  folder.
  SVN Rev[5241]

* Thu Jul 04 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - asterisk_cli/: It was corrected a configuration in the web
  folder.
  SVN Rev[5240]

* Thu Jul 04 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Endpoint Configurator: rename SQL file for update which was incorrectly
  named. This will prevent the update from being applied twice when updating
  from 2.4.0-1 to versions later than 2.4.0-7. Partially fixes Elastix bug #1618.
  SVN Rev[5213]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - voicemail/: The svn repository for module voicemail in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5178]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - trunks/: The svn repository for module trunks in trunk (Elx
  3) was restructured in order to accomplish a new schema.
  SVN Rev[5177]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - time_group/: The svn repository for module time_group in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5176]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - time_conditions/: The svn repository for module
  time_conditions in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5175]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - text_to_wav/: The svn repository for module text_to_wav in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5174]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - ring_group/: The svn repository for module ring_group in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5173]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - recordings/: The svn repository for module recordings in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5172]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - queues/: The svn repository for module queues in trunk (Elx
  3) was restructured in order to accomplish a new schema.
  SVN Rev[5171]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - outbound_route/: The svn repository for module
  outbound_route in trunk (Elx 3) was restructured in order to accomplish a new
  schema.
  SVN Rev[5170]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - musiconhold/: The svn repository for module musiconhold in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5169]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - monitoring/: The svn repository for module monitoring in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5168]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - ivr/: The svn repository for module ivr in trunk (Elx 3) was
  restructured in order to accomplish a new schema.
  SVN Rev[5167]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - inbound_route/: The svn repository for module inbound_route
  in trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5166]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - general_settings_admin/: The svn repository for module
  general_settings_admin in trunk (Elx 3) was restructured in order to
  accomplish a new schema.
  SVN Rev[5165]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - general_settings/: The svn repository for module
  general_settings in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5164]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - file_editor/: The svn repository for module file_editor in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5163]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - festival/: The svn repository for module festival in trunk
  (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5162]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - features_code/: The svn repository for module features_code
  in trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5161]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - extensions_batch/: The svn repository for module
  extensions_batch in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5160]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - extensions/: The svn repository for module extensions in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5159]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - endpoints_batch/: The svn repository for module
  endpoints_batch in trunk (Elx 3) was restructured in order to accomplish a
  new schema.
  SVN Rev[5158]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - endpoint_configurator/: The svn repository for module
  endpoint_configurator in trunk (Elx 3) was restructured in order to
  accomplish a new schema.
  SVN Rev[5157]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - did/: The svn repository for module did in trunk (Elx 3) was
  restructured in order to accomplish a new schema.
  SVN Rev[5156]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - control_panel/: The svn repository for module control_panel
  in trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5155]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - conference/: The svn repository for module conference in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5154]

* Tue Jul 02 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: trunk - asterisk_cli/: The svn repository for module asterisk_cli in
  trunk (Elx 3) was restructured in order to accomplish a new schema.
  SVN Rev[5153]

* Mon Jun 24 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Control Panel, Recordings: specify Context for AMI Originate instead of
  a blank field. Fixes Elastix bug #1605.
  SVN Rev[5121]

* Fri Jun 21 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: General Settings: fix trivial syntax error.
  SVN Rev[5115]

* Thu Jun 20 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Trunk - Apps: Was made chanes in elxpbx databse schema. This
  database contain data from pbx and now is integrated with framework database.
- CHANGED: Trunk - Apps: Was made change in privileged script asteriskconfig,
  email_account and faxconfig. This changes was made to incorpora the elastix
  framework database changes from sqlite to mysql
- CHANGED: Trunk - Apss: Was made change in some module of pbx. This changes
  are part of  elastix framework database changes from sqlite to mysql
  SVN Rev[5114]

* Tue Jun 18 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Voicemail, Monitoring: stop using popup.tpl. The blackmin theme lacks
  this template, and these two are the only two modules to use it. Additionally
  the template triggers javascript errors due to missing jquery. It is easier to
  just embed the html header and footer.
  SVN Rev[5108]
- CHANGED: Endpoint Configurator: prevent potential use of unset array element
  in session at construction of PaloSantoFileEndPoint.
  SVN Rev[5107]

* Thu Jun 13 2013 Alberto Santos <asantos@palosanto.com>
- FIXED: script hangup.agi. Fixed a missplaced code at line 89. Also changed
  the method to call the scripts.
  SVN Rev[5095]

* Wed Jun 12 2013 Jose Briones <jbriones@palosanto.com>
- ADDED: Added new file of sql commands for the new phone Grandstream GXP2200
  in endpoint database.
  SVN Rev[5090]

* Wed Jun 12 2013 Jose Briones <jbriones@palosanto.com>
- UPDATE: Module endpoint_configurator, Support for GXP2200 was added, and
  support for GXV3140 was improved.
  SVN Rev[5088]

* Thu Jun 06 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: File Editor, Monitoring, Voicemail: remove duplicate definition of
  getParameter() that gets confused by Fortify as the one used by the framework.
  SVN Rev[5057]

* Tue Jun 04 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Endpoint Configurator: search of extension given IP can falsely match
  another IP of which the target IP is a prefix. Fixed. Fixes part of Elastix
  bug #1570.
  SVN Rev[5052]

* Mon May 27 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-5
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Bump Release in specfile.
  SVN Rev[5029]

* Thu May 23 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- DELETED: pbxadmin: remove entire module folder. This module is a wrapper for
  freePBX which was removed in Elastix 3.
  SVN Rev[5003]

* Wed May 22 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Endpoint Configurator: add missing check for IPv4 address format.
  Pointed out by Fortify report.
  SVN Rev[4999]
- FIXED: Batch of Endpoints: remove unnecessary and risky copy of uploaded file.
  Pointed out by Fortify report.
  SVN Rev[4998]

* Tue May 21 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Asterisk CLI: rewrite to use escapeshellarg properly instead of
  reimplementing special character filtering. Remove bogus unused library.
  SVN Rev[4991]

* Mon May 20 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Recordings: fix a number of command injection vulnerabilities. Replace
  calls to exec with corresponding internal functions for mkdir(). Clean up
  code indentation. Pointed out by Fortify report.
  SVN Rev[4977]

* Fri May 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: asteriskconfig: remove some unnecessary exec() calls for chmod. Fix
  a potential arbitrary file deletion vulnerability through organization change
  code.
  SVN Rev[4965]

* Thu May 16 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Extensions Batch: rewrite the entire module to get rid of multiple
  opportunities for SQL injection and code execution. Tighten up and centralize
  validations on CSV fields. Improve readability and make the code smaller.
  SVN Rev[4954]

* Mon May 13 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Control Panel: validate several parameters before building AMI requests
  with them. More of the same.
  SVN Rev[4917]
- FIXED: Conference: validate several conference parameters before building AMI
  requests with them. Fixes Elastix bug #1551.
  SVN Rev[4915]
- CHANGED: pbx - Build/elastix-pbx.spec: Was added in the post section the
  creation of file /etc/asterisk/vm_general_custom.conf.

* Mon May 06 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Voicemail: check that voicemail password is numeric before writing
  configuration. Fixes Elastix bug #1547.
  SVN Rev[4886]
- FIXED: Voicemail: check that specified extension belongs to user before
  deleting voicemail. Fixes Elastix bug #1546.
  SVN Rev[4885]

* Wed Apr 10 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-4
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
  SVN Rev[4831]

* Wed Apr 10 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changed in module general_settings admin to
  delete saydurationm from voicemail parameters.
  SVN Rev[4830]
- CHANGED: Apps - PBX: Was made changed in module inbound_route to fix bug at
  the moment to report all the inbound_routes.
  SVN Rev[4829]
- CHANGED: Apps - PBX: Was made changed in the deafult configuration that exist
  in files iax.conf and voicemail.conf. In voicemail.conf was deleted parameter
  saydurationm because it causes problems at the moment to call VoiceMailMain
  application.
  SVN Rev[4828]

* Tue Apr 09 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-3
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
  SVN Rev[4807]

* Tue Apr 09 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was updated spec file in order to set appropiate
  permission to file odbc.ini that was added in commit 4802.
  SVN Rev[4803]

* Tue Apr 09 2013 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX: Was added file odbc.ini to /etc directory. In this file
  are configured the connection to database. The connection define here are
  used for asterisk
  SVN Rev[4802]

* Tue Apr 09 2013 Rocio Mera <rmera@palosanto.com>
- ADD: APPS - PBX: Was added files res_odbc.conf and func_odbc.conf to
  /etc/asterisk. The file res_odbc.conf contains the dsn conection to elxpbx
  database. The conetion defined there is used by the file extconfig.conf and
  func_odbc.conf
  CHANGED: APPS - PBX: File extconfig.conf was edited in order to use odbc
  connection to connect elxpbx to use realtime.
  SVN Rev[4801]

* Fri Apr 05 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changes in module Trunk. Was deleted
  restriction in username field that made this field can't be empty
  SVN Rev[4797]

* Fri Apr 05 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changes in module Trunk. At the moment to
  create a trunk the defautl values to parameters nat and dtmfmod. The dedault
  value of these settings are nat=no and dtmfomde=auto
  SVN Rev[4796]

* Tue Apr 02 2013 Rocio Mera <rmera@palosanto.com>
- DELETE: Apps - PBX: Was deleted file extensions_globals.conf that was added
  in the commit 4784
  SVN Rev[4785]

* Tue Apr 02 2013 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX: Was added file extensions_globals.conf
  SVN Rev[4784]

* Thu Mar 14 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Batch of Extensions: the keywords that encode the recording options
  must be written to the database with capital first letter. Fixes rest of
  Elastix bug #1435.
  SVN Rev[4755]

* Thu Mar 14 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Batch of Extensions: relax extension secret validation to match the
  validations performed by the FreePBX javascript checks. Fixes part of Elastix
  bug #1435.
  SVN Rev[4754]

* Mon Feb 18 2013 Rocio Mera Suarez <rmera@palosanto.com>
- CHANGED: Now the spec create files sip_general_custom,iax_general_custom,
  extensions_globals.conf. Also was deleted the creation of unnecessary files
  in folder etc/asterisk like sip_additional.conf,iax_additional.conf.

* Mon Feb 18 2013 German Macas <gmacas@palosanto.com>
- ADD: modules: endpoint_configurator: Add suppor to set new model Snom m9
  SVN Rev[4692]

* Mon Feb 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changes in priviliged script
  asteriskconfig.conf. This changes permit the creation of file sip.conf and
  iax.conf using the information saved in table sip_general.conf and
  iax_general.conf.
  ADD: Apps - PBX: Was addded file extensions.conf and extensions_general.conf
  to astect directory. This file contain a basic dialplan that is used in the
  PBX
  CHANGES: Apps - PBX: Was modified files sip.conf, iax.conf and
  voiceamil.conf. This file contain the general configurations of sip,iax and
  voicemail
  SVN Rev[4679]

* Mon Feb 18 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changes in database elxpbx. The format of table
  sip_general, iax_general and voicemail_general was modified. It was made in
  order to support implementation of new module general_settings_admin.
  SVN Rev[4678]

* Sat Feb 16 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Reverting commit 4675 in which module conference was
  deleted
  SVN Rev[4677]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- DELETED: Apps - PBX: Was deleted module conference in order to add a new
  implementation
  SVN Rev[4675]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was fixed in module recordings some minor bugs
  SVN Rev[4674]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changed in modules queues,outbound_route and
  trunks. Was modified the effect at moment to change fron one tab to other
  inside the module
  SVN Rev[4673]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changed in modules queues,outbound_route and
  trunks. Was modified the effect at moment to change fron one tab to other
  inside the module
  SVN Rev[4672]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was fixed in module ring_group bug that permit at the
  moment to edit a ring_group set its name to empty. Also was improve the
  javascript that permit select valid destinations inside the ivr
  SVN Rev[4670]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: In module extensions was changed some parameters at the
  moment to create a extension that use SIP technology. Was added the
  parameters transport,sendrpdi,trusrpdi. In addition was added the parameters
  emailbody and emailsubject to Vociemail section
  SVN Rev[4669]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was fixed in module ring_group bug that permit at the
  moment to edit a ring_group set its name to empty
  SVN Rev[4668]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- Changed: Apps - PBX: Was made changed in module general_settings. Was added
  new configuration options in SIP and Voicemail sections
  SVN Rev[4667]

* Fri Feb 15 2013 Rocio Mera <rmera@palosanto.com>
- ADD: Apps - PBX: Was added to PBX module general_settings_admin. This module
  permit to superadmin set general configurations in the PBX. This
  configurations are related with SIP, IAX, Voicemail technologies
  SVN Rev[4666]

* Fri Feb 08 2013 German Macas <gmacas@palosanto.com>
- ADD: modules: endpoint_configurator: Add support to set new model Snom 821
  SVN Rev[4664]

* Wed Feb 06 2013 German Macas <gmacas@palosanto.com>
- ADD: modules: endpoint_configurator: Add support to set new Fanvil models
  C56/C56P C58/C58P and C60
  SVN Rev[4661]

* Thu Jan 31 2013 German Macas <gmacas@palosanto.com>
- ADD: modules: endpoint_configurator: Add support to set new Yealink model
  SIP-T38G and automatic provision in VP530 model
  SVN Rev[4659]

* Tue Jan 29 2013 Luis Abarca <labarca@palosanto.com>
- REMOVED: pbx - modules/index.php: It were removed innecesary information when
  Festival is activated.
  SVN Rev[4652]

* Thu Jan 24 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: In module Control_Planel was made changes in function
  showChannels in order to fix bugs in wich the call made through a sip trunk
  have not been displayed in control panel
  SVN Rev[4616]

* Wed Jan 23 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changes in priviliged script asteriskConfig.
  Thi was made in order to add function changeStateOrganization
  SVN Rev[4613]

* Fri Jan 18 2013 Rocio Mera <rmera@palosanto.com>
- =CHANGED: Apps - PBX : In modules Extensions y Queues was added a restriction
  that limit the number of extensions and queues that a organization can
  create. This is based in the max numbers of extensions and queues setting at
  the moment to create such organization
  SVN Rev[4595]

* Wed Jan 16 2013 Luis Abarca <labarca@palosanto.com>
- CHANGE: modules - packages - festival -antispam: Change grid view and add
  option to Update packages in Package module - Fixed bug in StickyNote
  checkbox in festival and antispam modules
  SVN Rev[4588]

* Sat Jan 12 2013 German Macas <gmacas@palosanto.com>
- CHANGE: modules : endpoint_configurator : endpoint_configurator: Add support
  to set new Vendor Atlinks model Alcatel Temporis IP800 and fix Label Select a
  Model and Unselected in Endpoint Configurator grid
  SVN Rev[4583]

* Sat Jan 12 2013 Luis Abarca <labarca@palosanto.com>
- FIXED: The behavior of the checkbox in the sticky-notes its now normal,
  showing the checkbox instead of the ON-OFF slider button. Fixes Elastix BUG
  #1424 - item 3
  SVN Rev[4582]

* Sat Jan 12 2013 German Macas <gmacas@palosanto.com>
- CHANGE: modules - endpoint_configurator: Add support to set new Vendor
  Atlinks model Alcatel Temporis IP800 and fix Label Select a Model and
  Unselected in Endpoint Configurator grid
  SVN Rev[4581]

* Mon Jan 07 2013 German Macas <gmacas@palosanto.com>
- CHANGE: modules - endpoint_configurator - endpoints_batch: Add support to set
  new Vendors and models  Damall D3310 and Elastix LXP200
  SVN Rev[4560]

* Fri Jan 04 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was added support in module outbound_route to add
  time_group conditions in the settings at the moment to create or updated a
  route. In module trunk was fixed a issue that happened when updated a trunk,
  some parameters weren't saved with incorrects values in astDB base
  SVN Rev[4549]

* Wed Jan 02 2013 Rocio Mera <rmera@palosanto.com>
- Apps - Modules/Trunk: Was added support to create custom trunk in module
  trunk
  SVN Rev[4541]

* Fri Dec 28 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made chances in priviliged script asteriskconfing
  and generic_extensions.conf to set CDR record organization. Also was improve
  the dialplan to avoid the needed to reload the dialplan when is created a new
  did. To this is necesary add support for odbc functions. The file
  /etc/asterisk/sip.conf was added new globlas parameters to support fax
  detection and increase security
  SVN Rev[4536]

* Thu Dec 27 2012 Sergio Broncano <sbroncano@palosanto.com>
- CHANGED: module extensions_batch, Secret field validation must be minimum 6
  alphanumeric characters, including upper and lowercase.
  SVN Rev[4532]

* Thu Dec 20 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: module endpoint configurator, default DTMF mode was audio, now default
  DTMF is RFC. Grandstream model GXV280. Ported to new endpoint configurator.
  SVN Rev[4528]

* Thu Dec 20 2012 Bruno Macias <bmacias@palosanto.com>
- FIXED: module endpoint configurator, default DTMF mode was audio, now default
  DTMF is RFC. Grandstream model GXV280
  SVN Rev[4527]

* Mon Dec 17 2012 German Macas <gmacas@palosanto.com>
- CHANGED: modules - Recordings: Add superadmin access to manage recordings
  SVN Rev[4519]

* Fri Dec 14 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Now Module Ring_Group don't permit to modified the
  ring_group number once time a ring_group has been created, this was made to
  avoid missing destination if the ring_group is been used as a destination
  inside the dialplan
  SVN Rev[4518]

* Fri Dec 14 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was added to database elxpbx table meetme. This table
  contains the information necesary to create conference with realtime
  technology. Also was modified file extensions_generic.conf to record
  correctly the recodings made in the conferences.  Was modified file
  meetme.conf and spec file to add support to new models of conferences
  SVN Rev[4517]

* Fri Dec 14 2012 Bruno Macias <bmacias@palosanto.com>
- NEW: module conference: new implementation, now support schedule and not
  schedule conference with realtime.
  SVN Rev[4515]

* Fri Dec 14 2012 Bruno Macias <bmacias@palosanto.com>
- DELETED: module conference, Conference module was deleted by new
  implementation.
  SVN Rev[4514]

* Fri Dec 14 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Elastix Operator Panel: assign dummy logger to silence logging spam on
  httpd error logfile. Fixes Elastix bug #1426.
  SVN Rev[4512]

* Tue Dec 11 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Batch of Extensions: if individual extensions list a blank context,
  assume from-internal. Fixes Elastix bug #854.
  SVN Rev[4509]

* Wed Dec 05 2012 Luis Abarca <labarca@palosanto.com> 3.0.0-2
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
  SVN Rev[4506]

* Wed Dec 05 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was modified dialplan generated by module ivr. Was set
  in a correct form the timeout for wait a user enter a option in the ivr
  SVN Rev[4505]

* Tue Dec 04 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: modules - file_editor - sec_weak_keys: Fixed item 4 and 5 from bug
  1416, keep search filter in file_editor and change Reason for Status in
  sec_weak_keys
  SVN Rev[4503]

* Mon Dec 03 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was fixed in module trunk the conditions that field
  max_calls was obligatory
  SVN Rev[4493]

* Mon Dec 03 2012 German Macas <gmacas@palosanto.com>
- CHANGE: modules - endpoint_configurator: Add Support to set new model Escene
  620 and Fixed bug in Fanvil vendor
  SVN Rev[4492]

* Mon Dec 03 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX: Was added new module time_conditions. This module permit
  actions based in if the time match or not with a time_group already created
  SVN Rev[4490]

* Fri Nov 30 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Text to Wav: change operation mode of module. Now the module will
  stream the output of text2wave directly without creating a downloadable file
  in a web directory. This removes one requirement for a web directory that is
  both readable and writable by the httpd user.
  SVN Rev[4486]

* Fri Nov 30 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Framework - DB/elastix: Was deleted permission to superadmin to
  acces modules recordings
  SVN Rev[4485]

* Fri Nov 30 2012 German Macas <gmacas@palosanto.com>
- FIXED: modules - Recordings: Fixed security bugs and mp3 file conversion to
  wav
  SVN Rev[4484]

* Thu Nov 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changed in module ivr to add support to
  reproduce the recordings in the dialplan
  SVN Rev[4477]

* Thu Nov 29 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Festival: fix iButton setup so that it runs directly from $.ready
  instead of $.change. Fixes part 1 of Elastix bug #1416.
  SVN Rev[4476]

* Thu Nov 29 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Festival: fix iButton setup so that it runs directly from $.ready
  instead of $.change. Fixes part 1 of Elastix bug #1416.
  SVN Rev[4475]

* Thu Nov 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was made changes in module recording
  SVN Rev[4474]

* Thu Nov 29 2012 German Macas <gmacas@palosanto.com>
- ADD: modules - Recordings: Module to upload or create audio files
  SVN Rev[4472]

* Thu Nov 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/DB: Was modified table recordings in database elxpbx
  SVN Rev[4470]

* Thu Nov 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was modified priviliged script asteriskconfig to provied
  support to ring_groups and time_conditions
  SVN Rev[4466]

* Thu Nov 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was modified module Trunk to fix bugs refered to
  visualization on num_calls by trunk.
  SVN Rev[4464]

* Wed Nov 28 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/trunk: Was added new a feature security that permit the
  superadmin set a max num of calls in a period of time
  SVN Rev[4460]

* Fri Nov 23 2012 Rocio Mera <rmera@palosanto.com>
- FIXED: modules - extensions_batch: Bug 1117, set disable voicemail from csv
  file.
  SVN Rev[4456]

* Wed Nov 21 2012 Rocio Mera <rmera@palosanto.com>
- ADD: modules - endpoint_configurator: Add support to set new model Fanvil C62
  and fix validation in vendor Atcom.cfg
  SVN Rev[4449]

* Wed Nov 21 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX: Was added module time_group. This module permit create a
  list of time ranges that later could be used in module time_conditions to
  stablish actions based in that time_group
  SVN Rev[4447]

* Wed Nov 14 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was modified arrLanguage from module musiconhold
  SVN Rev[4435]

* Wed Nov 14 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX/modules: Was added a module 'musiconhold' to PBX. This
  modules permit create new musiconhold calls inside asterisk.
  SVN Rev[4432]

* Mon Nov 05 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/agi-bin: Was modified script pbdirectory and
  dialparties.agi
  SVN Rev[4407]

* Mon Nov 05 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX. Was modified file extensions_generic
  SVN Rev[4406]

* Mon Nov 05 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/Setup. Was added table ring_group and was modified table
  musiconhold
  SVN Rev[4405]

* Mon Nov 05 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX. Was fixed minor bugs in modules extensions and
  features_code
  SVN Rev[4404]

* Mon Nov 05 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/ring_group. Was modified part the dialplan that
  correspond to ring_groups modules
  SVN Rev[4403]

* Thu Nov 01 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Batch of Extensions: replace brittle regexp parsing of voicemail.conf
  and repeated invocation of grep with a single load. The rewritten loading of
  voicemail.conf is also more robust in the face of missing/additional/reordered
  key/value pairs in vm_options. Fixes Elastix bug #1117.
  SVN Rev[4401]

* Thu Nov 01 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - Setup: Was modified file generic_extensions.conf
  SVN Rev[4400]

* Thu Nov 01 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - DB/elxpbx: Was added table ring_group
  SVN Rev[4399]

* Thu Nov 01 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX/Ring_Group: Was added a new module, called ring_group to
  PBX. This module permit the creation of ring groups
  SVN Rev[4398]

* Mon Oct 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/Setup: Was modified privaleged script asteriskconfig.
  SVN Rev[4388]

* Mon Oct 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/Setup: Was modified dialplan in file
  extensions_generic.conf.
  SVN Rev[4387]

* Mon Oct 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/DB: Was modified databases elxpbx. Added new field in
  tables outbound_route and inbound_route
  SVN Rev[4386]

* Mon Oct 29 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/Modules: Was add the functionality of Vmx Locator in
  module extensions. Was finished the module outbound_route, now create the
  dialplan for the outbound_route. Was fixed little bugs in module
  inbound_route
  SVN Rev[4385]

* Thu Oct 18 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: pbx - Build/elastix-pbx.spec: For correct behavior of rmdir we have to
  erase all folders that exists inside the dir in order to erase it.
  SVN Rev[4371]

* Thu Oct 18 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/setup: Was edited privileged script asteriskconfig to fix
  some bugs dialplan
  SVN Rev[4363]

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

* Mon Oct 15 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: allow listmacip to be interrupted via a
  signal in order to implement cancellation of runaway network scans.
  SVN Rev[4341]

* Fri Sep 28 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was assigned A DID to call incoming from analog channels
  SVN Rev[4314]

* Fri Sep 28 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was added functionality to detect incoming fax
  SVN Rev[4312]

* Wed Sep 26 2012 Luis Abarca <labarca@palosanto.com>
- DELETED: Iax and Sip configurations files are no longer needed for now.
  SVN Rev[4307]

* Wed Sep 26 2012 Luis Abarca <labarca@palosanto.com>
- CHANGED: Now the spec create iac and sip registrantions files.
  SVN Rev[4306]

* Wed Sep 26 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/Outbound_route: Was reverted commit 4304
  SVN Rev[4305]

* Wed Sep 26 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/trunks: Was fixed bug to disabled trunks
  SVN Rev[4304]

* Tue Sep 25 2012 Rocio Mera <rmera@palosanto.com>
- FIXED: Apps -PBX/inbound_route: Was fixed error when was writting dialplan
  SVN Rev[4293]

* Tue Sep 25 2012 Rocio Mera <rmera@palosanto.com>
- DELETED: call to function include its not longer needed
  SVN Rev[4288]

* Tue Sep 25 2012 Rocio Mera <rmera@palosanto.com>
- FIXED: Now the squema creates the elxpbx db.
  SVN Rev[4287]

* Tue Sep 25 2012 Luis Abarca <labarca@palosanto.com>
- DELETED: prereq my_extension its no longer needed.
  SVN Rev[4282]

* Tue Sep 25 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX/OutbondRoute: Was added module outbound route. Was fixed
  bugs in module trunks
  SVN Rev[4274]

* Mon Sep 24 2012 Luis Abarca <labarca@palosanto.com>
- UPDATED: file installer.php into pbx, this file was cleared, its content was
  obsoleted.
  SVN Rev[4267]

* Mon Sep 24 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: Wildcard and path to elastixdir + permissions
  SVN Rev[4266]

* Mon Sep 24 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: Wildcard and path to elastixdir.
  SVN Rev[4263]

* Mon Sep 24 2012 Luis Abarca <labarca@palosanto.com>
- ADDED: Now pbx need a folder called elastixdir.
  SVN Rev[4260]

* Sat Sep 22 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/Trunks: Was fixed some bugs and added options to
  relationship organization
  SVN Rev[4258]

* Sat Sep 22 2012 Luis Abarca <labarca@palosanto.com>
- DELETED: Call to retrieve_conf its no longer necesary.
  SVN Rev[4253]

* Sat Sep 22 2012 Luis Abarca <labarca@palosanto.com>
- ADDED: Function load_default_timezone for resolve a warning launched by date
  function.
  SVN Rev[4252]

* Fri Sep 21 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: Now the squema creates the elxpbx db.
  SVN Rev[4251]

* Fri Sep 21 2012 Bruno Macias <bmacias@palosanto.com>
- UPDATED: spec file elastix-pbx was recontructed, optimazed for elastix 3.
  SVN Rev[4250]

* Fri Sep 21 2012 Rocio Mera <rmera@palosanto.com>
- =CHANGED: Apps - PBX/trunk: Was modified file asteriskconfig
  SVN Rev[4247]

* Fri Sep 21 2012 Rocio Mera <rmera@palosanto.com>
- =ADDED: Apps - PBX/trunk: Was added module trunk. THis module permit the
  superadmin create and manage the trunk
  SVN Rev[4246]

* Fri Sep 21 2012 Rocio Mera <rmera@palosanto.com>
- =CHANGED: Apps - PBX/elxpbx: Was modified database elxpbx was modified table
  trunk and was add tables did and did_details
  SVN Rev[4245]

* Fri Sep 21 2012 Rocio Mera <rmera@palosanto.com>
- =ADDED: Apps - PBX/agibin: Was added agi script.
  SVN Rev[4244]

* Fri Sep 21 2012 Rocio Mera <rmera@palosanto.com>
- =ADDED: Apps - PBX/astetc: Was added folder astect. This directory contain
  asterisk configuration file
  SVN Rev[4243]

* Fri Sep 21 2012 Rocio Mera <rmera@palosanto.com>
- ADD: templates asterisk config files for support.
  SVN Rev[4242]

* Fri Sep 21 2012 Rocio Mera <rmera@palosanto.com>
- ADD: elastixdir directory, this directory is used pbx module for general
  settings organizations
  SVN Rev[4241]

* Fri Sep 21 2012 Sergio Broncano <sbroncano@palosanto.com>
- CHANGED: MODULE - PBX/EXTENSION_BATCH: Password at least 6 characters, and
  query parameters for downloading extensions.

* Thu Sep 20 2012 Luis Abarca <labarca@palosanto.com>
- CHANGED: pbx - Build/elastix-pbx.spec: The prereq freepbx were deleted.
  SVN Rev[4238]

* Thu Sep 20 2012 Luis Abarca <labarca@palosanto.com> 3.0.0-1
- CHANGED: pbx - Build/elastix-pbx.spec: Update specfile with latest
  SVN history. Changed version and release in specfile.
- CHANGED: In spec file changed Prereq elastix-framework, elastix-my_extension and
  elastix-system to >= 3.0.0-1
  SVN Rev[4229]

* Thu Sep 13 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX/Setup/asteriskconfig: Was modified privileged script
  asteriskconfig. Was added functionality to create queue and ivr dialplan
  SVN Rev[4208]
- CHANGED: Apps - PBX/Setup/generic_extensions.conf: Was modified the file
  generic_extensions.conf. Was solve problem when use feature code automon
  SVN Rev[4207]
- CHANGED: Apps - PBX/Setup/elxpbx: Was modified the database elxpbx. Was added
  tables ivr, ivr_destination, queue y queue_member
  SVN Rev[4206]
- CHANGED: Apps - PBX/Modules: Where made changed in modules features_code and
  extensions to fixed some bugs
  SVN Rev[4205]
- ADDED: Apps - PBX/Modules: Was added new module ivr. This module permit the
  creation and edition of ivr in asterisk
  SVN Rev[4204]
- ADDED: Apps - PBX/Modules: Was added new module queues. This module permit
  the creation and edit of queue in asterisk using realtime technology
  SVN Rev[4203]

* Thu Sep 13 2012 Sergio Broncano <sbroncano@palosanto.com>
- CHANGED: MODULE - PBX/EXTENSION_BATCH: Query parameters to download the file
  .csv
  SVN Rev[4202]

* Mon Sep 10 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Control Panel: fix failure to update interface after user opens browser
  tab to different Elastix module while keeping Control Panel open.
  SVN Rev[4194]
- CHANGED: Endpoint Configurator: revert emergency commit (again). Now with a
  fat warning to update elastix-system instead.
  SVN Rev[4193]
- CHANGED: Port changes to Grandstream configurator for batch configuration to
  new parallel implementation.
  SVN Rev[4192]

* Fri Sep 07 2012 Sergio Broncano <sbroncano@palosanto.com>
- ADD: Module Endpoint Configurator, Endpoints Batch, Added support for phones
  Grandstream models GXP2100, GXP1405.
  SVN Rev[4191]
- ADD: Module Endpoint Configurator, Endpoints Batch, Added support for phones
  Grandstream models GXP2100, GXP1405, GXP2120.
  SVN Rev[4187]

* Mon Sep 03 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: fill-out implementation of Sangoma Vega
  endpoint. Copy completed implementation to trunk.
  SVN Rev[4181]

* Mon Sep 03 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Batch of Endpoints: reimplement CSV download to take into account all
  of the endpoints that were configured via Endpoint Configurator and therefore
  have no parameters as inserted by Batch of Endpoints. Fixes Elastix bug #1360.
  SVN Rev[4175]

- CHANGED: Endpoint Configurator: revert emergency commit. The problem that was
  fixed in this commit should no longer occur with the Prereq: elastix-system
  >= 2.3.0-10 that fixed Elastix bug #1358.
  SVN Rev[4174]

* Fri Aug 31 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX/Setup: Was modified database elxpbx
  SVN Rev[4171]
- ADDED: Apps - PBX/Setup: Was modified privileged scrip asteriskconfig. This
  script write dialplan asterisk configuration
  SVN Rev[4170]
- ADDED: Apps - PBX/Setup: Was added file phpagi-asmanager.php. This file
  contain function used with asterisk manager application
  SVN Rev[4169]
- CHANGED: Apps - PBX: Was modified file generic_extensions.conf. Was resolved
  problem when realized recordings of calls and other issues
  SVN Rev[4168]
- CHANGED: Apps - PBX: Was finished module general_settings. This modules
  permit to each organization admin customized this pbx
  SVN Rev[4166]

* Fri Aug 31 2012 Alex Villacis Lasso <a_villacis@palosanto.com> 2.3.0-14
- FIXED: Prereq: elastix-system >= 2.3.0-10. Fixes Elastix bug #1358.
  SVN Rev[4164]

* Fri Aug 31 2012 German Macas <gmacas@palosanto.com>
- FIXED: modules -control_panel : Reset counter in queues when there are not
  calls
  SVN Rev[4162]

* Thu Aug 30 2012 Bruno Macias <bmacias@palosanto.com>
- FIXED: modules - endpoint_configurator: network() function was changed,
  paloSantoNetwork invoke bad format.
  SVN Rev[4160]

* Fri Aug 24 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - PBX/Privileged: Was added scrip asteriskconfig. This script
  help in generation dialplan
  SVN Rev[4150]
- CHANGED: Apss - PBX/db: Was added to elxpbx sql script insert that must be
  done while installation time
  SVN Rev[4148]

* Fri Aug 24 2012 Bruno Macias <bmacias@palosanto.com>
- UPDATED: file script install elxpbx database was updated, user asteriskuser
  privileges
  SVN Rev[4147]

* Fri Aug 24 2012 Bruno Macias <bmacias@palosanto.com>
- UPDATED: file script install elxpbx database was updated, user asteriskuser
  privileges
  SVN Rev[4146]

* Fri Aug 24 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: App - PBX: Was added file generic_extensions.conf. This file contain
  the dialplan used in asterisk
  SVN Rev[4145]

* Fri Aug 24 2012 Bruno Macias <bmacias@palosanto.com>
- CHANGED: database elxpbx was moved from framework  to apps
  SVN Rev[4144]

* Fri Aug 24 2012 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - PBX: Was changed name database from elx_pbx to elxpbx
  SVN Rev[4142]
- CHANGED: Apps - PBX: Was changed name database from elx_pbx to elxpbx
  SVN Rev[4140]
- ADDED: Apps - PBX: Was added a new module general_settings. This module
  permit edit parameters sip,iax,voicemail configuration by organization
  SVN Rev[4135]
- CHANGED: Was added module features_code. This module is used to configurate
  dialplan of each organization
  SVN Rev[4134]

* Thu Aug 23 2012 Alberto Santos <asantos@palosanto.com> 2.3.0-13
- CHANGED: module voipprovider, added spanish translation to an
  informative message
  SVN Rev[4117]
- CHANGED: module voipprovider, this module was removed from core.
  An informative message is added to indicate to the user that this
  is now an Addon.
  SVN Rev[4116]
- ADDED: added new agi script called hangup.agi that is executed in
  file extensions_override_elastix.conf. This agi intends to be an
  intermediary between addons scripts that needs information about a
  call as soon as it is hang up. This addons_scripts must be in path
  /usr/local/elastix/addons_scripts
  SVN Rev[4114]
- CHANGED: Module Batch of Extensions: By downloading the csv file
  batch of Extensions reflects the Record Incoming and Record Outgoing
  ("Adhoc") as "On Demand".
  SVN Rev[4112]
- CHANGED: Menu.xml: The Level 2 module named "Endpoints", now called
  "Batch Configurations".
  CHANGED: Module Endpoint Configurator: The warning message that shows
  before discovering the endpoints on the network.
  ADD: Module Endpoints Batch: Download the current endpoints in CSV format
  CHANGED: Module Batch of Extensions: Upload the CSV with multiple subnets
  separated by "&" in the "Denny" and "Permit".
  CHANGED: Module Batch of Extensions: The parameters "IMAP Username" and
  "IMAP Password" is not shown in the "VM Options".
  CHANGED: Module Batch of Extensions: By downloading the csv file batch
  of Extensions reflects the Record Incoming and Record Outgoing ("Adhoc")
  as "On Demand".
  CHANGED: Module Batch of Extensions: Field "Secret" must have minimum 8
  alphanumeric characters, case sensitive.
  SVN Rev[4111]
- FIXED: modules - antispam - festival - sec_advanced_setting - remote_smtp:
  Fixed graphic bug in ON/OFF Button
  SVN Rev[4101]
- CHANGED: module pbx, deleted tables and queries related to voipprovider
  SVN Rev[4090]
- Fixed bug 0001318, bug 0001338: fixed in Asterisk File Editor return last
  query in Back link, fixed Popups, position and design, add in Dashboard
  Applet Admin option to check all
  SVN Rev[4088]
- Add Mac and application form to Set Sangoma Vega Gateway
  SVN Rev[4084]

* Mon Jul 30 2012 Rocio Mera <rmera@palosanto.com>
- ADDED: Apps - Modules/PBX: Was added a new module called extensions. This
  module is used to create extension type sip or iax2 using asterisk realtime
  technology
  SVN Rev[4081]

* Fri Jul 20 2012 German Macas <gmacas@palosanto.com>
- CHANGED: modules - endpoint_configurator: Add sql_insert and code to set
  models XP0100P and XP0120P of Xorcom Vendor
  SVN Rev[4076]

* Fri Jul 20 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- ADDED: Endpoint Configurator: add new command-line utility.
  This new utility runs from /usr/bin/elastix-endpoint-configure. The program
  aims to introduce a new architecture for endpoint configuration, with better
  encapsulation of vendor-specific operations, and with an emphasis on parallel
  configuration of multiple endpoints for speed. The ultimate goal is to enable
  the quick configuration of hundreds of phones at once.
  SVN Rev[4075]

* Wed Jul 18 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- ADDED: Endpoint Configurator: add SQL for vendor, MAC and model for Zultys.
  MAC range taken from http://www.base64online.com/mac_address.php?mac=00:0B:EA

* Tue Jul 17 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Endpoint Configurator: for Cisco phones, syncinfo.xml must contain
  <SYNCINFO> and </SYNCINFO> tags, else Cisco phone will not reboot.
  SVN Rev[4065]

* Tue Jul 03 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Endpoint Batch: Properly figure out network mask for local network
  instead of hardcoding a /24 network mask. SVN Rev[4037]

* Fri Jun 29 2012 Luis Abarca <labarca@palosanto.com> 2.3.0-12
- CHANGED: pbx - Build/elastix-pbx.spec: update specfile with latest
  SVN history. Changed release in specfile.

* Fri Jun 29 2012 German Macas <gmacas@palosanto.com>
- ADDED: module - endpoints_batch: image file_csv.jpg for help of module.
  SVN Rev[4029]

* Thu Jun 28 2012 Luis Abarca <labarca@palosanto.com> 2.3.0-11
- CHANGED: pbx - Build/elastix-pbx.spec: update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[4025]

* Thu Jun 28 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Endpoint Configurator: Remove stray print_r.
  SVN Rev[4018]

* Wed Jun 27 2012 German Macas <gmacas@palosanto.com>
- CHANGED : modules - endpoint_configurator: Add function and sql statement to
  set the new model Yealink VP530 from Endpoint Configurator.
  SVN Rev[4014]

* Tue Jun 26 2012 Sergio Broncano <sbroncano@palosanto.com>
- ADDED: Module endpoints_batch, copy from trunk revision
  SVN Rev[4013]

* Mon Jun 25 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Conference: Remove XSS vulnerability.
  SVN Rev[4012]

* Tue Jun 19 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: Reimplement Grandstream configuration encoder
  as a pure PHP method. This allows the package to drop the Java-based encoder,
  which in turn allows the package to drop the openfire dependency.
- CHANGED: Endpoint Configurator: modify listmacip so that it can stream output
  from nmap as it is generated.
  SVN Rev[4009]

* Tue Jun 12 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Endpoint Configurator: Properly figure out network mask for local
  network instead of hardcoding a /24 network mask. SVN Rev[3993]
- FIXED: Endpoint Configurator: Relax pattern matching in listmacip script to
  account for differences in output format in nmap from CentOS to Fedora 17.
  SVN Rev[3992]
- FIXED: Endpoint Configurator: Use ip addr show instead of ifconfig to get
  size of network mask for endpoint scan. Required for compatibility with
  Fedora 17.
  SVN Rev[3989]

* Mon Jun 11 2012 Sergio Broncano <sbroncano@palosanto.com>
- ADD: MODULE endpoints_batch, Parent menu is created second level called "Endpoints".
  with their respective classification Batch of Extensions, Endpoint Configurator become
  the third-level menu, menu is also added a third level called Batch Enpoints enabling
  mass configuration enpoints so fast, taking as input: subnet where the endpoints are
  connected and a file (. csv) file that contains configuration parameters such as
  (Vendor, Model, MAC, Ext, IP, Mask, GW, DNS1, DNS2, Bridge, Time Zone).
  SVN Rev[3985]

* Thu Jun 7 2012 Alberto Santos <asantos@palosanto.com>
- NEW: new rest resource in pbxadmin to make calls.
  SVN Rev[3971]

* Tue Jun 05 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: installer.php: stop leaving temporary file /tmp/trunk_dump.sql around
  after install or update.
- FIXED: installer.php: stop leaving temporary file /var/www/db/trunk-pbx.db
  around on most install/update scenarios.
  SVN Rev[3959]

* Mon Jun 04 2012 Alex Villacis Lasso <a_villacis@palosanto.com> 2.3.0-10
- FIXED: Changed specfile so that several files are explicitly set as
  executable. Fixes Elastix bugs #1291, #1292.

* Fri Jun 01 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: On-demand recording must not use MIXMON_FORMAT. The format for
  recording is TOUCH_MONITOR_FORMAT, if defined, or wav if not defined.
- CHANGED: Use ASTSPOOLDIR instead of hardcoded /var/spool/asterisk.

* Mon May 28 2012 Sergio Broncano <sbroncano@palosanto.com> 2.3.0-9
- CHANGED: MODULES - PBX/EXTENSION_BATCH: The following fields were added
  callgroup, pickupgroup, disallow, allow, deny, permit, Record Incoming,
  Outgoing Record in extensiones.csv file to upload and download.
  SVN Rev[3940]

* Mon May 07 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-8
- UPDATED: UPDATED in specfile to release 8

* Thu May 03 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Conference: Check the context variable MEETME_ACTUAL_RECORDINGFILE
  alongside MEETME_RECORDINGFILE in order to decide whether a MeetMe recording
  exists. Before this check, CBMySQL conferences end up marking a recording as
  available in Monitoring report when no recording actually exists.
  FIXED: Conference: Check that MEETME_ACTUAL_RECORDINGFILE with MIXMON_FORMAT
  extension exists. If not, fallback to assuming .wav as file extension. Before
  this check, CBMySQL conference recordings are not downloadable if
  MIXMON_FORMAT is something other than .wav .
  SVN Rev[3926].

* Wed May 02 2012 Rocio Mera <rmera@palosanto> 2.3.0-7
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-9

* Wed May 02 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Conference: Rework several conference manipulation functions so that
  they use SQL parameters. Fixes Elastix bug #1256.
  FIXED: Conference: Reorder evaluation of actions so that kicking and muting
  participants actually works. Fixes Elastix bug #1245.
  SVN Rev[3916].
- FIXED: Conference: Validation of type "integer" for HH:MM is unimplemented.
  Use "numeric" instead.
  SVN Rev[3914].

* Fri Apr 27 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-6
- CHANGED: Addons - Build/elastix-addons.spec: update specfile with latest
  SVN history. Changed release in specfile
- FIXED: Monitoring: queue recording file names are written with full absolute
  path in DB, so a LIKE operator is required to select on the file name.
  Rewrite recording file download to add more checks. Part of fix for Elastix
  bug #1225.
  SVN Rev[3873]
- FIXED: Batch of Extensions: voicemail password (if any) should be
  numeric-only. Part of the fix for Elastix bug #1238.
  SVN Rev[3871]
- FIXED: PBX Admin: add enough of the FreePBX validation javascripts so that
  field validations work. Part of the fix for Elastix bug #1238.
  SVN Rev[3870]
- ADDED: Build - SPEC's: The spec files were added to the corresponding modules
  and the framework.
  SVN Rev[3854]
  SVN Rev[3836]
- FIXED: PBX - Festival: Fixed problem when chance status to festival. Bug 1219
  SVN Rev[3814]

* Mon Apr 02 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-5
- FIXED: PBX - Festival: Fixed problem when chance status to festival. Bug 1219
  SVN Rev[3814]

* Fri Mar 30 2012 Bruno Macias <bmacias@palosanto.com> 2.3.0-4
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-5
- CHANGED: Control Panel: sqlite query reports SELECT X.COLUMN as 'X.COLUMN' in
  CentOS and 'COLUMN' in Fedora. Query needs to explicitly set the column name
  and perform conversion to the expected format.
  SVN Rev[3806]
- FIXED: modules - SQLs DB: se quita SQL redundante de alter table y nuevos
  registros, esto causaba un error leve en la instalación de el/los modulos.
  SVN Rev[3797]

* Mon Mar 26 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-3
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-3
- CHANGED: In spec file, changed prereq freePBX >= 2.8.1-12
- CHANGED: modules/control_panel: Se define auto como propiedad de altura a las
  areas.
  SVN Rev[3779]
- CHANGED: PBX - Monitoring little change in index.php to fix problem appear
  the 'X' option in whose filters that are always active
  SVN Rev[3757]
- CHANGED: PBX - Voicemail index.php change in index.php to fix problem
  coulnd't be the voicemail in the elastix web interface
  SVN Rev[3756]

* Fri Mar 09 2012 Alberto Santos <asantos@palosanto.com> 2.3.0-2
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-2
- CHANGED: PBX Admin: bail out if unable to connect to Asterisk AMI.
  SVN Rev[3727]

* Wed Mar 07 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-1
- CHANGED: In spec file, changed prereq elastix-framework >= 2.3.0-1
- FIXED: modules - faxlist: Se corrige bug de pagineo en el modulo de faxlist.
  Tambien se definen correctamente ciertas traducciones.
  SVN Rev[3714]
- CHANGED: voipprovider index.php add control to applied filters
  SVN Rev[3706]
- CHANGED: file_editor index.php add control to applied filters
  SVN Rev[3705]
- CHANGED: conference index.php add control to applied filters
  SVN Rev[3704]
- CHANGED: voicemail index.php add control to applied filters
  SVN Rev[3703]
- CHANGED: voicemail index.php add control to applied filters
  SVN Rev[3702]
- CHANGED: endpoint_configurator index.php change to put new function outside
  of filter
  SVN Rev[3685]
- FIXED: Conference: honor new parameter isUserAdministratorGroup in
  listConferences(). Requires elastix-conferenceroom >= 2.0.0.
  SVN Rev[3680]
- FIXED: PBX - Monitoring: sometimes blind transfer results in two or more CDRs
  with the same uniqueid. When recording calls, it is necessary to specify the
  distinct call filename in order to have access to all portions of the
  recording.
  SVN Rev[3678]
- UPDATED: modules - hardware_detector: Se define ancho de la tabla que formar
  parte de un puerto, en chrome se mostraba mal los puertos.
  SVN Rev[3663]
- CHANGED: pbx control_panel Now the max numbers of columns is 4, modifcations
  in index.php. Add the file 3_2.2.0-26_2.2.0-27.sql in db/update to change
  take efect in updating
  SVN Rev[3651]
- CHANGED: little change in file *.tpl to better the appearance the options
  inside the filter
  SVN Rev[3639]
- CHANGED: modules - extension_batch/themes: The incorrect positioning of
  Dowload link is now corrected.
  SVN Rev[3631]
- FIXED: Modules - Monitoring: Bugs about cannot listen on the web in
  google-crone the recording
  * Bug:0001085
  * Introduced by:
  * Since: Development Monitoring Module
  SVN Rev[3630]
- FIXED: Modules - Monitoring: Bugs about cannot listen on the web the
  recording
  * Bug:0001085
  * Introduced by:
  SVN Rev[3629]


* Wed Feb 1 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-26
- CHANGED: In spec file, changed prereq elastix-framework >= 2.2.0-30
- CHANGED: file index.php to fixed the problem with the paged
  to show callers of conference. SVN Rev[3623].
- CHANGED: file index.php to fixed the problem with the paged
  SVN Rev[3620].
- CHANGED: file index.php to fixed the problem with the paged.
  SVN Rev[3617]. SVN Rev[3616]. SVN Rev[3610].

* Mon Jan 30 2012 Alberto Santos <asantos@palosanto.com> 2.2.0-25
- CHANGED: In spec file, changed prereq elastix-framework >= 2.2.0-29
- CHANGED: Changed the word 'Apply Filter' by 'More Option'
  SVN Rev[3601]

* Sat Jan 28 2012 Rocio Mera <rmera@palosanto.com> 2.2.0-24
- CHANGED: In spec file, changed prereq elastix-framework >= 2.2.0-28
- CHANGED: Added support for the new grid design. SVN Rev[3576].
- CHANGED: modules - images: icon image title was changed on some
  modules. SVN Rev[3572].
- CHANGED: modules - trunk/core/pbx/modules/monitoring/index.php:
  Se modifico los archivos index.php para corregir problema con
  botondeleted. SVN Rev[3570]. SVN Rev[3568].
- CHANGED: modules - icons: Se cambio de algunos módulos los iconos
  que los representaba. SVN Rev[3563].
- CHANGED: modules - * : Cambios en ciertos mòdulos que usan grilla
  para mostrar ciertas opciones fuera del filtro, esto debido al
  diseño del nuevo filtro. SVN Rev[3549].
- CHANGED: Modules - PBX: Added support for the new grid layout.
  SVN Rev[3548].
- UPDATED: modules - *.tpl: Se elimino en los archivos .tpl de
  ciertos módulos que tenian una tabla demás en su diseño de filtro
  que formaba parte de la grilla. SVN Rev[3541].


* Thu Jan 12 2012 Alberto Santos <asantos@palosanto.com> 2.2.0-23
- ADDED: In spec file, added prereq asterisk >= 1.8
- CHANGED: In spec file, changed prereq elastix-system >= 2.2.0-18
- CHANGED: In spec file, changed prereq freePBX >= 2.8.1-11
- FIXED: modules control_panel, when the button reload is pressed
  all the boxes are displayed twice. This is fixed by doing an
  element.empty().append() where element is the container.
  FIXED: modules control_panel, the destination call was not
  displayed and when the page is refreshed all the times are reset
  to 3. This bug happens due to the migration to asterisk 1.8 where
  the command "core show channels concise" shows in different
  positions the time and destination of the call.
  SVN Rev[3524]
- CHANGED: modules endpoint_configurator, the VoIP server address
  is now the network that belongs to the endpoint's network address
  SVN Rev[3523]
- CHANGED: modules endpoint_configurator, now the network
  configuration is not changed in atcom telephones
  SVN Rev[3511]
- ADDED: added new update script for enpoint.db database, this
  scripts adds new atcom models "AT610" and "AT640" also changes
  old atcom model names to "AT620", "AT530" and "AT320"
  SVN Rev[3508]
- ADDED: modules endpoint_configurator, added new atcom models
  "AT610" and "AT640" also changed the name of old atcom models
  to "AT320", "AT530" and "AT620"
  SVN Rev[3507]

* Tue Jan 03 2012 Alberto Santos <asantos@palosanto.com> 2.2.0-22
- CHANGED: modules - pbx/setup/db: Schema de instalación de la
  base meetme, fue modificado para que no asigne una contraseña
  al definir el GRANT de permisos a la base meetme que sea
  administrable por el usuario asteriskuser
  SVN Rev[3499]

* Fri Dec 30 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-21
- CHANGED: In spec file, removed the creation of user asterisk,
  that is handled by elastix-framework

* Thu Dec 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-20
- CHANGED: In spec file, changed prereq elastix-framework >= 2.2.0-24
- CHANGED: In spec file, now in this spec is handled everything related
  with asterisk and not any more on framework
- CHANGED: changed everything to do with asterisk from the framework
  to elastix-pbx
  SVN Rev[3495]

* Tue Dec 20 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-19
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-23
- FIXED: When export a csv file extensions, not export well voicemails fields,
  the problem was because the path was not sent to find if the extension had
  a voicemail active.. SVN Rev[3458]

* Thu Dec 08 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-18
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-21
- FIXED: Festival: Use 'pidof' instead of 'service festival status'
  to work around https://bugzilla.redhat.com/show_bug.cgi?id=684881
  SVN Rev[3431]

* Fri Nov 25 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-17
- CHANGED: In spec file, changed prereq to elastix-framework >= 2.2.0-18
- CHANGED: Recordings: use load_language_module instead of handcoding
  i18n loading
  FIXED: Recordings: fixed forgotten i18n string change in
  Spanish translation
  SVN Rev[3399]
- FIXED: module festival, informative message was only displayed
  for theme elastixneo. Now it is displayed for all the themes
  SVN Rev[3393]
- CHANGED: Festival: use privileged script 'festival' to reimplement
  festival server activation
  SVN Rev[3392]
- CHANGED: Festival: no need to remove festival service from chkconfig.
  SVN Rev[3391]
- CHANGED: Festival: check for non-existent festival.scm file
  SVN Rev[3390]
- ADDED: Festival: introduce new privileged script 'festival' to
  enable/disable Festival and to add required configuration.
  SVN Rev[3389]
- CHANGED: Festival: sudo is not required for querying festival
  server status
  SVN Rev[3388]
- FIXED: PBX Admin: freePBX modules always need type=setup defined,
  so define if display is not defined
  SVN Rev[3387]
- FIXED: PBX Admin: all freePBX module links need a type=setup
  GET parameter. Partially fixes PHP Notice:  Undefined index:
  1 in /var/www/html/admin/modules/fax/functions.inc.php on line 389
  SVN Rev[3386]
- FIXED: PBX Admin: assign instead of concatenate to $htmlFPBX.
  Fixes PHP Notice:  Undefined variable: htmlFPBX in
  /var/www/html/modules/pbxadmin/libs/contentFreePBX.php on line 492
  SVN Rev[3385]
- FIXED: PBX Admin: $amp_conf_defaults must be declared as global
  BEFORE admin/functions.inc.php, fixes PHP Warning:  Invalid
  argument supplied for foreach() in /var/www/html/admin/functions.inc.php
  on line 782
  SVN Rev[3384]
- CHANGED: Endpoint Configurator: use privileged script 'listmacip'
  to reimplement endpoint mapping
  SVN Rev[3382]
- CHANGED: Endpoint Configurator: privileged script 'listmacip'
  needs to report netcard vendor description too.
  SVN Rev[3381]
- ADDED: Endpoint Configurator: introduce new privileged script
 'listmacip' to map out available IPs and MACs in a network.
  This removes the need to allow full access to nmap via sudo.
  SVN Rev[3380]
- CHANGED: Endpoint Configurator: remove no-longer-necessary
  sudo chmod around invocation of encoder for GrandStream configurator.
  SVN Rev[3379]

* Wed Nov 23 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-16
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-17
- FIXED: module voicemail, wrong concatenation of variable
  $contenidoModulo, consecuence of this the filter is showed twice
  SVN Rev[3353]

* Tue Nov 22 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-15
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-15
- FIXED: Voicemail: remove nested <form> tag. SVN Rev[3270]
- CHANGED: module endpoint_configurator, extensions showed
  in field "Current Extension" are unregistered when the
  button set is pressed. SVN Rev[3267]
- CHANGED: module endpoint_configurator, changed width and
  align in input for discovering endpoints. SVN Rev[3263]

* Tue Nov 01 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-14
- CHANGED: In spec file, changed prereq freePBX >= 2.8.1-7
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-14
- CHANGED: module pbxadmin, was increased the width of warning
  message in option "Unembedded FreePBX"
  SVN Rev[3249]

* Sat Oct 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-13
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-13

* Sat Oct 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-12
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-12
- CHANGED: module pbxadmin, added a margin-top negative to the
  informative message in Unembedded FreePBX
  SVN Rev[3229]
- CHANGED: module control_panel, added a border-radius to the style
  SVN Rev[3228]
- CHANGED: module voipprovider, changed the color of fieldset border and title
  SVN Rev[3199]
- FIXED: module festival, messages was not displaying for theme elastixneo
  SVN Rev[3185]
- CHANGED: module voipprovider, the module title is now handled by the framework
  SVN Rev[3162]
- CHANGED: module recordings, the module title is now handled by the framework
  SVN Rev[3160]
- CHANGED: module text_to_wav, the module title is now handled by the framework
  SVN Rev[3158]
- CHANGED: module file_editor, the module title is now handled by the framework
  SVN Rev[3157]
- CHANGED: module asterisk_cli, the module title is now handled by the framework
  SVN Rev[3155]
- CHANGED: module extensions_batch, the module title is now handled by the framework
  SVN Rev[3153]
- CHANGED: module conference, the module title is now handled by the framework
  SVN Rev[3151]
- CHANGED: module voicemail, the module title is now handled by the framework
  SVN Rev[3150]
- CHANGED: module pbxadmin, changed the module title to "PBX Configuration"
  SVN Rev[3149]
- CHANGED: module pbxadmin, added a title to the module pbxadmin
  SVN Rev[3147]

* Mon Oct 17 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-11
- CHANGED: module endpoint_configurator, when a patton does not
  have 2 ethernet ports, the WAN options are not displayed.
  SVN Rev[3087]
- CHANGED: module recordings, added information. SVN Rev[3082]

* Thu Oct 13 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-10
- CHANGED: module endpoint_configurator, added asterisks in some
  required fields
  SVN Rev[3081]
- CHANGED: module endpoint_configurator, in case an error occurs
  and a file can not be created, a message is showed
  SVN Rev[3078]

* Fri Oct 07 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-9
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-8
- FIXED: module conference, added an id of "filter_value" to the
  filter text box
  SVN Rev[3035]

* Wed Sep 28 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-8
- FIXED: module extensions_batch, wrong name of button, changed
  "CVS" to "CSV"
  SVN Rev[3006]
- FIXED: module extensions_batch, only if the field "Direct DID"
  is entered, an inbound route is created
  SVN Rev[3005]

* Tue Sep 27 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-7
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-5
- CHANGED: changed the password "elastix456" of AMI to the
  password set in /etc/elastix.conf
  SVN Rev[2995]

* Thu Sep 22 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-6
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-4
- ADDED: module endpoint_configurator, added the option for
  patton configuration
  SVN Rev[2985]
- ADDED: added script sql for database endpoint, it creates
  a new table called settings_by_country and added the vendor Patton
  SVN Rev[2984]
- CHANGED: Conference: add new method required for verification
  of date range. Part of fix for Elastix bug #937.
  SVN Rev[2983]
- FIXED: Embedded FreePBX: include jquery.toggleval if available,
  to fix some javascript errors.
  SVN Rev[2981]
- FIXED: Elastix Operator Panel: IE6 through IE8 deal incorrectly
  with nested draggables, as detailed in http://bugs.jqueryui.com/ticket/4333.
  Applied workaround suggested in bug report.
  SVN Rev[2980]
- FIXED: Elastix Operator Panel: fix incorrect regular expression
  that missed extension names with dashes.
  SVN Rev[2979]
- CHANGED: Elastix Operator Panel: remove comment and trailing comma
  that trigger syntax error in IE6. Part of fix for Elastix bug #938.
  SVN Rev[2978]
- CHANGED: Elastix Operator Panel: use jQuery methods instead of
  innerHTML to insert table information. Part of fix for Elastix bug #938.
  SVN Rev[2977]
- CHANGED: Elastix Operator Panel: use DOM instead of innerHTML
  to insert loading animation. Part of fix for Elastix bug #938.
  SVN Rev[2976]
- FIXED: Control Panel: check for support of DOMParser and fall back
  to IE-specific code if not supported. Partial fix for Elastix bug #938.
  SVN Rev[2971]
- CHANGED: module text_to_wav, deleted unnecessary asterisks
  SVN Rev[2962]
- CHANGED: module extensions_batch, deleted unnecessary asterisks
  SVN Rev[2961]
- CHANGED: module monitoring, deleted unnecessary asterisks
  SVN Rev[2960]
- CHANGED: module voicemail, deleted unnecessary asterisks
  SVN Rev[2959]
- FIXED: module recordings, variable $pDB and $filename were not defined
  SVN Rev[2957]
- CHANGED: module recordings, database address_book.db is not
  used. Deleted any relation with that database
  SVN Rev[2954]

* Fri Sep 09 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-5
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-3
- CHANGED: module recordings, changed the location of module
  recordings, now it is in PBX->tools
  SVN Rev[2953]
- CHANGED: module conference, in view mode the asterisks and
  word required were removed
  SVN Rev[2951]
- FIXED: module endpoint_configurator, the version must have 4 decimals
  SVN Rev[2939]
- FIXED: module control_panel, word class is reserved in javascript
  for firefox >= 5. Changed the variable name to other one
  SVN Rev[2934]
- CHANGED: installer.php, deleted the register string of trunks
  created in voipprovider
  SVN Rev[2930]
- ADDED: added script sql for database control_panel_design.db,
  updated the description for the trunk area
  SVN Rev[2928]

* Tue Aug 30 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-4
- CHANGED: In spec file, verify the inclusion of
  sip_notify_custom_elastix.conf on /etc/asterisk/sip_notify_custom.conf
- CHANGED: installer.php, trunks created by provider_account were
  written in file /etc/asterisk/sip_custom.conf. Now when this
  script is executed these trunks are deleted from the mentioned
  file because now the voipprovider trunks are created in freePBX database
  SVN Rev[2925]

* Fri Aug 26 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-3
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-2
- ADDED: In spec file, added prereq elastix-system >= 2.2.0-5
- CHANGED: PBX: Return a diagnostic message instead of exiting
  when some FreePBX issue disables all modules.
  SVN Rev[2910]
- CHANGED: installer.php, the trunks created in voipprovider
  are also created in the database asterisk of freePBX
  SVN Rev[2890]
- FIXED: module monitoring, fixed many security holes in this module
  SVN Rev[2885]
- CHANGED: module voicemail, if user is not administrator and does
  not have an extension assigned only a message is showed
  SVN Rev[2883]

* Fri Aug 03 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-2
- FIXED: module control_panel, the queues was not showing the
  extension or agent which attends it. Now it shows all the
  extensions or agents that attent it
  SVN Rev[2873]

* Tue Aug 02 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-1
- ADDED: In Spec file added requires festival >= 1.95
- FIXED: module festival, informative message was not displayed.
  The error was fixed and now it is displayed
  SVN Rev[2863]

# el script de patton query debe moverse a /usr/share/elastix/priviliges en el spec
* Fri Jul 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-28
- CHANGED: in spec file changed prereq elastix >= 2.0.4-30
- ADDED: pbx setup/db, sql script to add iax support. SVN Rev[2842]
- ADDED: pbx setub, added the script that searchs for patton
  devices. SVN Rev[2841]
- ADDED: module endpoint_configurator, added support iax
  (on phones that support it), also added support to smartnodes.
  SVN Rev[2840]
- FIXED: extensions_override_elastix.conf, when the audio file
  is not created the field userfield is set empty in the database
  SVN Rev[2821]
- FIXED: module monitoring, when user is not admin the filter
  options dissapear. Now those options remains with any user.
  SVN Rev[2820]
- CHANGED: module festival, the button save was eliminated, now
  when user press on or off automatically make the action. SVN Rev[2798]
- CHANGED: module voicemail, changed message when user does not
  have an extension associated. SVN Rev[2794]
- CHANGED: module monitoring, changed message when a user does
  not have an extension associated. SVN Rev[2793]
- CHANGED: module voicemail, when the user does not have an
  extension associated, a link appear to assign one extension.
  SVN Rev[2790]
- CHANGED: module monitoring, The link here
  (when a user does not have an extension) now open a new window to
  edit the extension of the user logged in. SVN Rev[2788]
- ADDED: module extensions_batch, added iax2 support. SVN Rev[2774]

* Wed Jun 29 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-27
- FIXED: module festival, added a sleep of 2 seconds when the service
  is started that is the maximum time delay. SVN Rev[2764]

* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-26
- CHANGED: In spec file change prereq freepbx >= 2.8.1-1 and
  elastix >= 2.0.4-24
- CHANGED: Modules - Trunk: The ereg function was replaced by the
  preg_match function due to that the ereg function was deprecated
  since PHP 5.3.0. SVN Rev[2688]
- FIXED: module festival, wrong informative message the file
  modified is /usr/share/festival/festival.scm and not
  /usr/share/elastix/elastix.scm. SVN Rev[2669]
- CHANGED: The split function of these modules was replaced by the
  explode function due to that the split function was deprecated
  since PHP 5.3.0. SVN Rev[2650]

* Wed May 18 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-25
- CHANGED: change prereq of freePBX to 2.8.0-3

* Wed May 18 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-24
- CHANGED: module pbxadmin, library contentFreePBX.php updated with
  the last code in pbxadmin
  SVN Rev[2646]
- CHANGED: module pbxadmin, created a library that gets the content
  of freePBX modules
  SVN Rev[2645]
- FIXED: module voipprovider, when a trunk is created by voipprovider
  and then this one is deleted in freePBX, it is not deleted in the
  database of voipprovider. Now its deleted from the database of voipprovider
  SVN Rev[2640]
- ADDED: Conference: new Chinese translations for Conference interface.
  Part of fix for Elastix bug #876
  SVN Rev[2639]

* Thu May 12 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-23
- CHANGED: renamed sql scripts 4 and 5 for updates in database endpoint
  SVN Rev[2638]
- FIXED: Endpoint Configurator: check that selected phone model is
  a supported model before using include_once on it.
  FIXED: Endpoint Configurator: check that MAC address for endpoint
  is valid.
  SVN Rev[2637]
- ADDED: module endpoint_configurator, disabled other accounts in
  YEALINK phones.
  SVN Rev[2635]
- FIXED: File Editor: undo use of <button> inside of <a> as this
  combination does not work as intended in Firefox 4.0.1. Related
  to Elastix bug #864
  SVN Rev[2632]
- FIXED: module pbxadmin, added a width of 330px to the informative
  message in "Unembedded freePBX"
  SVN Rev[2627]
- FIXED: module pbxadmin, the option "Unembedded freePBX" was placed
  at the end of the list, also a warning message was placed on it.
  SVN Rev[2626]

* Thu May 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-22
- FIXED:    module pbxadmin, IVR did not displayed extensions,
  conferences, trunks, etc. Now that information is displayed
  according to the option selected in the combo box. SVN Rev[2620]
- CHANGED:  PBX - monitoring: Changed  value of
  $arrConfModule['module_name'] = 'monitoring2' to
  $arrConfModule['module_name'] = 'monitoring' in default.conf.php
  SVN Rev[2591]

* Tue Apr 26 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-21
- CHANGED: installer.php, changed installer.php in order to works for
  updates to elastix 2.0.4
  SVN Rev[2586]
- FIXED: module control_panel, added a validation in case there is no data
  SVN Rev[2585]
- ADDED: module festival, added folders lang, configs and help
  SVN Rev[2583]
- CHANGED: module voicemail, changed class name to core_Voicemail
  SVN Rev[2580]
- ADDED: added new provider called "Vozelia"
  SVN Rev[2574]
- CHANGED: provider vozelia was removed from the installation script
  SVN Rev[2573]
- CHANGED: module voicemail, changed name from puntosF_Voicemail.class.php
  to core.class.php
  SVN Rev[2571]
- UPDATED: module file editor, some changes with the styles of buttons
  SVN Rev[2561]
- NEW: new scenarios for SOAP in voicemail
  SVN Rev[2559]
- NEW: new module festival
  SVN Rev[2553]
- ADDED: added new module in tools called Festival
  SVN Rev[2552]
- NEW: service festival in /etc/init.d and asterisk file sip_notify_custom_elastix.conf
  SVN Rev[2551]
- CHANGED: In Spec file, moved the files festival and sip_notify_custom_elastix.conf

* Wed Apr 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-20
- FIXED: pbx - extension_batch: Removed download_csv.php, this file
  was removed in commit 1550 but this file was put in this package
  by error in the rpm version 2.0.4-19.
- ADDED: module endpoint_configurator, added the vendor LG-ERICSSON
  and the model IP8802A. SVN Rev[2536][2537]
- CHANGED: module endpoint_configurator, changed model names for
  phones Yealink. SVN Rev[2527][2529][2530]
- ADDED: module endpoint_configurator, added support for
  phones Yealink models T20, T22, T26 and T28. SVN Rev[2518][2519]

* Tue Apr 04 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-19
- FIXED: module voipprovider, undefined data was set to the
  combo box. Added a validation for default values in case of
  an undefined data. SVN Rev[2507]

* Mon Apr 04 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-18
- FIXED: module control_panel, when the area is empty, a box
  can not be dropped. Now it can. SVN Rev[2498]

* Thu Mar 31 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-17
- FIXED: Error to install databases of sqlite in "process of
  installation" because in spec file when mysql is down this
  event is sending to "first-boot" but only mysql scripts and
  not sqlite.

* Thu Mar 31 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-16
- FIXED: Module Conference, database meetme, bad defintion sql
  script was fixed. SVN Rev[2477]

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-15
- ADDED: module voicemail, added a new validation in case the
  path file does not exist when writing the file voicemail.conf.
  SVN Rev[2469]

* Thu Mar 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-14
- CHANGED: module voipprovider, now the provider net2phone is
  the first in the list of providers. SVN Rev[2391]
- ADDED:  file .sql to create a new column called orden in the
  table provider of the database trunk.db also the orden field
  was set for each provider. SVN Rev[2390]

* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-13
- CHANGED: in Spec file change prereq elastix>=2.0.4-10
- ADDED: module control_panel, added a loading image until all
  the boxes are loaded, also the info window was reduced.
  SVN Rev[2385]
- CHANGED: module voipprovider, voipprovider now insert the
  data in the database of freepbx and automatically reload
  asterisk files. SVN Rev[2384]
- ADDED: database trunk, added a column called id_trunk in
  table provider_account. SVN Rev[2382]
- FIXED: module voipprovider, the edit mode did not show the
  data of the account. Now the data is showed. SVN REV[2380]
- FIXED: module voipprovider, fixed the problem of moving down
  the peer settings options when the width of the browser is
  smaller. SVN Rev[2378]
- ADDED: module file_editor, added a new button called
  "Reload Asterisk" that applies the command module reload to
  asterisk. SVN Rev[2376]
- CHANGED: module endpoint_configurator, added a message when
  the files are configurated. SVN Rev[2373]
- CHANGED: module enpoint_configurator, changed the field status
  to current extension which shows the extension to which is
  registered the phone. SVN Rev[2371]
- FIXED:   Error to try to renove database meetme, change action
  "drop table meetme" to "drop database meetme". SVN Rev[2365]
- CHANGED: module voipprovider, added a checkbox called advanced
  that when is checked displays the PEER Setting options.
  SVN Rev[2358]
- ADDED: module endpoint_configurator, added the configuration
  for the vendor AudioCodes with models 310HD and 320HD.
  SVN Rev[2356]
- FIXED: module control_panel, the extensions on the area 1,2
  and 3 didnt show the status also when you call to a conference
  or a number that is not an extension the call destiny didn't
  display. All those problems were fixed. SVN Rev[2355]
- FIXED:  PBX - control Panel: Error in script.sql to update
  control panel to the next version, The error was the script
  try to update a table rate but it did not exit and the correct
  table was area. SVN Rev[2344]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-12
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-11
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format
  to sql script for administer process install, update and delete.
  In Installer.php remove all instances of .db but the logic to
  update the old versions of trunk.db is there. SVN Rev[2333]
- ADD:  PBX - setup: New schema organization to get better
  performance to databases sqlite and mysql. SVN Rev[2328]
- CHANGED: Module conference, meetme database was merged, now
  sql script is 1_schema.sql. SVN Rev[2317]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-10
- CHANGED:  menu.xml to support new tag "permissions" where has
  all permissions of group per module and new attribute "desc"
  into tag  "group" for add a description of group.
  SVN Rev[2294][2299]
- CHANGED: module endpoint_configurator, eliminated a print_r.
  SVN Rev[2290]
- ADDED:    database endpoint, added model GXV3175 in the table
  model. SVN Rev[2287]
- ADDED:    module endpoint_configurator, added model GXV3175.
  SVN REV[2286]
- ADDED:    database control_panel_design.db, added a new area,
  parking lots, and added a new column for the color of each
  area. SVN Rev[2257]
- CHANGED:  module control_panel, new area for parking lots the
  boxes are generated in the client side and the time counting
  for the calls are made also in the client side. SVN Rev[2256]
- ADD:      database control_panel_design, added new data in
  the tabla area for the conferences and the SIP/IAX Trunks.
  SVN Rev[2237]

* Thu Jan 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED: In spect file was added script to add permissions
  to "Operador" Group on "Control Panel" Module

* Wed Jan 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- UPDATED: Module VoIP Provider, Update codecs of Vozelia
  provider. SVN Rev[2220]
- ADDED: database endpoint, added the model AT 620R in the
  table model. SVN Rev[2219]
- ADDED: module endpoint_configuration, added a new model of
  phone for the vendor ATCOM. SVN Rev[2218]

* Wed Jan 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- NEW: Module VoIP Provider, New provider Vozelia. SVN Rev[2215]
- FIXED: PBX: Hangup macro now tests if MixMon filename actually
  exists, and clears CDR(userfield) if file is missing (as is
  the case for NOANSWER call status). Fixes Elastix bug #422.
  SVN Rev[2209]
- CHANGED: PBX: add comments to extension macros for readability
  SVN Rev[2209]
- FIXED: Monitoring: Do NOT delete CDR from database when
  deleting audio file. Instead, update CDR to have audio:deleted
  as its audio file. Also update index.php to cope with this
  change. SVN Rev[2206]
- CHANGED: Monitoring: do not complain if recording does not
  exist when deleting it. SVN Rev[2205]
- FIXED: Monitoring: do not reset filter with bogus values at
  recording removal time. This allows user to realize that
  recording has indeed been removed when displaying date ranges
  other than current day. SVN Rev[2204]

* Mon Jan 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- UPDATED: Module VoIP Provider, Provider Net2phone codecs
  updated attributes. SVN Rev[2201]

* Thu Dec 30 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- FIXED: Module Monitoring, Fix bug with record of audio files
  in a conference. SVN Rev[2200]
- CHANGED: module endpoint_configuration, new parameter for the
  phone GXV3140. SVN Rev[2197]

* Thu Dec 30 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED: module endpoint_configurator, new parameters for the
  configuration of the phones grandstream and renamed the names
  of the files with the configuration. SVN Rev[2187]
- CHANGED: database endpoint, four new models were inserted.
  SVN Rev[2186]
- CHANGED: Module VoIP Provider, change ip 208.74.169.86, for
  gateway.circuitid.com of provider CircuitID. SVN Rev[2180]

* Tue Dec 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
-  NEW: New file macros elastix, old files macro hangup and
   macro record was remove as sources of RPM and put in tar file
   of PBX. SVN Rev[2167]

* Mon Dec 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGED: In Spec file add new prereq elastix-my_extension to
  remove the old instance of myextension of elastix-pbx
- FIXED: In Database Voip Provider appear a warning after to
  install, this warning appear in the moment to read the old
  database to replace during a update. SVN Rev[2159]

* Mon Dec 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED: Add tftpboot, openfire, ftp and vsftp in spec file as
  part of process install and post. This configurations were in
  elastix.spec
- NEW:     Module VoIP Provider, new provider CircuitID was added.
  SVN Rev[2120]
- DELETED:  Module myextension of PBX wax remove and moved to
  new main menu called My Extension. SVN Rev[2113]
- NEW:     New files of vsftpd, xinetd.d folders and
  vsftpd.user_list file in setup/etc in modules/trunk/pbx/, now
  the spec of elastix.pbx use and required these services
  SVN Rev[2109]
- NEW:     Tftpboot in setup of pbx was added from trunk, it is
  for get a better organization. SVN Rev[2106]
- CHANGED: Module endpoint configurator, DTMF in phones atcom,
  are configurated to send on rfc2833. SVN Rev[2093]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-42
- CHANGED: Add new Prereq freePBX in spec file
- FIXED:  Quit menu=module_name as parameters to send for ajax to
  identify the menu to get data. This change was done in javascript
  in voipProvider module. SVN Rev[2042]
- CHANGED:    Module monitoring, to export data from reports the
  output contain text html if the report to export has any styles
  or html elements as part of grid. The solution was changing the
  data to export only if the request is export so, the data(array)
  can be returned without html elements only the data from
  database, it is in paloSantoGrid.class.php in commit 2024.
  SVN Rev[2034]
- CHANGED:    Module VOIP Provider was changed and new functionality
  were done, for example the creation of new account and custom
  accounts. SVN Rev[2025]
- FIXED: Module monitoring, variable $file no found in commit 2011.
  SVN Rev[2016]
- CHANGED: massive search and replace of HTML encodings with the
  actual characters. SVN Rev[2002]
- FIXED:   Conference: detect Asterisk version on the fly to
  decide whether to use a pipe or a comma to separate arguments
  for an Asterisk application. Fixes Elastix bug #578. SVN Rev[1998]
- FIXED:   Conference: properly escape HTML characteres to prevent
  XSS in grid display of conferences. SVN Rev[1992]
- CHANGED: stop assigning template variable "url" directly, and
  remove nested <form> tag. The modules with those changes are:
  Conference SVN Rev[1992], Voicemail SVN Rev[1990],
  Endpoint Configurator SVN Rev[1984]
- FIXED: Voicemail: emit proper 404 HTTP header when denying
  access to a recording. SVN Rev[1990]
- CHANGED: Voicemail: synchronize index.php between Elastix 1.6
  and Elastix 2. SVN Rev[1987]
- FIXED: File Editor: complete rewrite. This rewrite achieves
  the following:
         Add proper license header to module file
         Improve readability of code by splitting file listing
           and file editing into separate procedures
         Remove opportunities for XSS in file list navigation
           (ongoing fix for Elastix bug #572)
         Remove opportunities for XSS in file content viewing.
         Remove possible opportunity for arbitrary command
           execution due to nonvalidated exec()
         Fix unintended introduction of DOS line separators when
           saving files.
         Remove nested <form> tags as grid library already
           introduces them.
  SVN Rev[1983]

* Fri Nov 26 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-41
- FIXED:  Monitoring module, the problem was that the recordings
  of the queues "the audio file" if it was created but not saved
  the information in the database. For the solution
  extensions_override_freepbx.conf file was modified to add the
  information stored in database at the time of the hangup, and
  the respective changes in Monitoring Module. SVN Rev[2011]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-40
- FIXED:  Fixed bug where use $oForm->fetchForm in the function
  load_extension in extension batch and never was used. SVN Rev[1953]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-39
- FIXED:change style in the tittle of the module my extension.
  SVN Rev[1946]
- FIXED: make module aware of url-as-array in paloSantoGrid.
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template fetch. They are
      not required, since the template already includes a proper <form>
      tag enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902
      in order to work properly.
  SVN Rev[1915]
- FIXED: Problem with changing the page, when searching and want to move
  from page to page the search pattern is lost, also did not show the
  correct amount of the results, related bug [# 564] of bugs.elastix.org.
  Also had the problem that in the link of the page showing all the names
  of the files as parameters of GET request. The solution was to change
  the way to build the url. Also the way to change the filter to obtain
  data for both GET and POST. SVN Rev[1904]

* Fri Nov 05 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-38
- ADDED: Create folder version_sql in update process. SVN Rev[1896]
- FIXED: day night modes cannot be edited in Elastix embedded
  freePBX, [#576] www.bugs.elastix.org. SVN Rev[1893]
- CHANGED: Routine maintenance, changed the name of the file and
  remove lines that do nothing to create folders that were not used.
  SVN Rev[1891]

* Sat Oct 30 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-37
- FIXED:  Add macros in /etc/asterisk/extensions_override_freepbx.conf
  but asterisk never is reloaded. Changes in SPEC

* Fri Oct 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-36
- FIXED:  File migartionFileMontor.php was not work fine.
  Some monitoring audio files were not written SVN Rev[1877]
- FIXED:  Fixed bug where users cannot listen the audios in
  monitoring. [#563].SVN Rev[1875]

* Thu Oct 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-35
- FIXED:  Change move migrationFilesMonitor.php into folder installer
  /usr/share/elastix/"moduleInstall"/setup/

* Thu Oct 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-34
- CHANGED: Add headers of information in migrationFilesMonitor.php.
  SVN Rev[1868]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-33
- CHANGED: Spec file was change. New file migrationFilesMonitor.php, it
  was removed from elastix.spec and now it part of the source of
  elastix-pbx. SVN Rev[1866]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-32
- CHANGED: Updated the Bulgarian language elastix. SVN Rev[1857]
- FIXED:  Batch Of Extensions Problems with Outbound CID and Inbound DID,
  they don't appear this fields in csv files to download.
  More details in http://bugs.elastix.org/view.php?id=447. SVN Rev[1853]

* Tue Oct 26 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-31
- CHANGED: The Spec file valid if version and release are lower to
  1.6.2-13 for doing migration of monitoring audio files. It is only for
  migration Elastix 1.6 to 2.0

* Tue Oct 26 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-30
- CHANGED: Move line elastix-menumerge at beginning the "%post" in spec file.
  It is for the process to update.

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-29
- FIXED:   Fixed security bug with audio.php and popup.php where an user can be download
  files system without authentication. See in http://bugs.elastix.org/view.php?id=552
  SVN Rev[1833]
- CHNAGED: Language fr.lang was updated. SVN Rev[1825]
- ADDED:   New lang file fa.lang. SVN Rev[1823]
- FIXED:   It validates that the index of the callerid exist, if it don't
  exits the callerid is left. This fixes a problem that did not display the number of
  participants at the conference when it is an outside call.
  Bug [#491]. Bug [#491] SVN Rev[1814]

* Mon Sep 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-28
- FIXED:    include character '/' in function isDialpatternChar where character / (match cid) not valid for dial pattern in outbound routes. SVN Rev[1754], Bug[#485]

* Tue Sep 14 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-27
- CHANGED: rework translation support so that it will work with untranslated English strings for new menu items. Rev[1734]
- FIXED:   add several new menu items for FreePBX menus, to make them appear on the embedded interface. Should fix Elastix bug #458. Rev[1734]
- FIXED:   Valid fields with only spaces blank. Rev[1740]
- FIXED:   actually implement paging correctly on discovered endpoint list. Should fix Elastix bug #411. Rev[1732]
- FIXED:   preserve list of discovered endpoints across page refreshes until next reload. Rev[1732]
- CHANGED: enforce sorting by IP on list of discovered endpoints. Rev[1732]

* Mon Aug 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-26
- REMOVE: Remove extensions_override_freepbx.conf in Sources for many macros as macro-record-enable and macro-hangupcall.

* Mon Aug 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-25
- FIXED: Fixed bug[#409] and change the Source extensions_override_freepbx.conf.

* Fri Aug 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-24
- FIXED: Fix incorrect wording for text string. Part of fix for bug #421. Rev[1724]
- FIXED: Merge translations strings from local language with English. This allows module to display English text if localized text is unavailable, instead of showing blanks. Rev[1721]
- FIXED: do not use uninitialized array indexes when logged-on user has no extension. Rev[1718]

* Thu Aug 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-23
- NEW:     New module My Extension in PBX, It configure the user's extension from elastix web interface. Rev[1694]

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-22
- CHANGED: Change help files in  Operator Panel, Endpoint Configurator, VoIP Provider.
           Change the module name to Operator Panel
- CHANGED: Task [#243] extension batch. Now if no extension availables the file downloaded show all columns about the information that it must have...

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-21
- FIXED: Script is not authenticated session, and anyone from the internet can be invoked with arbitrary parameters.
-        Expose connection data of known IP providers

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-20
- NEW: Implementation to support install database in fresh install.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-19
- CHANGED: database module conference (meetme db) was removed in process index.php instalation in web interface. Now the install db is with to help elastix-dbprocess.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-18
- DELETED: Source module realtime, this module is devel yet.
- CHANGED: String connection database to asteriskuser in module monitoring.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-17
- CHANGED: Name module to Operator Panel.
- CHANGED: String connection database to asteriskuser.

* Thu Jul 01 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- FIXED:    Add line 'global $recordings_save_path' in pbxadmin module to obtain the path where upload audio files in recording [#346] bugs.elastix.org

* Thu Jun 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-15
- Change module extensions batch. Now the option download cvs is processing by index.php

* Thu Apr 15 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-14
- Change port 5061 to 5060 in file config vendor Cisco.cfg.php module endpoint configurator.
- Fixed bug module extension batch, wasn't validating the file csv. Error code in compration boolean expresion.
- Fixed bug in module monitoring not had been changed  the new code.
- Be improve the look in module voip provider.


* Thu Mar 25 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-13
- Re-write macro-record-enable for freePBX, this action is for module monitorin support new programming in based database asteriskcdrdb.
- Module Monitoring was rewrited, improved behavoir in search audio files.

* Fri Mar 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-12
- Defined Lang missed.

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-11
- Defined number order menu.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- Fixed minor bug in EOP.

* Wed Dec 30 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Fixed bug module Voip Provider, change name voip-provider-cust to voip-provider.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- Improved module control panel support multi columns.
- Fixed bug, boxes of extension into other them.
- Improved module VOIP Provider performance.

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Improved the modulo voip provider, validation and look.

* Mon Oct 19 2009 Alex Villacis <bmacias@palosanto.com> 2.0.0-5
- Inplemetation for support web conferences in module cenferences elastix.

* Fri Sep 18 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- New module VOIP PROVIDER
- Fixed minor bugs in definition words languages and messages.
- Add accion uninstall rpm.

* Fri Sep 18 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add words in module coference.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.
