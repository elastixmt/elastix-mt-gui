<?php
//This file is part of FreePBX.
//
//    FreePBX is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    FreePBX is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//
//    Copyright (C) 2004 Coalescent Systems Inc. (info@coalescentsystems.ca)
//
$elxPath="/usr/share/elastix";
include_once "$elxPath/libs/misc.lib.php";

class extension {
	var $data;
	var $error;

	function extension($data = '') {
		$this->data = $data;
	}

	function incrementContents($value) {
		return true;
	}

	function output() {
		return $this->data;
	}

	function isEmpty($var){
		if(is_null($var) || $var==="" || $var===false){
			return true;
		}
	}
}

class ext_gosub extends extension {
	var $pri;
	var $ext;
	var $context;
	var $params;

	function ext_gosub($pri, $ext = false, $context = false, $parameters = false) {
		if ($context !== false && $ext === false) {
			$this->error="\$ext is required when passing \$context in ext_gosub::ext_gosub()";
		}

		$this->pri = $pri;
		$this->ext = $ext;
		$this->context = $context;
		$this->params = $parameters;
	}

	function incrementContents($value) {
		$this->pri += $value;
	}

	function output() {
		return 'Gosub('.($this->context ? $this->context.',' : '').($this->ext ? $this->ext.',' : '').$this->pri.($this->params ?'('.$this->params.')':'').')' ;
	}
}

class ext_return extends extension {
    var $return_value;
    
    function ext_return($return=""){
        if(isset($return))
            $this->return_value = $return;
    }
    
	function output() {
		return "Return(".$this->return_value.")";
	}
}

class ext_gosubif extends extension {
	var $true_priority;
	var $false_priority;
	var $condition;
	function ext_gosubif($condition, $true_priority, $false_priority = false) {
		$this->true_priority = $true_priority;
		$this->false_priority = $false_priority;
		$this->condition = $condition;
	}
	function output() {
		return 'GosubIf(' .$this->condition. '?' .$this->true_priority.($this->false_priority ? ':' .$this->false_priority : '' ). ')' ;
	}
	function incrementContents($value) {
		$this->true_priority += $value;
		$this->false_priority += $value;
	}
}

class ext_goto extends extension {
	var $pri;
	var $ext;
	var $context;

	function ext_goto($pri, $ext = false, $context = false) {
		if ($context !== false && $ext === false) {
			$this->error="\$ext is required when passing \$context in ext_goto::ext_goto()";
		}

		$this->pri = $pri;
		$this->ext = $ext;
		$this->context = $context;
	}

	function incrementContents($value) {
		$this->pri += $value;
	}

	function output() {
		return 'Goto('.(!$this->isEmpty($this->context) ? $this->context.',' : '').(!$this->isEmpty($this->ext) ? $this->ext.',' : '').$this->pri.')' ;
	}
}

class ext_gotoif extends extension {
	var $true_priority;
	var $false_priority;
	var $condition;
	function ext_gotoif($condition, $true_priority, $false_priority = false) {
		$this->true_priority = $true_priority;
		$this->false_priority = $false_priority;
		$this->condition = $condition;
	}
	function output() {
		return 'GotoIf(' .$this->condition. '?' .$this->true_priority.($this->false_priority ? ':' .$this->false_priority : '' ). ')' ;
	}
	function incrementContents($value) {
		$this->true_priority += $value;
		$this->false_priority += $value;
	}
}

class ext_gotoiftime extends extension {
	var $true_priority;
	var $condition;
	function ext_gotoiftime($condition, $true_priority) {
		$this->condition = str_replace("|", ",", $condition);
		$this->true_priority = $true_priority;
	}
	function output() {
		return 'GotoIfTime(' .$this->condition. '?' .$this->true_priority. ')' ;
	}
	function incrementContents($value) {
		$this->true_priority += $value;
	}
}

class ext_noop extends extension {
	function output() {
		return "Noop(".$this->data.")";
	}
}

class ext_dial extends extension {
	var $number;
	var $options;

	function ext_dial($number, $options = "tr") {
		$this->number = $number;
		$this->options = $options;
	}

	function output() {
		return "Dial(".$this->number.",".$this->options.")";
	}
}

class ext_setvar {
	var $var;
	var $value;

