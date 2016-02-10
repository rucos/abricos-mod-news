var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['editor.js']},
        {name: 'widget', files: ['calendar.js']},
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.NewsEditorWidget = Y.Base.create('newsEditorWidget', SYS.AppWidget, [], {
        buildTData: function(){
            return {
                statusClass: this.get('newsid') > 0 ? 'isSavedNews' : 'isNewNews'
            }
        },
        onInitAppWidget: function(err, appInstance){
            this.set('waiting', true);
            appInstance.config(function(){
                this.set('waiting', false);

                var newsid = this.get('newsid') | 0;
                if (newsid === 0){
                    var NewsItem = appInstance.get('NewsItem'),
                        newsItem = new NewsItem({
                            appInstance: appInstance
                        });
                    this.set('newsItem', newsItem);
                    this.renderNewsItem();
                } else {
                    appInstance.newsItem(newsid, function(err, result){
                        if (!err){
                            this.set('newsItem', result.newsItem);
                        }
                        this.renderNewsItem();
                    }, this);
                }
            }, this);
        },
        destructor: function(){
            if (this._introEditor){
                this._introEditor.destroy();
                this._bodyEditor.destroy();
                this.publishedWidget.destroy();
            }
        },
        renderNewsItem: function(){
            var newsItem = this.get('newsItem'),
                tp = this.template;

            if (!newsItem || !NS.roles.isWrite){
                tp.toggleView(true, 'notFound', 'viewer');
                return;
            }

            tp.setValue(newsItem.toJSON());

            this._introEditor = new SYS.Editor({
                appInstance: this.get('appInstance'),
                srcNode: tp.gel('introEditor'),
                content: newsItem.get('intro'),
                toolbar: SYS.Editor.TOOLBAR_STANDART
            });

            this._bodyEditor = new SYS.Editor({
                appInstance: this.get('appInstance'),
                srcNode: tp.gel('bodyEditor'),
                content: newsItem.get('body'),
                toolbar: SYS.Editor.TOOLBAR_STANDART
            });

            this.publishedWidget = new Brick.mod.widget.DateInputWidget(tp.gel('publishedWidget'), {
                'date': newsItem.get('published'),
                'showTime': true
            });
        },
        save: function(action){
            var newsItem = this.get('newsItem'),
                tp = this.template,
                published = this.publishedWidget.getValue(),
                data = {
                    id: newsItem.get('id'),
                    title: tp.getValue('title'),
                    intro: this._introEditor.get('content'),
                    body: this._bodyEditor.get('content'),
                    sourceName: tp.getValue('sourceName'),
                    sourceURI: tp.getValue('sourceURI'),
                    published: published ? published.getTime() / 1000 : (new Date()).getTime() / 1000
                };

            switch(action){
                case 'publish':
                    break;
                case 'draft':
                    data.published = 0;
                    break;
            }

            this.set('waiting', true);
            this.get('appInstance').newsSave(data, function(err, result){
                this.set('waiting', false);
                if (!err){
                    this.go('manager.view');
                }
            }, this);
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget'},
            newsid: {value: 0},
            newsItem: {value: null}
        },
        CLICKS: {
            save: 'save',
            publish: {
                event: function(){
                    this.save('publish');
                }
            },
            draft: {
                event: function(){
                    this.save('draft');
                }
            },
            cancel: {
                event: function(){
                    this.go('manager.view');
                }
            }
        }
    });


    NS.NewsEditorWidget.parseURLParam = function(args){
        return {
            newsid: args[0] | 0
        };
    };

};