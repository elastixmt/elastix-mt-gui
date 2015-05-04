DELETE FROM activated_applet_by_user WHERE id IN 
(   SELECT activated_applet_by_user.id 
    FROM activated_applet_by_user, default_applet_by_user, applet 
    WHERE activated_applet_by_user.id_dabu = default_applet_by_user.id 
        AND default_applet_by_user.id_applet = applet.id 
        AND applet.code in (
        'Applet_Calendar', 'Applet_Calls', 'Applet_Emails', 'Applet_Faxes', 'Applet_System', 'Applet_Voicemails'
        )
);
DELETE FROM default_applet_by_user WHERE id IN 
(   SELECT default_applet_by_user.id
    FROM default_applet_by_user, applet
    WHERE default_applet_by_user.id_applet = applet.id 
        AND applet.code in (
        'Applet_Calendar', 'Applet_Calls', 'Applet_Emails', 'Applet_Faxes', 'Applet_System', 'Applet_Voicemails'
        )
);
DELETE FROM applet WHERE code in (
    'Applet_Calendar', 'Applet_Calls', 'Applet_Emails', 'Applet_Faxes', 'Applet_System', 'Applet_Voicemails'
);
