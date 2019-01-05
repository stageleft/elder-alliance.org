/* Ajax-SlimStat */
var SlimStat = {
	doing_ajax: null,
	cnt: "^_^",
	filter:function(){
		if (jQuery("#fi").val() == '')
			alert('No Filter Resource');
		else {
			var par = jQuery('#slimstat_filter').formSerialize();
			this.ajax('slim_main'+this.cnt+par);
		}
	},
	toggleAllSubs:function(id, me, count){
		var expanded = me.innerHTML == 'collapse';
		if(expanded) {
			jQuery('#twraper_'+id+' tr.subcons').each(function() {
				jQuery(this).removeClass('subcons').addClass('collapsed-subcons');
			});
		} else {
			jQuery('#twraper_'+id+' tr.collapsed-subcons').each(function() {
				jQuery(this).removeClass('collapsed-subcons').addClass('subcons');
			});
		}
		jQuery('#twraper_'+id+' a.subcontgl').each(function(tgl) {
			if(expanded) jQuery(this).addClass('collapsed');
			else jQuery(this).removeClass('collapsed');
		});
		me.innerHTML = (expanded) ? 'expand' : 'collapse';
	},
	toggleSub:function(num, me, id){
		var expanded = (me.className.indexOf("collapsed") < 0);
		jQuery('#twraper_'+id+' tr.subcon_'+num).each(function() {
			jQuery(this).removeClass(expanded?'subcons':'collapsed-subcons').addClass(expanded?'collapsed-subcons':'subcons');
		});
		if(expanded) me.className += ' collapsed';
		else me.className = me.className.replace(' collapsed', '');
	},
	nav:function(){
		var par = jQuery('#page_navi').formSerialize();
		this.ajax('slim_main'+this.cnt+par);
	},
	module:function(id, fi, t, p){
		var el = "twraper_"+ t;
		var par = "panel="+p+"&ajt=true&moid="+ id;
		if(fi) par += decodeURIComponent(fi);
		this.ajax(el+this.cnt+par);
	},
	reloadmodule:function(id, fi, p){
		var sel = document.getElementById('mo_selector_'+id);
		if (sel) {
			var load = sel.options[sel.selectedIndex].value;
		} else {
			var load = id;
		}
		SlimStat.module(load, fi, id, p);
	},
	panel:function(p,fi){
		var par = "panel=" + p;
		if(fi) par += decodeURIComponent(fi);
		this.ajax('slim_main'+this.cnt+par);
	},
	_toQueryParams:function(a){
		var q = "" + a;
		var qv = [];
		q = q.replace(/^\?/,''); // remove any leading ?
		q = q.replace(/\&$/,''); // remove any trailing &
		jQuery.each(q.split('&'), function(){
			var key = this.split('=')[0];
			var val = this.split('=')[1];
			if(/^[0-9.]+$/.test(val))
				val = parseFloat(val);
			else if (/^[0-9]+$/.test(val))
				val = parseInt(val);
				if (!isNaN(val) && val != undefined && val !== null) {
					qv[key] = val;
			}
		});
		return qv;
	},
	toQueryParams: function( s ) {
		var r = {}; if ( !s ) { return r; }
		var s = s.replace(/^\?/,'').replace(/[;&]$/,''); // remove any leading ?, remove any trailing & || ;
		var pp = s.split('&');
		for ( var i=0; i<pp.length; i++ ) {
			var p = pp[i].split('=');
			r[p[0]] = p[1];
		}
		return r;
	},
	current:function(tid, qv, aborted){
		if (aborted || tid == 'slim_main') {
			jQuery('#slim_menu .slm_current:first').removeClass('slm_current');
			jQuery('#slim_menu #slm'+qv['panel']).addClass('slm_current');
		}
		if (tid != 'slim_main') {
			var parent = tid.replace('twraper_', '');
			var me = qv['moid'] || parent;
			var sel = document.getElementById('mo_selector_'+parent);
			if (!sel) return;
			for (var i=0; i<sel.options.length; i++) {
				if (sel.options[i].value == me) { sel.selectedIndex = i; 	break; }
			}
		}
	},
	ajax:function(hash){
		if( hash == '' || hash.indexOf(this.cnt) < 0 ) return;
    if(typeof jQuery.historyAddHistory == 'function')
			jQuery.historyLoad(decodeURIComponent(hash));
		else SlimLoading.start(hash);
	}
};

var SlimLoading = {
	start:function(hash) {
		if( hash == '' || hash.indexOf(SlimStat.cnt) < 0 ) return;
		SlimLoading.aborted = SlimLoading.loadmodule = false;
		if (SlimLoading.doing_ajax)
			SlimLoading.abort();
		SlimLoading.empty();
		SlimLoading.hash = hash;
		var pars = hash.split(SlimStat.cnt);
		if(!pars[1] || !pars[0]) return;
		SlimLoading.tid = pars[0];
		SlimLoading.par = pars[1];
		SlimLoading.qv = SlimStat.toQueryParams(SlimLoading.par);
		if(!jQuery('#'+SlimLoading.tid).length) {
			SlimLoading.fix_par();
		}
		SlimLoading.request();
	},
	empty:function() {
		SlimLoading.hash = SlimLoading.tid = SlimLoading.par = SlimLoading.qv = null;
	},
	abort:function() {
		SlimLoading.aborted = true;
		SlimLoading.doing_ajax.abort();
		jQuery('#'+SlimLoading.tid).fadeTo('normal', 1).parent().parent().removeClass('now_loading');
	},
	fix_par: function(){
		SlimLoading.tid = 'slim_main';
		SlimLoading.par = '';
		var _qv = SlimLoading.qv;
		delete _qv['moid'];
		delete _qv['ajt'];
		SlimLoading.par = jQuery.param(_qv);
		SlimLoading.loadmodule = true;
	},
	request:function() {
		SlimLoading.doing_ajax = jQuery.ajax({
			async : true,
			type : 'GET',
			processData: false,
			url: jQuery('#ajax_request').attr('href') + "/" + ((SlimLoading.tid == "slim_main")?'panel':'module') + ".php",
			data: SlimLoading.par,
			error: function(){
			},
			complete : function(){
				SlimLoading.doing_ajax = null;
			},
			cache: true,
			beforeSend: function() {
				SlimStat.current(SlimLoading.tid, SlimLoading.qv, SlimLoading.aborted);
				jQuery('#slimloading').css('display', 'inline');
				jQuery('#'+SlimLoading.tid).fadeTo('normal', 0.3).parent().parent().addClass('now_loading');
			},
			success: function(request){
				jQuery('#'+SlimLoading.tid).html(request).fadeTo('normal', 1).parent().parent().removeClass('now_loading');
				jQuery('#slimloading').css('display', 'none');
				// if loaded from history cache and last request was module load,
				// Just go to the last panel and send request for module again.
				if(SlimLoading.loadmodule)
					SlimLoading.start(SlimLoading.hash);
				else {
					// init sweetTitles
					if(typeof sweetTitles != 'undefined') {
						jQuery('#toolTip').remove();
						sweetTitles.init();
					}
/*					// Reset opacity - for google maps
					if (window.ActiveXObject) 
						tid.style.filter = '';
					tid.style.opacity = '';
*/
				}
			}
		});
	}
};