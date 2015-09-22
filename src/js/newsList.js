var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.NewsListWidget = Y.Base.create('newsListWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance){
            var page = this.get('page');
            appInstance.newsList(page, function(err, result){
                if (!err){
                    this.set('newsList', result.newsList);
                }
                this.renderList();
            }, this);
        },
        renderList: function(){
            var newsList = this.get('newsList');
            if (!newsList){
                return;
            }

            var tp = this.template,
                lst = "";

            newsList.each(function(news){
                lst += tp.replace('row', [
                    {
                        published: news.get('published') ?
                            Brick.dateExt.convert(news.get('published')) :
                            tp.replace('publishButton'),
                        dateline: Brick.dateExt.convert(news.get('dateline'))
                    },
                    news.toJSON()
                ]);
            });

            tp.setHTML('list', tp.replace('table', {rows: lst}));
        },
        newsPublish: function(newsid){
            var tp = this.template;
            tp.hide('publishButton.btn-' + newsid);
            tp.show('publishButton.loading-' + newsid);
            this.get('appInstance').newsPublish(newsid, function(err, result){
                tp.hide('publishButton.loading-' + newsid);
                if (!err){
                    tp.show('publishButton.published-' + newsid);
                    tp.setHTML('publishButton.published-' + newsid, Brick.dateExt.convert(result.newsItem.get('published')))
                }
            }, this);
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget,table,row,publishButton'},
            page: {value: 1},
            newsList: {value: null}
        },
        CLICKS: {
            publish: {
                event: function(e){
                    var newsid = e.target.getData('id') | 0;
                    this.newsPublish(newsid);
                }
            },
            edit: {
                event: function(e){
                    var newsid = e.target.getData('id') | 0;
                    this.go('news.editor', newsid);
                }
            }
        }
    });
};