	function ext_setvar($var, $value) {
		$this->var = $var;
		$this->value = $value;
	}

	function output() {
		return "Set(".$this->var."=".$this->value.")";
	}
}
class ext_set extends ext_setvar {} // alias, SetVar was renamed to Set in ast 1.2

class ext_setglobalvar {
	var $var;
	var $value;

	function ext_setglobalvar($var, $value) {
		$this->var = $var;
		$this->value = $value;
	}

	function output() {
		return "Set(".$this->var."=".$this->value.",g)";
	}
}

class ext_sipaddheader {
	var $header;
	var $value;

	function ext_sipaddheader($header, $value) {
		$this->header = $header;
		$this->value = $value;
	}

	function output() {
		return "SIPAddHeader(".$this->header.": ".$this->value.")";
	}
}

class ext_sipgetheader {
	var $header;
	var $value;

	function ext_sipgetheader($value, $header) {
		$this->value = $value;
		$this->header = $header;
	}

	function output() {
		return "SIPGetHeader(".$this->value."=".$this->header.")";
	}
}

class ext_alertinfo {
	var $value;

	function ext_alertinfo($value) {
		$this->value = $value;
	}

	function output() {
		return "SIPAddHeader(Alert-Info: ".$this->value.")";
	}
}

class ext_wait extends extension {
	function output() {
		return "Wait(".$this->data.")";
	}
}

class ext_parkedcall extends extension {
	function output() {
		return "ParkedCall(".$this->data.")";
	}
}


class ext_resetcdr extends extension {
	function output() {
		return "ResetCDR(".$this->data.")";
	}
}

class ext_nocdr extends extension {
	function output() {
		return "NoCDR()";
	}
}

class ext_forkcdr extends extension {
	function output() {
		return "ForkCDR()";
	}
}

class ext_waitexten extends extension {
	var $seconds;
	var $options;

	function ext_waitexten($seconds = "", $options = "") {
		$this->seconds = $seconds;
		$this->options = $options;
	}

	function output() {
        if($this->options="")
            return "WaitExten(".$this->seconds.",".$this->options.")";
        else
            return "WaitExten(".$this->seconds.")";
	}
}

class ext_answer extends extension {
	function output() {
		return "Answer";
	}
}

class ext_privacymanager extends extension {
	function output() {
		return "PrivacyManager(".$this->data.")";
	}
}

class ext_macro {
	var $macro;
	var $args;

	function ext_macro($macro, $args='') {
		$this->macro = $macro;
		$this->args = $args;
	}

	function output() {
		return "Macro(".$this->macro.",".$this->args.")";
	}
}

//      The app_false argument only works with asterisk 1.6
//
class ext_execif{
	var $expr;
	var $app_true;
	var $data_true;
	var $app_false;
	var $data_false;

	function ext_execif($expr, $app_true, $data_true='', $app_false = '', $data_false = '') {
		$this->expr = $expr;
		$this->app_true = $app_true;
		$this->data_true = $data_true;
		$this->app_false = $app_false;
		$this->data_false = $data_false;
	}

	function output() {
		if ($this->app_false != '')
			return "ExecIf({$this->expr}?{$this->app_true}({$this->data_true}):{$this->app_false}({$this->data_false}))";
		else
			return "ExecIf({$this->expr}?{$this->app_true}({$this->data_true}))";
	}
}

class ext_setcidname extends extension {
	function output() {
		return "Set(CALLERID(name)=".$this->data.")";
	}
}

class ext_setcallerpres extends extension {
	function output() {
		return "Set(CALLERPRES()={$this->data})";
	}
}

class ext_record extends extension {
	function output() {
		return "Record(".$this->data.")";
	}
}

class ext_playback extends extension {
	function output() {
		return "Playback(".$this->data.")";
	}
}

class ext_queue {
	var $var;
	var $value;

	function ext_queue($queuename, $options, $optionalurl, $announceoverride, $timeout) {
		$this->queuename = $queuename;
		$this->options = $options;
		$this->optionalurl = $optionalurl;
		$this->announceoverride = $announceoverride;
		$this->timeout = $timeout;
	}

