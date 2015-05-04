UPDATE org_email_template
    SET content =
'Your entity {COMPANY_NAME}, associated with the domain {DOMAIN} has been created.
To configure you Elastix server, please go to https://{HOST_IP} and login into Elastix with the following credentials:
Username: admin@{DOMAIN}
Password: {USER_PASSWORD}'
WHERE category = "create"
    AND content = 'Welcome to Elastix Server.<br>Your company {COMPANY_NAME} with domain {DOMAIN} has been created.<br>To start to configurate you elastix server go to {HOST_IP} and login into elastix as:<br>Username: admin@{DOMAIN}<br>Password: {USER_PASSWORD}';

-- Grant access to kamailio database
GRANT SELECT, UPDATE, INSERT, DELETE ON `kamailio`.* to asteriskuser@localhost;