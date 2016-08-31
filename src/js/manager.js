var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['newsList.js', 'lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.ManagerWidget = Y.Base.create('managerWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            var tp = this.template;
            
            this.listWidget = new NS.NewsListWidget({
                srcNode: tp.gel('list'),
                pageCurrent: this.get('page')
            });
        },
        destructor: function(){
            if (this.listWidget){
                this.listWidget.destroy();
            }
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget'}
        },
        CLICKS: {

        }
    });
};