	function output() {
		// for some reason the Queue cmd takes an empty last param (timeout) as being 0
		// when really we want unlimited
		if ($this->timeout != "")
			return "Queue(".$this->queuename.",".$this->options.",".$this->optionalurl.",".$this->announceoverride.",".$this->timeout.")";
		else
			return "Queue(".$this->queuename.",".$this->options.",".$this->optionalurl.",".$this->announceoverride.")";
	}
}

class ext_addqueuemember extends extension {
	var $queue;
	var $channel;

	function ext_addqueuemember($queue, $channel){
		$this->queue = $queue;
		$this->channel = $channel;
	}

	function output() {
		return "AddQueueMember({$this->queue},{$this->channel})";
	}
}

class ext_removequeuemember extends extension {
	var $queue;
	var $channel;

	function ext_removequeuemember($queue, $channel){
		$this->queue = $queue;
		$this->channel = $channel;
	}

	function output() {
		return "RemoveQueueMember({$this->queue},{$this->channel})";
	}
}

class ext_userevent extends extension {
	var $eventname;
	var $body;

	function ext_userevent($eventname, $body=""){
		$this->eventname = $eventname;
		$this->body = $body;
	}

	function output() {
		if ($this->body == '')
			return "UserEvent({$this->eventname})";
		else
			return "UserEvent({$this->eventname},{$this->body})";
	}
}

class ext_macroexit extends extension {
	function output() {
		return "MacroExit()";
	}
}

class ext_hangup extends extension {
	function output() {
		return "Hangup";
	}
}

class ext_digittimeout extends extension {
	function output() {
		return "Set(TIMEOUT(digit)=".$this->data.")";
	}
}

class ext_responsetimeout extends extension {
	function output() {
		return "Set(TIMEOUT(response)=".$this->data.")";
	}
}

class ext_background extends extension {
	function output() {
		return "Background(".$this->data.")";
	}
}

class ext_read {
	var $astvar;
	var $filename;
	var $maxdigits;
	var $option;
	var $attempts; // added in ast 1.2
	var $timeout;  // added in ast 1.2

	function ext_read($astvar, $filename='', $maxdigits='', $option='', $attempts ='', $timeout ='') {
		$this->astvar = $astvar;
		$this->filename = $filename;
		$this->maxdigits = $maxdigits;
		$this->option = $option;
		$this->attempts = $attempts;
		$this->timeout = $timeout;
	}

	function output() {
		return "Read(".$this->astvar.",".$this->filename.",".$this->maxdigits.",".$this->option.",".$this->attempts.",".$this->timeout.")";
	}
}

class ext_confbridge {
	var $confno;
	var $options;
	var $pin;

	function ext_confbridge($confno, $options='', $pin='') {
		$this->confno = $confno;
		$this->options = $options;
		$this->pin = $pin;
	}

	function output() {
		return "ConfBridge(".$this->confno.",".$this->options.",".$this->pin.")";
	}
}

class ext_meetmeadmin {
	var $confno;
	var $command;
	var $user;

	function ext_meetmeadmin($confno, $command, $user='') {
		$this->confno = $confno;
		$this->command = $command;
		$this->user = $user;
	}

	function output() {
		return "MeetMeAdmin(".$this->confno.",".$this->command.",".$this->user.")";
	}
}

class ext_meetme {
	var $confno;
	var $options;
	var $pin;

	function ext_meetme($confno, $options='', $pin='') {
		$this->confno = $confno;
		$this->options = $options;
		$this->pin = $pin;
	}

	function output() {
        $this->options=(empty($this->options))?"":",".$this->options;
		$this->options=(empty($this->pin))?"":",".$this->pin;
		return "MeetMe(".$this->confno."".$this->options."".$this->pin.")";
	}
}

class ext_authenticate {
	var $pass;
	var $options;

	function ext_authenticate($pass, $options='') {
		$this->pass = $pass;
		$this->options = $options;
	}
	function output() {
		return "Authenticate(".$this->pass.",".$this->options.")";
	}
}

class ext_vmauthenticate {
	var $mailbox;
	var $options;

	function ext_vmauthenticate($mailbox='', $options='') {
		$this->mailbox = $mailbox;
		$this->options = $options;
	}
	function output() {
		return "VMAuthenticate(" .$this->mailbox . (($this->options != '') ? ','.$this->options : '' ) .")";
	}
}

