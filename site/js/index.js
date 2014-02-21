$(function(){
	if(location.host != purl(__HOSTNAME__).attr('host')){
		location.href = __HOSTNAME__;
	}
	Pomo.domain = 'messages';
	Pomo.unescapeStrings = true;
	var _ = window._ = function(text){
			try{
				var t = Pomo.getText(text);
				return t.translation;
			}catch(e){
				return text;
			}
		},
		dialogs = $('#dialogs').children('div'),
		memos,
		news,
		templates = [],
		logout = function(){
			$.removeCookie('user',{
				path: '/'
			});
			$.removeCookie('key',{
				path: '/'
			});
			$.removeCookie('token',{
				path: '/'
			});
			$.removeCookie('PHPSESSID',{
				path: '/'
			});
			$.ajax(__HOSTNAME__+'site/api/',{
				data: {
					action: 'logout'
				},
				complete: function(){
					location.reload();
				},
				dataType: 'json'
			});
		},
		lang = Pomo.load(
			__HOSTNAME__+'site/api?action=lang',{
				format: 'po',
				mode: 'ajax'
			}
		);
	lang.ready(function(){
		$('script[id^=template-]').each(function(){
			templates[this.id.substr(9)] = Handlebars.compile($(this).html());
		});
		Handlebars.registerHelper('html',function(body){
			return new Handlebars.SafeString(body.replace(/(\b(https?|ftps?|file|irc):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig,"<a class='link' href='$1'>$1</a>"));
		});
		$('form').submit(function(){
			var form = $(this),
				btn = form.children('input[type=submit]'),
				action = form.children('input[type=hidden][name=action]').val();
			$.ajax(__HOSTNAME__+'site/api/?'+form.serialize(),{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					btn.removeAttr('disabled');
					if(d.code === 0){
						switch(action){
							case 'oper':
								form.find('input[name=password]').val('');
								alert(_('Oper updated'));
							break;
							default:
								location.reload();
						}
					}else{
						alert(d.message);
					}
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not submit the form")+": "+msg);
					btn.removeAttr('disabled');
				},
				dataType: 'json'
			});
			btn.attr('disabled','disabled');
			return false;
		}).children('input[type=hidden][name=action]').removeAttr('disabled');
		$('#logout').click(logout);
		$('#newpass-button').click(function(){
			$('#newpass-diag').dialog('open');
		});
		$('#roles-button').click(function(){
			$('#roles-diag').dialog('open');
		});
		$('#rehash-servers').click(function(){
			$.ajax(__HOSTNAME__+'site/api/?action=rehash',{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					alert(d.message);
					$('#rehash-servers').removeAttr('disabled');
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not rehash the servers")+": "+msg);
					$('#rehash-servers').removeAttr('disabled');
				},
				dataType: 'json'
			});
			$(this).attr('disabled','disabled');
			return false;
		});
		$('#2-factor-disable').click(function(){
			var btn = $(this);
			$.ajax(__HOSTNAME__+'site/api/?action=2-factor-delete',{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					alert(d.message);
					btn.removeAttr('disabled');
					location.reload();
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert("Could not disable 2-factor: "+msg);
					btn.removeAttr('disabled');
				},
				dataType: 'json'
			});
			$(this).attr('disabled','disabled');
			return false;
		});
		$('#sync-pass').click(function(){
			var btn = $(this);
			$.ajax(__HOSTNAME__+'site/api/?action=sync-pass',{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					alert(d.message);
					btn.removeAttr('disabled');
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not synchronize your password")+": "+msg);
					btn.removeAttr('disabled');
				},
				dataType: 'json'
			});
			btn.attr('disabled','disabled');
			return false;
		});
		$('#persona-register').hover(function(){
			$(this).addClass('ui-state-hover');
		},function(){
			$(this).removeClass('ui-state-hover');
		}).click(function(){
			if(confirm(_("This is an admin only feature. Continue?"))){
				navigator.id.request({
					siteName: 'Omninet'
				});
			}
		});
		if(navigator.id){
			navigator.id.watch({
				loggedInUser: $.cookie('personaUser'),
				onlogin: function(assertion){
					$.ajax({
						type: 'post',
						url: __HOSTNAME__+'site/api/?action=persona-login',
						data: {
							assertion: assertion
						},
						success: function(d){
							if(d.code !== 0){
								if(d.message){
									console.log(d.message);
									alert(d.message);
								}
							}
							location.reload();
						},
						error: function(xhr,s,e){
							navigator.id.logout();
							alert(_("Login failure")+": " + e);
						}
					});
				},
				onlogout: function(){
					//$('#logout').click();
				}
			});
		}
		$('button[id^=persona-remove-]').each(function(){
			var id = this.id.substr(15),
				btn = $(this);
			btn.click(function(){
				$.ajax(__HOSTNAME__+'site/api/?action=persona-remove&id='+id,{
					success: function(d){
						if(d.log){
							console.log(d.log);
						}
						if(d.message){
							alert(d.message);
						}
						location.reload();
					},
					error: function(xhr,msg,e){
						console.error(e);
						alert(_("Could not remove persona address")+": "+msg);
						btn.removeAttr('disabled');
					},
					dataType: 'json'
				});
				btn.attr('disabled','disabled');
				return false;
			});
		});
		$('.server-opers,.server-owner,.server-children,.server-parent').click(function(){
			$(this).next().toggle();
		}).next().hide();
		$('.button,button,input[type=button],input[type=submit]').button();
		$('.tabs').tabs({
			activate: function(e,ui){
				var url = $.url(),
					params = url.data.param.query;
				params.tab = ui.newPanel.attr('id');
				History.pushState({},document.title,url.attr('path')+'?'+$.param(params)+url.attr('anchor'));
			},
			create: function(e,ui){
				$(window).trigger('statechange');
			},
			heightStyle: 'fill'
		}).addClass('transparent').each(function(){
			var tabs = $(this);
			tabs.parent().resize(function(){
				tabs.tabs('refresh');
			});
		});
		dialogs.dialog({
			modal: true,
			draggable: false,
			autoOpen: false,
			width: 500
		});
		$('.menu').menu();
		$(window).on('statechange',function(){
			var url = $.url(),
				tab = url.param('tab'),
				params = url.data.param.query,
				tabel = $('.tabs').children('ul').children('li').children('a[href="#'+tab+'"]');
			if(tab && tabel.length == 1){
				$('.tabs').tabs('option','active',tabel.parent().index());
			}else{
				var href = $('.tabs').children('ul').children('li').children('a');
				if(href.length > 0){
					href = href.get(0).href;
				}else{
					href = '';
				}
				params.tab = $.url(href).attr('fragment');
				History.pushState({},document.title,url.attr('path')+'?'+$.param(params)+url.attr('anchor'));
			}
		}).trigger('statechange').resize(function(){
			dialogs.each(function(){
				var d = $(this);
				if(d.dialog('isOpen')){
					d.dialog("option", "position", "center");
				}
			});
			var b = $('#user-menu-button');
			if(b.length > 0){
				$('#user-menu').offset({
					top: b.offset().top
				});
			}
		});
		$('#login-diag,#verify-diag').dialog('option',{
			closeOnEscape: false,
			close: function(){
				location.href = 'http://omnimaga.org';
			},
			position:{
				my: "center",
				at: "center",
				of: window
			}
		}).dialog('open');
		$('#verify-diag').dialog('option','close',logout);
		$('.accordion').accordion({
			collapsible: true,
			active: false,
			heightStyle: 'content'
		}).css('max-height','500px');
		$('.tree').treegrid({
			initialState: 'collapsed'
		});
		$('#user-menu-button').click(function(){
			$('#user-menu').show();
		});
		$('#user-menu').css({
			position: 'fixed',
			right: '0'
		}).hover(function(){},function(){
			$(this).hide();
		}).click(function(){
			$(this).hide();
		}).hide();
		if(!Modernizr.inputtypes.date){
			$('input[type=date]').datepicker({
				dateFormat: 'yy-mm-dd'
			});
		}
		if(!Modernizr.inputtypes.datetime){
			$('input[type=datetime]').datetimepicker({
				dateFormat: 'yy-mm-dd',
				timeFormat:'HH:mm:ssZ'
			});
		}
		if(!Modernizr.inputtypes.number){
			$('input[type=number]').spinner();
		}
		$('body').show();
		window.ServerPing = function(){
			console.log(_("Server Ping"));
			$.ajax(__HOSTNAME__+'site/api/?action=ping',{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					if(d.message){
						alert(d.message);
					}
					if(d.code!==0){
						location.reload();
					}
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not ping server")+": "+msg);
					location.reload();
				},
				dataType: 'json'
			});
			setTimeout(window.ServerPing,1000*60*5); // Every 5 minutes
		};
		window.FetchMemos = function(once){
			console.log(_("Fetching Memos"));
			$.ajax(__HOSTNAME__+'site/api/?action=get-memos',{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					if(d.message){
						alert(d.message);
					}
					if(d.code!==0){
						location.reload();
					}
					var i,
						m;
					if(d.memos){
						for(i in d.memos){
							m = d.memos[i];
							m.date = m.date.year+'-'+m.date.month+'-'+m.date.day+' '+m.date.time;
							d.memos[i] = m;
						}
						if(typeof memos != 'undefined' && !once && ($(d.memos).not(memos).length !== 0 || $(memos).not(d.memos).length !== 0)){
							alert('New memo');
						}
						memos = d.memos;
					}
					$('#memos').html(templates.memos(d)).find('button').button();
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not contact server")+": "+msg);
					location.reload();
				},
				dataType: 'json'
			});
			if(!once){
				setTimeout(window.ServerPing,1000*60); // Every minute
			}
		};
		window.DeleteMemoFromButton = function(){
			window.DeleteMemo($(this).parent());
		};
		window.ReplyToMemoFromButton = function(){
			window.ReplyToMemo($(this).parent());
		};
		window.ReplyToMemo = function(div){
			var from = div.find('.memo-from').text().trim();
			$('#memo-diag').dialog('open').find('input[name=to]').val(from);
			$('#memo-diag').find('input[name=message]').select();
		};
		window.DeleteMemos = function(){
			window.DeleteMemo($('<div>').attr('id','memo-all'),function(){
				window.FetchMemos(true);
			});
		};
		window.DeleteMemo = function(div,callback){
			var id = $(div).attr('id').substr(5);
			console.log(_("Deleting memo")+": "+id);
			$.ajax(__HOSTNAME__+'site/api/?action=delete-memo&id='+id,{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					if(d.message){
						alert(d.message);
					}
					if(d.code!==0){
						location.reload();
					}
					div.remove();
					if(typeof callback != 'undefined'){
						callback();
					}
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not ping server")+": "+msg);
					location.reload();
				},
				dataType: 'json'
			});
		};
		window.FetchNews = function(once){
			console.log(_("Fetching News"));
			$.ajax(__HOSTNAME__+'site/api/?action=get-news',{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					if(d.message){
						alert(d.message);
					}
					if(d.code!==0){
						location.reload();
					}
					var i,
						n;
					if(d.news){
						d.news = d.news.reverse();
						for(i in d.news){
							n = d.news[i];
							n.date = n.date.year+'-'+n.date.month+'-'+n.date.day+' '+n.date.time;
							d.news[i] = n;
						}
						if(typeof news != 'undefined' && !once && ($(d.news).not(news).length !== 0 || $(news).not(d.news).length !== 0)){
							alert(_('New news item'));
						}
						news = d.news;
					}
					$('#news').html(templates.news(d)).find('button').button();
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not contact server")+": "+msg);
					location.reload();
				},
				dataType: 'json'
			});
			if(!once){
				setTimeout(window.ServerPing,1000*60); // Every minute
			}
		};
		window.FetchChannels = function(once){
			console.log(_("Fetching Channels"));
			$.ajax(__HOSTNAME__+'site/api/?action=get-channels',{
				success: function(d){
					if(d.log){
						console.log(d.log);
					}
					if(d.message){
						alert(d.message);
					}
					if(d.code!==0){
						location.reload();
					}
					var i,
						n;
					if(d.channels){
						for(i in d.channels){
							n = d.channels[i];
							d.channels[i] = n;
						}
					}
					$('#channels').html(templates.channels(d)).find('button').button();
				},
				error: function(xhr,msg,e){
					console.error(e);
					alert(_("Could not contact server")+": "+msg);
					location.reload();
				},
				dataType: 'json'
			});
			if(!once){
				setTimeout(window.ServerPing,1000*60); // Every minute
			}
		};
		$('body').find('*').contents().filter(function(){
			return this.nodeType === 3;
		}).each(function(){
			console.log(_($(this).text()));
			this.nodeValue = _($(this).text());
		});
		$('body').resize();
	});
});