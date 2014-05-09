var CxensePush = new function(){
    this.data = function(){
        if(confirm('Är du säker?')){
            jQuery.post(cxense.post_url, {
                getPosts: 'true'
            }, function (data) {
                var array = data.split(",");
                if(confirm("Kommer pusha "+array.length+" inlägg, fortsätta?")){
                    var start=prompt("Skippa ID tom:","0");
                    for(var i = 0; i<array.length;i++){

                        var id = array[i];
                        if(id > start){
                            jQuery.post(cxense.ajax_url, {
                                    id: id
                                }
                            );
                        }

                    }
                }
            });
        }
    }
}