class ext_page extends extension {
	function output() {
		return "Page(".$this->data.")";
	}
}

class ext_disa extends extension {
	function output() {
		return "DISA(".$this->data.")";
	}
}
class ext_agi extends extension {
	function output() {
		return "AGI(".$this->data.")";
	}
}
class ext_deadagi extends extension {
	function output() {
		return "DeadAGI(".$this->data.")";
	}
}
class ext_dbdel extends extension {
        function output() {
			return 'Noop(Deleting: '.$this->data.' ${DB_DELETE('.$this->data.')})';
        }
}
class ext_dbdeltree extends extension {
	function output() {
		return "dbDeltree(".$this->data.")";
	}
}
class ext_dbget extends extension {
	var $varname;
	var $key;
	function ext_dbget($varname, $key) {
		$this->varname = $varname;
		$this->key = $key;
	}
	function output() {
		return 'Set('.$this->varname.'=${DB('.$this->key.')})';
	}
}
class ext_dbput extends extension {
	var $key;
	function ext_dbput($key, $data) {
		$this->key = $key;
		$this->data = $data;
	}
	function output() {
		return 'Set(DB('.$this->key.')='.$this->data.')';
	}
}
class ext_vmmain extends extension {
	function output() {
		return "VoiceMailMain(".$this->data.")";
	}
}
class ext_vm extends extension {
	function output() {
		return "VoiceMail(".$this->data.")";
	}
}

class ext_vmexists extends extension {
	function output() {
		  return "MailBoxExists(".$this->data.")";
    }
}

class ext_saydigits extends extension {
	function output() {
		return "SayDigits(".$this->data.")";
	}
}
class ext_sayunixtime extends extension {
	function output() {
			// SayUnixTime in 1.6 and greater does NOT require slashes. If they're
			// supplied, strip them out.
			$fixed = str_replace("\\", "", $this->data);
			return "SayUnixTime($fixed)";
	}
}
class ext_echo extends extension {
	function output() {
		return "Echo(".$this->data.")";
	}
}
// Thanks to agillis for the suggestion of the nvfaxdetect option
class ext_nvfaxdetect extends extension {
	function output() {
		// change from '|' to ','
		$astdelimeter = str_replace("|", ",", $this->data);
		return "NVFaxDetect($astdelimeter)";
	}
}
class ext_receivefax extends extension {
	function output() {
		return "ReceiveFAX(".$this->data.")";
	}
}
class ext_rxfax extends extension {
	function output() {
		return "rxfax(".$this->data.")";
	}
}
class ext_sendfax extends extension {
	function output() {
		return "SendFAX(".$this->data.")";
	}
}
class ext_playtones extends extension {
	function output() {
		return "Playtones(".$this->data.")";
	}
}
class ext_stopplaytones extends extension {
	function output() {
		return "StopPlaytones";
	}
}
class ext_zapbarge extends extension {
	function output() {
		global $chan_dahdi;

		if ($chan_dahdi) {
			$command = 'DAHDIBarge';
		} else {
			$command = 'ZapBarge';
		}

		return "$command(".$this->data.")";
	}
}
class ext_sayalpha extends extension {
	function output() {
		return "SayAlpha(".$this->data.")";
	}
}
class ext_saynumber extends extension {
	var $gender;
	function ext_saynumber($data, $gender = 'f') {
		parent::extension($data);
		$this->gender = $gender;
	}
	function output() {
		return "SayNumber(".$this->data.",".$this->gender.")";
	}
}
class ext_sayphonetic extends extension {
	function output() {
		return "SayPhonetic(".$this->data.")";
	}
}
class ext_system extends extension {
	function output() {
		return "System(".$this->data.")";
	}
}
class ext_festival extends extension {
	function output() {
		return "Festival(".$this->data.")";
	}
}
class ext_pickup extends extension {
	function output() {
		return "Pickup(".$this->data.")";
	}
}
class ext_dpickup extends extension {
	function output() {
		return "DPickup(".$this->data.")";
	}
}
class ext_lookupcidname extends extension {
	function output() {
		return 'ExecIf($["${DB(cidname/${CALLERID(num)})}" != ""]?Set(CALLERID(name)=${DB(cidname/${CALLERID(num)})}))';
	}
}

class ext_txtcidname extends extension {
	var $cidnum;

