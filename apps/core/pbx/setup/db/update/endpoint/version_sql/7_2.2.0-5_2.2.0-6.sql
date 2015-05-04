BEGIN TRANSACTION;

CREATE TABLE settings_by_country
(
	id	    	  integer	  primary key,
	country     	  varchar(50)     not null default '',
	fxo_fxs_profile   varchar(50)	  not null default '',
	tone_set          varchar(500)    not null default ''
);

INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Argentina','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -13 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 300 425 -7 
pause 2 200 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 300 425 -7 
pause 2 400 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 300 425 -7 
pause 2 400');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Australia','au','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 450 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 400 425 -13 450 -13 
pause 2 200 
play 1 400 425 -13 450 -13 
pause 2 2000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 375 425 -7 
pause 2 375 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 375 425 -7 
pause 2 375 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 375 425 -7 
pause 2 375');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Austria 420Hz','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 420 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 420 -13 
pause 2 5000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 400 420 -7 
pause 2 400 profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 200 420 -7 
pause 2 200 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 200 420 -7 
pause 2 200');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Austria 450Hz','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 450 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 450 -13 
pause 2 5000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 300 450 -7 
pause 2 300 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 300 450 -7 
pause 2 300 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 300 450 -7 
pause 2 300');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Belgium','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 0 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 3000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 170 425 -7 
pause 2 170 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 170 425 -7 
pause 2 170');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Brazil','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Cyprus','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 350 -6 450 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1500 425 -13 
pause 2 3000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Czech Republic','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 330 425 -6 
pause 2 330 
play 3 660 425 -6 
pause 4 660 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -13 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 330 425 -7 
pause 2 330 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 165 425 -7 
pause 2 165 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 165 425 -7 
pause 2 165');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Denmark','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Finland','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 300 425 -7 
pause 2 300 profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 200 425 -7 
pause 2 200 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 200 425 -7 
pause 2 200');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('France','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 440 0 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1500 440 -7 
pause 2 3500 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 440 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Germany','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 0 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 4000 profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 480 425 -7 
pause 2 480 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 240 425 -7 
pause 2 240 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 240 425 -7 
pause 2 240');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Greece','etsi','profile call-progress-tone defaultDialtone 
play 1 200 425 0 
pause 2 300 
play 3 700 425 0 
pause 4 800 
profile call-progress-tone defaultBusytone 
play 1 300 425 -7 
pause 2 300 
profile call-progress-tone defaultReleasetone 
play 1 150 425 -7 
pause 2 150 
profile call-progress-tone defaultCongestiontone 
play 1 150 425 -7 
pause 2 150');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Holland','nl','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 0 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('India','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 400 400 -13 
pause 2 200 
play 3 400 400 -13 
pause 4 2000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 750 400 -7 
pause 2 750 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 400 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 400 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Ireland','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 400 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 400 400 -6 
pause 2 200 
play 3 400 400 -6 
pause 4 2000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -6 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 500 425 -6 
pause 2 500 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 500 425 -6 
pause 2 500');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Italy','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 200 425 0 
pause 2 200 
play 1 600 425 0 
pause 1 1000 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 200 425 -7 
pause 2 200 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Japan','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 400 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 400 -13 
pause 2 2000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 400 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 500 400 -7 
pause 2 500 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 500 400 -7 
pause 2 500');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('New Zeland','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 5000 400 -15 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
pause 2 200 
play 3 400 400 -15 450 -15 
pause 4 2000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 400 -15 
pause 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 500 400 -15 
pause 500 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 500 400 -15 
pause 500 
profile call-progress-tone defaultSDTone 
flush-play-list 
play 1 10000 425 -19 620 -19 
profile call-progress-tone defaultWaitingtone 
flush-play-list 
play 1 200 400 -15 
pause 200');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Norway','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -13 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 200 425 -7 
pause 2 200 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 200 425 -7 
pause 2 200');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Poland','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -13 
pause 2 4000 profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 500 425 -7 
pause 2 500');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Portugal','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -13 
pause 2 5000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 200 425 -7 
pause 2 200 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 200 425 -7 
pause 2 200');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Russia','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 800 425 -13 
pause 2 3200 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 400 425 -7 
pause 2 400 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 400 425 -7 
pause 2 400 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 400 425 -7 
pause 2 400');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('South Africa','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 400 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 400 400 -13 
pause 2 200 
play 3 400 400 -13 
pause 4 2000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 400 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 400 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 400 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Spain','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1500 425 -13 
pause 2 3000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 200 425 -7 
pause 2 200 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 200 425 -7 
pause 2 200 
play 3 200 425 -7 
pause 4 200 
play 5 200 425 -7 
pause 6 600 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 200 425 -7 
pause 2 200 
play 3 200 425 -7 
pause 4 200 
play 5 200 425 -7 
pause 6 600');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Sweden','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -13 
pause 2 5000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 750 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 750');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Switzerland','ch','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 425 0 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 425 -7 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 425 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 425 -7 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 425 -7 
pause 2 250');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('Turkey','etsi','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 450 -6 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 2000 450 -13 
pause 2 4000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 450 -7 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 200 450 -7 
pause 2 200 
play 3 200 450 -7 
pause 4 200 
play 5 200 450 -7 
pause 6 200 
play 7 600 450 -7 
pause 8 200 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 200 450 -7 
pause 2 200 
play 3 200 450 -7 
pause 4 200 
play 5 200 450 -7 
pause 6 200 
play 7 600 450 -7 
pause 8 200');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('United Kingdom','gb','profile call-progress-tone defaultDialtone 
flush-play-list 
play 1 1000 350 -13 440 -13 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 400 400 -19 450 -19 
pause 2 200 
play 3 400 400 -19 450 -19 
pause 4 2000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 375 400 -24 
pause 2 375 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 400 400 -24 
pause 2 350 
play 3 225 400 -24 
pause 4 525 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 400 400 -24 
pause 2 350 
play 3 225 400 -24 
pause 4 525');
INSERT INTO settings_by_country(country,fxo_fxs_profile,tone_set) VALUES ('United States','us','profile call-progress-tone defaultDialtone
flush-play-list 
play 1 1000 350 -13 440 -13 
profile call-progress-tone defaultAlertingtone 
flush-play-list 
play 1 1000 440 -19 480 -19 
pause 2 3000 
profile call-progress-tone defaultBusytone 
flush-play-list 
play 1 500 480 -24 620 -24 
pause 2 500 
profile call-progress-tone defaultReleasetone 
flush-play-list 
play 1 250 480 -24 620 -24 
pause 2 250 
profile call-progress-tone defaultCongestiontone 
flush-play-list 
play 1 250 480 -24 620 -24 
pause 2 250');

INSERT INTO vendor (name,description) VALUES ('Patton','Patton');
INSERT INTO model (name,description,id_vendor,iax_support) VALUES ('Patton','Patton',(SELECT id FROM vendor WHERE name='Patton'),'0');

COMMIT;
