var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['appModel.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        SYS = Brick.mod.sys;

    NS.NewsItem = Y.Base.create('newsItem', SYS.AppModel, [], {
        structureName: 'NewsItem'
    });

    NS.NewsList = Y.Base.create('newsList', SYS.AppModelList, [], {
        appItem: NS.NewsItem
    });

    NS.Config = Y.Base.create('config', SYS.AppModel, [], {
        structureName: 'Config'
    });
};