	function ext_txtcidname($cidnum) {
		$this->cidnum = $cidnum;
	}

	function output() {
		return 'Set(TXTCIDNAME=${TXTCIDNAME('.$this->cidnum.')})';
	}
}

class ext_mysql_connect extends extension {
	var $connid;
	var $dbhost;
	var $dbuser;
	var $dbpass;
	var $dbname;

	function ext_mysql_connect($connid, $dbhost, $dbuser, $dbpass, $dbname) {
		$this->connid = $connid;
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbname = $dbname;
	}

	function output() {
		return "MYSQL(Connect ".$this->connid." ".$this->dbhost." ".$this->dbuser." ".$this->dbpass." ".$this->dbname.")";
	}
}

class ext_mysql_query extends extension {
	var $resultid;
	var $connid;
	var $query;

	function ext_mysql_query($resultid, $connid, $query) {
		$this->resultid = $resultid;
		$this->connid = $connid;
		$this->query = $query;
		// Not escaping mysql query here, you may want to insert asterisk variables in it
	}

	function output() {
		return 'MYSQL(Query '.$this->resultid.' ${'.$this->connid.'} '.$this->query.')';
	}
}

class ext_mysql_fetch extends extension {
	var $fetchid;
	var $resultid;
	var $fars;

	function ext_mysql_fetch($fetchid, $resultid, $vars) {
		$this->fetchid = $fetchid;
		$this->resultid = $resultid;
		$this->vars = $vars;
	}

	function output() {
		return 'MYSQL(Fetch '.$this->fetchid.' ${'.$this->resultid.'} '.$this->vars.')';
	}
}

class ext_mysql_clear extends extension {
	var $resultid;

	function ext_mysql_clear($resultid) {
		$this->resultid = $resultid;
	}

	function output() {
		return 'MYSQL(Clear ${'.$this->resultid.'})';
	}
}

class ext_mysql_disconnect extends extension {
	var $connid;

	function ext_mysql_disconnect($connid) {
		$this->connid = $connid;
	}

	function output() {
		return 'MYSQL(Disconnect ${'.$this->connid.'})';
	}
}

class ext_ringing extends extension {
	function output() {
		return "Ringing()";
	}
}

class ext_db_put extends extension {
	var $family;
	var $key;
	var $value;

	function ext_db_put($family, $key, $value) {
		$this->family = $family;
		$this->key = $key;
		$this->value = $value;
	}

	function output() {
		return 'Set(DB('.$this->family.'/'.$this->key.')='.$this->value.')';
	}
}

class ext_zapateller extends extension {
	function output() {
		return "Zapateller(".$this->data.")";
	}
}

class ext_musiconhold extends extension {
	function output() {
		return "MusicOnHold(".$this->data.")";
	}
}

class ext_setmusiconhold extends extension {
	function output() {
		return "SetMusicOnHold(".$this->data.")";
	}
}

class ext_congestion extends extension {
	var $time;

	function ext_congestion($time = '20') {
		$this->time = $time;
	}
	function output() {
		return "Congestion(".$this->time.")";
	}
}

class ext_busy extends extension {
	var $time;

	function ext_busy($time = '20') {
		$this->time = $time;
	}
	function output() {
		return "Busy(".$this->time.")";
	}
}

class ext_flite extends extension {
	function output() {
		return "Flite('".$this->data."')";
	}
}
class ext_chanspy extends extension {
	var $prefix;
	var $options;
	function ext_chanspy($prefix = '', $options = '') {
		$this->prefix = $prefix;
		$this->options = $options;
	}
	function output() {
		return "ChanSpy(".$this->prefix.($this->options?','.$this->options:'').")";
	}
}

class ext_lookupblacklist extends extension {
	function output() {
		return "LookupBlacklist(".$this->data.")";
	}
}

class ext_dictate extends extension {
	function output() {
		return "Dictate(".$this->data.")";
	}
}

class ext_chanisavail extends extension {
	var $chan;
	var $options;
	function ext_chanisavail($chan, $options = '') {
		$this->chan = $chan;
		$this->options = $options;
	}

	function output() {
		return 'ChanIsAvail('.$this->chan.','.$this->options.')';
	}
}

class ext_setlanguage extends extension {
	function output() {
		return "Set(CHANNEL(language)={$this->data})";
	}
}

