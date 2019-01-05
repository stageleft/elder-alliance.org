// Author: Siegfried Puchbauer <rails-spinoffs@lists.rubyonrails.org>
Ajax.History = {
    initialize: function(options) {
        this.options = Object.extend({
            interval: 200
        },options||{});
        this.callback = this.options.callback || Prototype.emtpyFunction;
        if(navigator.userAgent.toLowerCase().indexOf('msie') > 0)
            this.locator = new Ajax.History.Iframe('ajaxHistoryHandler', this.options.iframeSrc);
        else
            this.locator = new Ajax.History.Hash();
        this.currentHash = '';
        this.locked = false;
    },
    add: function(hash) {
        this.locked = true;
        clearTimeout(this.timer);
        this.currentHash = hash;
        this.locator.setHash(hash);
        this.timer = setTimeout(this.checkHash.bind(this), this.options.interval);
        this.locked = false;
    },
    checkHash: function(){
        if(!this.locked){
            var check = this.locator.getHash();

            if(check != this.currentHash) {
/*							var myhash = check;
								if(myhash.indexOf('fi=') > -1) {
									myhash = myhash.split('^_^');
									var __qv = myhash[1].toQueryParams();
									myhash[1] = '';
									for(pkey in __qv) {
										if(pkey == 'fi')
											myhash[1] += pkey + '=' + encodeURIComponent(__qv[pkey]) + '&';
										else
											myhash[1] += pkey + '=' + __qv[pkey] + '&';
									}
									myhash[1] = myhash[1].replace(/&$/, '');
									myhash = myhash[0] + '^_^' + myhash[1];
								} else
									myhash = check;*/
                this.callback(check);
                this.currentHash = check;
            }
        }
        this.timer = setTimeout(this.checkHash.bind(this),
this.options.interval);
    },
    getBookmark: function(){
        return this.locator.getBookmark();
    }
};
// Hash Handler for IE (Tested with IE6)
Ajax.History.Iframe = Class.create();
Ajax.History.Iframe.prototype = {
    initialize: function(id, src) {
        this.url = '';
        this.id = id || 'ajaxHistoryHandler';
        this.src = src || '';
        document.write('<iframe src="'+this.src+'" id="'+this.id+'" name="'+this.id+'" style="display: none;" ></iframe>');
    },
    setHash: function(hash){
        try {
            $(this.id).setAttribute('src', this.src + '?' + hash);
        }catch(e) {}
        window.location.href = this.url + '#' + hash;
    },
    getHash: function(){
        try {
            return (document.frames[this.id].location.href||'?').split('?')[1];
        }catch(e){ return ''; }
    },
    getBookmark: function(){
        try{
            return window.location.href.split('#')[1]||'';
        }catch(e){ return ''; }
    }
};
// Hash Handler for a modern browser (tested with firefox 1.5)
Ajax.History.Hash = Class.create();
Ajax.History.Hash.prototype = {
    initialize: function(){
    },
    setHash: function(hash){
        window.location.hash = hash;
    },
    getHash: function(){
        return window.location.hash.substring(1)||'';
    },
    getBookmark: function(){
        try{
            return window.location.hash.substring(1)||'';
        }catch(e){ return ''; }
    }
};
