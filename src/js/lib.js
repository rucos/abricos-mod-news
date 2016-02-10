var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['application.js']},
        {name: '{C#MODNAME}', files: ['model.js']}
    ]
};
Component.entryPoint = function(NS){

    NS.roles = new Brick.AppRoles('{C#MODNAME}', {
        isAdmin: 50,
        isWrite: 30,
        isView: 10
    });

    var COMPONENT = this,
        SYS = Brick.mod.sys;

    SYS.Application.build(COMPONENT, {}, {
        initializer: function(){
            this.cacheClear();
            NS.roles.load(function(){
                this.initCallbackFire();
            }, this);
        },
        cacheClear: function(){
            this._cache = {
                newsList: {}
            };
        }
    }, [], {
        REQS: {
            newsList: {
                args: ['page'],
                attribute: false,
                type: 'modelList:NewsList',
                // TODO: local cache
                /*
                response: function(res){
                    var NewsList = this.get('NewsList'),
                        newsList = new NewsList({
                            appInstance: this,
                            items: res.list,
                            page: res.page
                        });
                    return this._cache.newsList[res.page] = newsList;
                }
                /**/
            },
            newsItem: {
                args: ['newsid'],
                attribute: false,
                type: 'model:NewsItem'
            },
            newsSave: {
                args: ['news']
            },
            newsPublish: {
                args: ['newsid']
            },
            newsRemove: {
                args: ['newsid']
            },
            config: {
                attribute: true,
                type: 'model:Config'
            },
            configSave: {
                args: ['config']
            },
            newsCount: {
            	
            }
        },
        ATTRS: {
            isLoadAppStructure: {value: true},
            NewsItem: {value: NS.NewsItem},
            NewsList: {value: NS.NewsList},
            Config: {value: NS.Config},
            curentPage: {value: 1}
        },
        URLS: {
            ws: "#app={C#MODNAMEURI}/wspace/ws/",
            manager: {
                view: function(){
                    return this.getURL('ws') + 'manager/ManagerWidget/'
                }
            },
            news: {
                create: function(){
                    return this.getURL('news.editor');
                },
                editor: function(newsid){
                    return this.getURL('ws') + 'newsEditor/NewsEditorWidget/' + (newsid | 0) + '/';
                }
            },
            config: function(){
                return this.getURL('ws') + 'config/ConfigWidget/';
            }
        }
    });
};