class ext_mixmonitor extends extension {
	var $file;
	var $options;
	var $postcommand;

	function ext_mixmonitor($file, $options = "", $postcommand = "") {
		$this->file = $file;
		$this->options = $options;
		$this->postcommand = $postcommand;
	}

	function output() {
		return "MixMonitor(".$this->file.",".$this->options.",".$this->postcommand.")";
	}
}

class ext_stopmonitor extends extension {
	function output() {
		return "StopMonitor(".$this->data.")";
	}
}

class ext_stopmixmonitor extends extension {
	function output() {
		return "StopMixMonitor(".$this->data.")";
	}
}

// Speech recognition applications
class ext_speechcreate extends extension {
	var $engine;

	function ext_speechcreate($engine = null)  {
		$this->engine = $engine;
	}

	function output() {
		return "SpeechCreate(".($this->engine?$this->engine:"").")";
	}
}
class ext_speechloadgrammar extends extension {
	var $grammar_name;
	var $path_to_grammar;

	function ext_speechloadgrammar($grammar_name,$path_to_grammar)  {
		$this->grammar_name = $grammar_name;
		$this->path_to_grammar = $path_to_grammar;
	}

	function output() {
		return "SpeechLoadGrammar(".$this->grammar_name.",".$this->path_to_grammar.")";
	}
}
class ext_speechunloadgrammar extends extension {
	var $grammar_name;

	function ext_speechunloadgrammar($grammar_name)  {
		$this->grammar_name = $grammar_name;
	}

	function output() {
		return "SpeechUnloadGrammar(".$this->grammar_name.")";
	}
}
class ext_speechactivategrammar extends extension {
	var $grammar_name;

	function ext_speechactivategrammar($grammar_name)  {
		$this->grammar_name = $grammar_name;
	}

	function output() {
		return "SpeechActivateGrammar(".$this->grammar_name.")";
	}
}

class ext_speechstart extends extension {

	function output() {
		return "SpeechStart()";
	}
}
class ext_speechbackground extends extension {
	var $sound_file;
	var $timeout;

	function ext_speechbackground($sound_file,$timeout=null)  {
		$this->sound_file = $sound_file;
		$this->timeout = $timeout;
	}

	function output() {
		return "SpeechBackground(".$this->sound_file.($this->timeout?",$this->timeout":"").")";
	}
}
class ext_speechdeactivategrammar extends extension {
	var $grammar_name;

	function ext_speechdeactivategrammar($grammar_name)  {
		$this->grammar_name = $grammar_name;
	}

	function output() {
		return "SpeechDeactivateGrammar(".$this->grammar_name.")";
	}
}
class ext_speechprocessingsound extends extension {
	var $sound_file;

	function ext_speechprocessingsound($sound_file)  {
		$this->sound_file = $sound_file;
	}

	function output() {
		return "SpeechProcessingSound(".$this->sound_file.")";
	}
}

class ext_speechdestroy extends extension {

	function output() {
		return "SpeechDestroy()";
	}
}

// optionally call this before a ext_speechbackground and if the speech engine recognizes
// DTMF, it will stop recognizing speech after $digits digits and return the recognized
// DTMF in ${SPEECH_TEXT(0)}
class ext_speechdtmfmaxdigits  extends extension {
	var $digits;
	function ext_speechdtmfmaxdigits($digits)  {
		$this->digits = $digits;
	}

	function output()  {
		return "Set(SPEECH_DTMF_MAXLEN=".$this->digits.")";
	}
}

// optionally call this before ext_speechbackground and the speech engine will consider this
// a terminator to dtmf entry.  It should be noted that despite a lack of documentation, # is
// set by default for this behavior, so if you need to recognize # in a speech/dtmf application
// You need to set this to some other terminator.
class ext_speechdtmfterminator  extends extension {
        var $digits;
        function ext_speechdtmfterminator($terminator)  {
                $this->terminator = $terminator;
        }

        function output()  {
                return "Set(SPEECH_DTMF_TERMINATOR=".$this->terminator.")";
        }
}

class ext_progress extends extension {
	function output() {
		return "Progress";
	}
}


//hasta aqui lo implementado por freepbx
class ext_hint extends extension {
    public $exten;
    public $domain;
    public $code;

