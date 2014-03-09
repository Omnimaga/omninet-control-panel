<?php
	header('Content-type: text/plain');
	require_once('header.php');
	if(!isset($_GET['user']) || !isset($_GET['key']) || !isset($_GET['server'])){
		$opts = getopt('u:k:s:',Array('user:','key:','server:'));
		$_GET['user'] = isset($opts['user'])?$opts['user']:(isset($opts['u'])?$opts['u']:false);
		$_GET['key'] = isset($opts['key'])?$opts['key']:(isset($opts['k'])?$opts['k']:false);
		$_GET['server'] = isset($opts['server'])?$opts['server']:(isset($opts['s'])?$opts['s']:false);
		if(!$_GET['user'] || !$_GET['key'] || !$_GET['server']){
			header('Location: site/');
			die();
		}
	}
	$user = get_current_user_obj('netadmin') or $user = get_current_user_obj('servermanager') or $user = get_current_user_obj('globaladmin') or die('# Invalid user/key pair.');
	$server = get_current_server_obj() or die('# Invalid server name');;
	$opers = get_opers_for_server_obj($server['id']);
	$pass = mkpasswd(get_conf('server-pass'));
?>
#################################################
##                   Classes                   ##
#################################################
class		clients
{
	pingfreq 120;
	maxclients 500;
	sendq 100000;
	recvq 8000;
};
class		servers
{
	pingfreq 120;
	maxclients 11;
	sendq 1000000;
	connfreq 100;
};
#################################################
##                     Me                      ##
#################################################
me {
	name "<?php echo $server['host'];?>";
	info "<?php echo $server['description'];?>";
	numeric <?php echo $server['id'];?>;
};
#################################################
##                   Admin                     ##
#################################################
admin {
	"<?php echo $user['real_name'];?>";
	"<?php echo $user['nick'];?>";
	"<?php echo $user['email'];?>";
};
#################################################
##                 Listeners                   ##
#################################################
listen         *:6697
{
	options
	{
		ssl;
		clientsonly;
	};
};
listen         *:8067;
listen         *:6667;
listen         *:6666;
listen         *:6665;
listen         *:7150
{
	options
	{
		serversonly;
	};
};
listen         *:7100
{
	options
	{
		ssl;
		serversonly;
	};
};
#################################################
##                   Link                      ##
#################################################
<?php
	$ulines = get_ulines_obj();
	foreach($ulines as $k => $u){?>
link        <?php echo $u['host'];?> {
	username *;
	hostname *;
	bind-ip *;
	hub *;
	port 7150;
	password-receive "<?php echo $pass ?>" { sha1; };
	password-connect "<?php echo get_conf('server-pass'); ?>";
	class servers;
};
<?php
	}
	if(!is_null($server['parent'])){?>
link        <?php echo $server['parent']['host'];?> {
	username *;
	hostname <?php echo $server['parent']['ip'];?>;
	bind-ip *;
	hub *;
	port 7100;
	password-receive "<?php echo $pass ?>" { sha1; };
	password-connect "<?php echo get_conf('server-pass'); ?>";
	class       servers;
	options
	{
		zip;
		ssl;
		autoconnect;
		nodnscache;
		nohostcheck;
	};
};
<?php
	}
	if(isset($server['children'])){
		foreach($server['children'] as $k => $c){?>
link        <?php echo $c['host'];?> {
	username *;
	hostname <?php echo $c['ip'];?>;
	bind-ip *;
	hub *;
	port 7100;
	password-receive "<?php echo $pass ?>" { sha1; };
	password-connect "<?php echo get_conf('server-pass'); ?>";
	class       servers;
	options
	{
		zip;
		ssl;
		autoconnect;
		nodnscache;
		nohostcheck;
	};
};
<?php		}
	}
