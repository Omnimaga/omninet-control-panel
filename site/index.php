<?php
	require_once(dirname(dirname(__FILE__)).'/header.php');
	header('X-UA-Compatible: IE=Edge');
	global $user;
	if($user = is_logged_in()){
		if(has_flag($user,'a')){
			$servers = get_servers_obj();
			$opers = get_opers_obj();
		}else{
			$servers = get_servers_for_current_user_obj();
			$opers = get_opers_for_current_user_obj();
		}
	}
	$dialogs = array();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Omninet</title>
		<script>
			__HOSTNAME__ = '<?php echo HOSTNAME; ?>';
		</script>
		<link href="<?php echo HOSTNAME; ?>site/favicon.ico" rel="icon" type="image/x-icon" />
		<script src="<?php echo HOSTNAME; ?>site/js/pomo.min.js"></script>
		<script src="<?php echo HOSTNAME; ?>site/js/Modernizr.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.0/jquery.cookie.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.8.5/js/selectize.min.js"></script>
		<script src="<?php echo HOSTNAME; ?>site/js/jquery.treegrid.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/history.js/1.8/bundled/html5/jquery.history.min.js"></script>
		<script src="<?php echo HOSTNAME; ?>site/js/jquery.timepicker.js"></script>
		<script src="<?php echo HOSTNAME; ?>site/js/jquery.ba-resize.min.js"></script>
		<?php if(get_conf('2-factor-method') == 'authy'){ ?>
			<script src="//cdnjs.cloudflare.com/ajax/libs/authy-forms.js/2.0/form.authy.min.js"></script>
		<?php } ?>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-url-parser/2.3.1/purl.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/1.3.0/handlebars.min.js"></script>
		<?php if(get_conf('persona-endpoint') != 'none'){ ?>
			<script src="//login.persona.org/include.js"></script>
		<?php } ?>
		<script src="<?php echo HOSTNAME; ?>site/js/index.js"></script>
		<link href="//code.jquery.com/ui/1.10.4/themes/dot-luv/jquery-ui.css" rel="stylesheet"/>
		<link href="//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.8.5/css/selectize.css" rel="stylesheet"/>
		<link href="//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.8.5/css/selectize.default.css" rel="stylesheet"/>
		<link href="<?php echo HOSTNAME; ?>site/jquery.treegrid.css" rel="stylesheet"/>
		<link href="<?php echo HOSTNAME; ?>site/jquery.timepicker.css" rel="stylesheet"/>
		<?php if(get_conf('2-factor-method') == 'authy'){ ?>
			<link href="//cdnjs.cloudflare.com/ajax/libs/authy-forms.css/2.0/form.authy.min.css" rel="stylesheet"/>
		<?php } ?>
		<link href="<?php echo HOSTNAME; ?>site/index.css" rel="stylesheet"/>
		<script>
			function runWhenExists(name){
				var run = function(){
					if(typeof window[name] != 'function'){
						setTimeout(run,10);
					}else{
						window[name]();
					}
				};
				run();
			}
		</script>
		<?php
			if($user){
				echo "<script>$(document).ready(function(){";
				if(is_logged_in() && is_verified()){
					echo "runWhenExists('ServerPing');";
				}
				if(has_flag($user,'u')){
					echo "runWhenExists('FetchMemos');";
					echo "runWhenExists('FetchNews');";
					echo "runWhenExists('FetchChannels');";
				}
				echo "});</script>";
				if(has_flag($user,'u')){ ?>
					<script id="template-memos" type="text/x-handlebars-template">
						<button class="button" value="<?php echo _('Refresh'); ?>" onclick="window.FetchMemos(true);">
							<?php echo _('Refresh'); ?>
						</button>
						<button style="background-color:green;background-image:none;" class="button" value="<?php echo _('New Memo'); ?>" id="send-memo" onclick="$('#memo-diag').dialog('open');">
							<?php echo _('New Memo'); ?>
						</button>
						<button class="button" style="background-color:red;background-image:none;" value="<?php echo _('Delete All'); ?>" onclick="return window.DeleteMemos();">
							<?php echo _('Delete All'); ?>
						</button>
						{{#each memos}}
							<div style="padding: 5px;" class="ui-widget ui-state-default ui-corner-all" id="memo-{{this.id}}">
								<div>
									<span>
										<?php echo _('From'); ?>:
									</span>
									<span style="font-weight:normal;" class='memo-from'>
										{{this.from}}
									</span>
								</div>
								<div>
									<span>
										<?php echo _('Sent'); ?>:
									</span>
									<span style="font-weight:normal;" class='memo-date'>
										{{this.date}}
									</span>
								</div>
								<div>
									<span>
										<?php echo _('Body'); ?>:
									</span>
									<span style="font-weight:normal;" class="memo-body">
										{{html this.body}}
									</span>
								</div>
								<button class="button" value="<?php echo _('Reply'); ?>" onclick="return window.ReplyToMemoFromButton.call(this);">
									<?php echo _('Reply'); ?>
								</button>
								<button style="background-color:red;background-image:none;" class="button" value="<?php echo _('Delete'); ?>" onclick="return window.DeleteMemoFromButton.call(this);">
									<?php echo _('Delete'); ?>
								</button>
							</div>
						{{/each}}
					</script>
					<script id="template-news" type="text/x-handlebars-template">
						<button value="<?php echo _('Refresh'); ?>" onclick="window.FetchNews(true);">
							<?php echo _('Refresh'); ?>
						</button>
						{{#each news}}
							<div id="news-{{this.id}}" class="ui-widget ui-state-default ui-corner-all" style="padding:5px;">
								<h2>
									{{this.title}}
								</h2>
								<div>
									<span>
										<?php echo _('From'); ?>:
									</span>
									<span style="font-weight:normal;">
										{{this.from}}
									</span>
								</div>
								<div>
									<span>
										<?php echo _('Sent'); ?>:
									</span>
									<span style="font-weight:normal;">
										{{this.date}}
									</span>
								</div>
								<p style="font-weight:normal;">
									{{html this.body}}
								</p>
							</div>
						{{/each}}
					</script>
					<script id="template-channels" type="text/x-handlebars-template">
						<button value="<?php echo _('Refresh'); ?>" onclick="window.FetchChannels(true);">
							<?php echo _('Refresh'); ?>
						</button>
						<button value="<?php echo _('New Channel'); ?>" style="background-color:green;background-image:none;" onclick="">
							<?php echo _('New Channel'); ?>
						</button>
						{{#each channels}}
							<div id="channel-{{this.name}}" class="ui-widget ui-state-default ui-corner-all" style="padding:5px;">
								{{this.name}}
								<br/>
								<?php echo _('Flags:'); ?>
								<ul>
									{{#each this.flags}}
										<li>{{this.name}}</li>
									{{/each}}
								</ul>
								<button value="<?php echo _('Delete'); ?>" style="background-color:red;background-image:none;" onclick="">
									<?php echo _('Delete'); ?>
								</button>
							</div>
						{{/each}}
					</script>
				<?php }
			}
		?>
	</head>
	<body style="display:none;">
		<?php
			$flag = is_verified();
			if($user && $flag){
		?>
			<div class="tabs">
				<ul>
					<?php
						if(has_flag($user,'u')){ ?>
							<li><a href="#news"><?php echo _('News'); ?></a></li>
							<li><a href="#memos"><?php echo _('Memos'); ?></a></li>
							<li><a href="#channels"><?php echo _('Channels'); ?></a></li>
						<?php }
						echo has_flag($user,'n')?'<li><a href="#servers">'._('Servers').'</a></li>':'';
						echo has_flag($user,'o')?'<li><a href="#opers">'._('Opers').'</a></li>':'';
						echo has_flag($user,'a')?'<li><a href="#config">'._('Configuration').'</a></li>':'';
					?>
					<li><a href="#profile"><?php echo _('Profile'); ?></a></li>
					<div id="user-menu-button" class="right button">
						<?php echo $user['nick']; ?>
					</div>
				</ul>
				<?php if(has_flag($user,'n')){?>
					<div id="servers">
						<?php
							echo get_servers_list_html($servers);
						?>
					</div>
				<?php }
				if(has_flag($user,'o')){?>
					<div id="opers">
						<?php echo get_opers_html($opers); ?>
					</div>
				<?php }
				if(has_flag($user,'a')){ ?>
					<div id="config">
						<?php echo render_configuration_table(); ?>
					</div>
				<?php }
				if(has_flag($user,'u')){ ?>
					<div id="news"></div>
					<div id="memos"></div>
					<div id="channels"></div>
				<?php 
					array_push($dialogs,array(
						'id'=>'memo-diag',
						'type'=>'form',
						'form_id'=>'memo',
						'form_submit_label'=>'Send',
						'form_fields'=>array(
							array(
								'name'=>'to',
								'label'=>_('To'),
								'type'=>'string',
								'value'=>''
							),
							array(
								'name'=>'message',
								'label'=>_('Message'),
								'type'=>'string',
								'value'=>''
							),
							array(
								'name'=>'action',
								'type'=>'hidden',
								'value'=>'send-memo'
							)
						)
					));
				} ?>
				<div id="profile">
					<?php
						echo get_user_html($user);
						if(has_flag($user,'a') || has_flag($user,'o') || has_flag($user,'n')){
							if(!isset($user['secret_key']) || is_null($user['secret_key']) || $user['secret_key'] == ''){
								switch(get_conf('2-factor-method')){
									case 'authy':
										echo '<div class="login-form">Enable 2-factor Authentication'.get_form_html('2-factor',array(
											array(
												'name'=>'country-code',
												'label'=>_('Country'),
												'type'=>'text',
												'attributes'=>array(
													'id'=>'authy-countries'
												)
											),
											array(
												'name'=>'cellphone',
												'label'=>_('Cell #'),
												'type'=>'text',
												'attributes'=>array(
													'id'=>'authy-cellphone'
												)
											),
											array(
												'name'=>'action',
												'type'=>'hidden',
												'value'=>'2-factor-register'
											)
										),'Submit').'</div>';
									break;
									case 'google-authenticator':
										$api = get_api();
										$_SESSION['secret_key'] = $api->createSecret();
										echo '<div class="login-form">Enable 2-factor Authentication'.get_form_html('2-factor',array(
											array(
												'type'=>'custom',
												'html'=>"<img src='data:image/png;base64,".base64_encode(file_get_contents($api->getQRCodeGoogleUrl('Omninet',$_SESSION['secret_key'])))."'/>"
											),
											array(
												'name'=>'token',
												'label'=>_('Token'),
												'type'=>'text'
											),
											array(
												'name'=>'action',
												'type'=>'hidden',
												'value'=>'2-factor-register'
											)
										),'Submit').'</div>';
									break;
									default:
								}
							}else{
								switch(get_conf('2-factor-method')){
									case 'authy':case 'google-authenticator':
										echo "<button id='2-factor-disable' value='".('Disable 2-factor')."'>"._('Disable 2-factor')."</button>";
									break;
									default:
								}
							}
							if(get_conf('persona-endpoint') != 'none'){
								echo "<div><span id='persona-register' class='ui-button ui-widget ui-state-default ui-corner-all' style='overflow:hidden;height:42px;padding:0px 20px 0px 0px;vertical-align:middle;'><img style='height:100%;float:left;' src='img/persona-logo.png'/><span style='display:inline-block;line-height:42px;'>"._('Link Persona')."</span></span></div>";
								$emails = get_emails($user['id'],true);
								foreach($emails as $k => $email){
									echo "<div><button id='persona-remove-{$email['id']}' value='"._('Remove')."'>"._('Remove')."</button>{$email['email']}</div>";
								}
							}
						}
					?>
				</div>
			</div>
			<ul class="menu" id="user-menu">
				<li><a id="roles-button"><?php echo _('Switch Role'); ?></a></li>
				<?php if(has_flag($user,'n')||has_flag($user,'a')){?>
					<li><a id="rehash-servers"><?php echo _('Rehash'); ?></a></li>
				<?php } ?>
				<li><a id="newpass-button"><?php echo _('Change Password'); ?></a></li>
				<?php if(has_flag($user,'u')){ ?>
					<li><a id="sync-pass"><?php echo _('Sync Password'); ?></a></li>
				<?php } ?>
				<li><a id="logout"><?php echo _('Logout'); ?></a></li>
			</ul>
			<?php
					array_push($dialogs,array(
						'id'=>'newpass-diag',
						'type'=>'form',
						'form_id'=>'newpass',
						'form_submit_label'=>_('Change Password'),
						'form_fields'=>array(
							array(
								'name'=>'password',
								'label'=>_('Password'),
								'type'=>'password',
								'value'=>''
							),
							array(
								'name'=>'newpass',
								'label'=>_('New Password'),
								'type'=>'password',
								'value'=>''
							),
							array(
								'name'=>'action',
								'type'=>'hidden',
								'value'=>'newpass'
							)
						)
					));
					$roles = array(array(
						'value'=>'user',
						'label'=>_('User')
					));
					if($res = query("SELECT rt.name AS value,rt.description AS label FROM user_role_types rt JOIN user_roles r ON r.user_role_id = rt.id JOIN users u ON r.user_id = u.id WHERE u.id = %d",array($user['id']))){
						while($role = $res->fetch_assoc()){
							array_push($roles,$role);
						}
					}
					array_push($dialogs,array(
						'id'=>'roles-diag',
						'type'=>'form',
						'form_id'=>'roles',
						'form_submit_label'=>_('Switch'),
						'form_fields'=>array(
							array(
								'name'=>'type',
								'label'=>_('Type'),
								'type'=>'select',
								'values'=>$roles,
								'value'=>isset($_COOKIE['type'])?$_COOKIE['type']:'user'
							),
							array(
								'name'=>'action',
								'type'=>'hidden',
								'value'=>'role'
							)
						)
					));
				}elseif($user && !$flag){
					array_push($dialogs,array(
						'id'=>'verify-diag',
						'type'=>'form',
						'autocomplete'=>'off',
						'form_id'=>'verify',
						'form_submit_label'=>_('Login'),
						'form_fields'=>array(
							array(
								'name'=>'token',
								'label'=>_('2-Factor Verification'),
								'type'=>'text',
								'attributes'=>array(
									'id'=>'authy-token',
									'style'=>'background-color:#F2DEDE;'
								)
							),
							array(
								'name'=>'action',
								'type'=>'hidden',
								'value'=>'verify'
							)
						)
					));
				}else{ 
					$roles = array(array(
						'value'=>'user',
						'label'=>_('User')
					));
					if($res = query("SELECT name AS value,description AS label FROM ircd.user_role_types")){
						while($role = $res->fetch_assoc()){
							array_push($roles,$role);
						}
					}
					array_push($dialogs,array(
						'id'=>'login-diag',
						'type'=>'form',
						'form_id'=>'login',
						'form_submit_label'=>_('Login'),
						'form_fields'=>array(
							array(
								'type'=>'custom',
								'html'=>get_conf('persona-endpoint') != 'none'?"<div><span id='persona-register' class='ui-button ui-widget ui-state-default ui-corner-all' style='overflow:hidden;height:42px;padding:0px 20px 0px 0px;vertical-align:middle;'><img style='height:100%;float:left;' src='img/persona-logo.png'/><span style='display:inline-block;line-height:42px;'>"._('Persona')."</span></span></div>":''
							),
							array(
								'name'=>'username',
								'label'=>_('Username'),
								'type'=>'text',
								'value'=>''
							),
							array(
								'name'=>'password',
								'label'=>_('Password'),
								'type'=>'password',
								'value'=>''
							),
							array(
								'name'=>'type',
								'label'=>_('Type'),
								'type'=>'select',
								'values'=>$roles
							),
							array(
								'name'=>'action',
								'type'=>'hidden',
								'value'=>'login'
							)
						)
					));
				}
			?>
			<div id="dialogs">
				<?php
					foreach($dialogs as $k => $diag){
						echo "<div id='{$diag['id']}'>";
						switch($diag['type']){
							case 'form':
								array_push($diag['form_fields'],array(
									'type'=>'submit',
									'value'=>$diag['form_submit_label']
								));
								$attributes = array(
									'id'=>$diag['form_id']
								);
								if(isset($diag['autocomplete'])){
									$attributes['autocomplete'] = $diag['autocomplete'];
								}
								echo get_form_html_advanced($attributes,$diag['form_fields']);
							break;
						}
						echo "</div>";
					}
				?>
			</div>
	</body>
</html>