    function ext_hint($exten,$domino) {
        global $arrConf;
        if($this->isEmpty($exten) || $this->isEmpty($domino)){
            $this->error="Number extension and domain can't be empty";
        }else{
            $this->exten=$exten;
            $this->domain=$domino;
            $pDB = new paloDB($arrConf['elastix_dsn']['elastix']);
            $query="SELECT code from organization where domain=?";
            $result = $pDB->getFirstRowQuery($query, false, array($this->domain));
            $this->code=$result[0];
        }
    }

    function output() {
        if($this->error!="")
            return "";
        else
            return $this->get_hint()."&Custom:DND".$this->code."_".$this->exten;
    }

    function get_hint(){
        // We should always check the EXTUSER in case they logged into a device
        // but we will fall back to the old methond if $astman not open although
        // I'm pretty sure everything else will puke anyhow if not running
        //
        $error="";
        $pDB=new paloDB(generarDSNSistema("asteriskuser", "elxpbx"));
        $astman=AsteriskManagerConnect($error);
        if($astman!=false){
            //se obtine los dispositivos a los cuales la extension esta asociada
            $deviceDB=$astman->database_get("EXTUSER",$this->code."/".$this->exten."/device");
            $device_arr = explode('&',$deviceDB);
            //se obtine como se marca a dichos dispositivos
            foreach($device_arr as $device){
                $tmp_dial=$astman->database_get("DEVICE",$this->code."/".$device."/dial");
                if($tmp_dial!=="ERROR")
                    $dial[]=$tmp_dial;
            }
        } else {
            $query = "SELECT dial from extension where exten=? and organization_domain=?";
            $arrayParam=array($this->exten,$this->domain);
            $results = $pDB->fetchTable($query, false, $arrayParam);
            //create an array of strings
            if (is_array($results)){
                foreach ($results as $result) {
                    $dial[] = str_replace('ZAP', 'DAHDI', $result[0]);
                }
            }
        }

        //create a string with & delimiter
        if (isset($dial) && is_array($dial)){
            $hint = implode($dial,"&");
        } else {
            $query = "SELECT dial from extension where exten=? and organization_domain=?";
            $results = $pDB->getFirstRowQuery($query, false, array($this->exten,$this->domain));
            if (isset($results[0])) {
                $hint = $results[0];
            } else {
                $hint = "";
            }
        }

        return $hint;
    }
}

class ext_directory extends extension{
	public $context;
	public $dial_context;
	public $options;

	function ext_directory($context,$dial_context,$options) {
		if(empty($context)){
			$this->error="vm-context can't be empty can't be empty";
		}else{
			$this->context=$context;
			if(empty($dial_context))
				$this->dial_context="default";
			else
				$this->dial_context=$dial_context;

			if(empty($options))
				$this->options="";
			else
				$this->options=",".$options;
		}
	}

	function output() {
		if($this->error)
			return "Noop(".$this->error.")";
		else
			return "Directory(".$this->context.",".$this->dial_context.$this->options.")";
	}
}

class ext_messageSend extends extension {
    public $to;
    public $from;
    
    function ext_messageSend($to,$from){
        $this->to=$to;
        $this->from=$from;
    }
    
    function output() {
        return "MessageSend(".$this->to.",".$this->from.")";
    }
    
}

class ext_while extends extension {
    public $condition;
    
    function ext_while($condition){
        $this->condition=$condition;
    }
    
    function output() {
        return "While(".$this->condition.")";
    }
}
/* example usage
$ext = new extensions;


$ext->add('default','123', 'dial1', new ext_dial('ZAP/1234'));
$ext->add('default','123', '', new ext_noop('test1'));
$ext->add('default','123', '', new ext_noop('test2'));
$ext->add('default','123', '', new ext_noop('test at +101'), 'dial1', 101);
$ext->add('default','123', '', new ext_noop('test at +102'));
echo "<pre>";
echo $ext->generateConf();
echo $ext->generateOldConf();
exit;
*/

/*
exten => 123,1(dial1),Dial(ZAP/1234)
exten => 123,n,noop(test1)
exten => 123,n,noop(test2)
exten => 123,dial1+101,noop(test at 101)
exten => 123,n,noop(test at 102)
*/
?>