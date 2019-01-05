/* Ajax-SlimStat */
var SlimStat = {
	doing_ajax: null,
	cnt: "^_^",
	filter:function(){
		if ($("fi").value == '')
			alert('No Filter Resource');
		else {
			var par = Form.serialize($('slimstat_filter'));
			this.ajax('slim_main'+this.cnt+par);
		}
	},
	toggleAllSubs:function(id, me, count){
		var expanded = me.innerHTML == 'collapse';
		if(expanded) {
			$$('#twraper_'+id+' tr.subcons').each(function(subcon) {
				subcon.className = subcon.className.replace(' subcons',' collapsed-subcons');
			});
		} else {
			$$('#twraper_'+id+' tr.collapsed-subcons').each(function(subcon) {
				subcon.className = subcon.className.replace(' collapsed-subcons', ' subcons');
			});
		}
		$$('#twraper_'+id+' a.subcontgl').each(function(tgl) {
			if(expanded) tgl.addClassName('collapsed');
			else tgl.removeClassName('collapsed');
		});
		me.innerHTML = (expanded) ? 'expand' : 'collapse';
	},
	toggleSub:function(num, me, id){
		var expanded = !me.hasClassName('collapsed');
		$$('#twraper_'+id+' tr.subcon_'+num).each(function(subcon) {
			subcon.className = expanded ? subcon.className.replace(' subcons',' collapsed-subcons'):subcon.className.replace(' collapsed-subcons',' subcons');
		});
		if(expanded) me.addClassName('collapsed');
		else me.removeClassName('collapsed');
	},
	nav:function(){
		var par = Form.serialize($('page_navi'));
		this.ajax('slim_main'+this.cnt+par);
	},
	module:function(id, fi, t, p){
		var el = "twraper_"+ t;
		var par = "panel="+p+"&ajt=true&moid="+ id;
		if(fi) par += decodeURIComponent(fi);
		this.ajax(el+this.cnt+par);
	},
	panel:function(p,fi){
		var par = "panel=" + p;
		if(fi) par += decodeURIComponent(fi);
		this.ajax('slim_main'+this.cnt+par);
	},
	ajax:function(hash){
		if( hash == '' || hash.indexOf(this.cnt) < 0 ) return;
//    if(typeof dhtmlHistory !='undefined')
//			dhtmlHistory.add(hash, null); // dhtmlHistory
    if(typeof Ajax.History !='undefined')
			Ajax.History.add(decodeURIComponent(hash));	 // Ajax.History
		new SlimLoading(hash);
	}
};

var SlimLoading = Class.create();
SlimLoading.prototype = {
	initialize: function(hash){
		if (SlimStat.doing_ajax)
			SlimStat.doing_ajax.transport.abort();
		this.hash = hash;
		var pars = hash.split(SlimStat.cnt);
		if(!pars[1] || !pars[0]) return;
		this.qv = pars[1].toQueryParams();
		this.tid = $(pars[0]);
		this.par = pars[1];
		this.loadmodule = false;
		if(!this.tid) {
			this.fix_par();
		}
		this.url = $('ajax_request').href + "/" + ((pars[0] == "slim_main")?'panel':'module') + ".php";
		this.loading = $('slimloading');
		this.tid.fader = new fx.Opacity(this.tid, {duration: 250, onComplete: this.request.bind(this) });
		this.before();
	},
	current:function(cls, parent, cid){
		$$('#'+parent+' .'+cls).each(function(el){el.removeClassName(cls);});
		cid = $(cid);
		if(cid)
			cid.addClassName(cls);
	},
	fix_par: function(){
		this.tid = $('slim_main');
		this.par = '';
		for(key in this.qv) {
			if(key != 'moid' && key != 'ajt') {
				this.par += key + '=' + this.qv[key] + '&';
			}
		}
		this.par = this.par.replace(/&$/, '');
		this.loadmodule = true;
	},
	before: function(){
		if(this.tid.id == 'slim_main') {
			this.current('slm_current', 'slim_menu', 'slm'+this.qv['panel']);
		} else {
			var me = this.tid.id.replace('twraper_', '');
			this.current('md_current', 'module_'+me, 'ml'+me+'_'+this.qv['moid']);
		}
		this.tid.fader.custom(0.99, 0.4);
		this.loading.style.display = 'inline';
	},
	request: function(){
		this.tid.fader.options.onComplete = this.reset.bind(this);
		SlimStat.doing_ajax = new Ajax.Updater(this.tid.id, this.url, {method: 'get', parameters: this.par, onComplete: this.after.bind(this) });
	},
	reset: function(){
		// Reset opacity - for google maps
		if (window.ActiveXObject) 
			this.tid.style.filter = '';
		this.tid.style.opacity = '';

		// init sweetTitles
		if(typeof sweetTitles != 'undefined') {
			$('toolTip').remove();
			sweetTitles.init();
		}
		// if loaded from history cache and last request was module load,
		// Just go to the last panel and send request for module again.
		if(this.loadmodule)
			this.initialize(this.hash);
	},
	after: function(){
		this.loading.style.display = 'none';
		SlimStat.doing_ajax = null;
		this.tid.fader.custom(0.4, 0.99);
	}
};
//Event.observe(window,'load',function(){SlimStat.panel('1');},false);