?>
ulines {
	<?php
		$ulines = get_ulines();
		foreach($ulines as $k => $uline){
			echo $uline.";\n";
			if($k < count($ulines)-1){
				echo "\t";
			}
		}
	?>
};
#################################################
##                   Log                       ##
#################################################
log "ircd.log" {
	flags {
		oper;
		kline;
		connects;
		server-connects;
		kills;
		errors;
		sadmin-commands;
		chg-commands;
		oper-override;
		spamfilter;
	};
};
#################################################
##                   Alias                     ##
#################################################
alias "glinebot" {
	format ".+" {
		command "gline";
		type real;
		parameters "%1 2d Bots are not allowed on this server, please read the faq at http://www.example.com/faq/123";
	};
	type command;
};
alias statserv { type stats; };
alias ss { target statserv; type stats; };
#################################################
##                   DRPass                    ##
#################################################
drpass {
	restart "<?php echo $pass ?>" { sha1; };
	die "<?php echo $pass ?>" { sha1; };
};
#################################################
##             Network Settings                ##
#################################################
set {
	network-name 		"omnimaga.org";
	default-server 		"irc.omnimaga.org";
	services-server 	"<?php echo get_conf('services-server','string'); ?>";
	stats-server		"<?php echo get_conf('stats-server','string'); ?>";
	help-channel 		"#omnimaga";
	hiddenhost-prefix	"omni";
	cloak-keys {
		"XFGasdgREWhgreTG43FDSfweqfew";
		"FDSAyh5ghREFadhrGHrewGQEg324";
		"ASGfdah4431fgdsagdsagASgrw32";
	};
	hosts {
		local			"local.users.irc.omnimaga.org";
		global			"global.users.irc.omnimaga.org";
		coadmin			"coadmin.users.irc.omnimaga.org";
		admin			"admin.users.irc.omnimaga.org";
		servicesadmin	"servicesadmin.users.irc.omnimaga.org";
		netadmin 		"netadmin.users.irc.omnimaga.org";
		host-on-oper-up "yes";
	};
	modes-on-join		"+nt";
	kline-address "admin@omnimaga.org";
	modes-on-connect "+G";
	modes-on-oper	 "+wgs";
	oper-auto-join "<?php echo get_conf('ops-channel','string'); ?>";
	options {
		hide-ulines;
		show-connect-info;
	};
	maxchannelsperuser 50;
	anti-spam-quit-message-time 10s;
	oper-only-stats "okfGsMRUEelLCXzdD";
	throttle {
		connections 3;
		period 60s;
	};
	anti-flood {
		nick-flood 3:60;
	};
	spamfilter {
		ban-time 1d;
		ban-reason "Spam/Advertising";
		virus-help-channel "#help";
	};
};
#################################################
##                Enable Mibbit                ##
#################################################
// Datacenter one:
cgiirc {
	type webirc;
	hostname 64.62.228.82;
	password <?php echo get_conf('mibbit-password','string'); ?>;
};
// Datacenter two:
cgiirc {
	type webirc;
	hostname 207.192.75.252;
	password <?php echo get_conf('mibbit-password','string'); ?>;
};
// Datacenter three:
cgiirc {
	type webirc;
	hostname 78.129.202.38;
	password <?php echo get_conf('mibbit-password','string'); ?>;
};
// Datacenter four:
cgiirc {
	type webirc;
	hostname 109.169.29.95;
	password <?php echo get_conf('mibbit-password','string'); ?>;
};
#################################################
##                    Allow                    ##
#################################################
allow {
	ip             *@*;
	hostname       *@*;
	class           clients;
	maxperip	10;
};
#################################################
##                     Deny                    ##
#################################################
deny dcc {
	filename "*sub7*";
	reason "Possible Sub7 Virus";
};
#################################################
##                    Bans                     ##
#################################################
ban nick {
	mask "*C*h*a*n*S*e*r*v*";
	reason "Reserved for Services";
};
#################################################
##                Localization                 ##
#################################################
files {
	motd "motd/en.txt";
	rules "rules/en.txt";
};
tld {
	mask *@*.ca;
	motd "motd/en_CA.txt";
	rules "rules/en_CA.txt";
};
tld {
	mask *@*.com;
	motd "motd/en_US.txt";
	rules "rules/en_US.txt";
};
tld {
	mask *@*.fr;
	motd "motd/fr.txt";
	rules "rules/fr.txt";
};
#################################################
##                    Opers                    ##
#################################################
oper RehashServ {
	class		clients;
	from {
		userhost RehashServ@localhost;
		userhost <?php echo get_conf('services-server'); ?>;
		userhost <?php echo get_conf('stats-server'); ?>;
		userhost <?php echo get_conf('irc-server'); ?>;
		userhost <?php echo get_conf('rehash-host'); ?>;
	};
	password "<?php echo mkpasswd(get_conf('rehash-pass')); ?>" { sha1; };
	flags {
		can_rehash;
		netadmin;
	};
};

<?php foreach($opers as $k => $oper){?>
oper <?php echo $oper['nick'];?> {
	class		clients;
	from {
		<?php foreach($oper['hosts'] as $k => $host){?>
			userhost <?php echo $host;?>;
		<?php } ?>
	};
	password "<?php echo $oper['password'];?>" { <?php echo $oper['password_type'];?>; };
	flags {
		<?php echo $oper['flags'];?>
	};
	swhois "<?php echo $oper['swhois'];?>";
};
<?php }?>
