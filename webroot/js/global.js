var Global = {

    LoadSwf:function(){
        var flash_url = SOURCEURL + 'FruitMachApp.swf?'+(new Date()).getTime();
        var flash_vars = {
            'uid':UID,
            'serverip':SERVERIP,
            'serverport':SERVERPORT,
            'sourceurl':SOURCEURL
        };
        var flash_params = {
            'allowScriptAccess' : 'always',
            'allowFullScreen' : 'true',
            'wmode' :$.browser.mozilla? 'window':'opaque',
            'movie' : flash_url
        };
        var flash_attributes = {};
        swfobject.embedSWF(flash_url, "main", "800", "600", "10.0.0","expressInstall.swf", flash_vars, flash_params, flash_attributes, function(){});

    }

}