var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['data.js', 'container.js', 'widgets.js', 'wait.js']}
    ]
};
Component.entryPoint = function(NS){

    if (!NS.data){
        NS.data = new Brick.util.data.byid.DataSet('news');
    }
    var DATA = NS.data;

    var LW = Brick.widget.LayWait;

    var buildTemplate = this.buildTemplate;

    var NewsListWidget = function(el){
        var TM = buildTemplate(this, 'widget,table,row,rowwait,rowdel,btnpub');

        var config = {
            rowlimit: 10,
            tables: {
                'list': 'newslist',
                'count': 'newscount'
            },
            tm: TM,
            paginators: ['widget.pagtop', 'widget.pagbot'],
            DATA: DATA
        };
        NewsListWidget.superclass.constructor.call(this, el, config);
    };

    YAHOO.extend(NewsListWidget, Brick.widget.TablePage, {
        initTemplate: function(){
            return this._T['widget'];
        },
        renderTableAwait: function(){
            var TM = this._TM;
            TM.getEl("widget.table").innerHTML = TM.replace('table', {
                'scb': '', 'rows': TM.replace('rowwait')
            });
        },
        renderRow: function(di){
            return this._TM.replace(di['dd'] > 0 ? 'rowdel' : 'row', {
                'dl': Brick.dateExt.convert(di['dl']),
                'tl': di['tl'],
                'dp': (di['dp'] > 0 ? Brick.dateExt.convert(di['dp']) : this._T['btnpub']),
                'prv': '/news/' + di['id'] + '/',
                'scb': '',
                'id': di['id']
            });
        },
        renderTable: function(lst){
            this._TM.getEl("widget.table").innerHTML = this._TM.replace('table', {
                'scb': '', 'rows': lst
            });
        },
        onClick: function(el){
            var TM = this._TM, T = this._T, TId = this._TId;

            switch (el.id) {
                case TId['widget']['refresh']:
                    this.refresh();
                    return true;
                case TId['widget']['rcclear']:
                    this.recycleClear();
                    return true;
            }

            var prefix = el.id.replace(/([0-9]+$)/, '');
            var numid = el.id.replace(prefix, "");

            switch (prefix) {
                case (TId['rowdel']['restore'] + '-'):
                    this.restore(numid);
                    return true;
                case (TId['row']['remove'] + '-'):
                    this.remove(numid);
                    return true;
                case (TId['btnpub']['id'] + '-'):
                    this.publish(numid);
                    return true;
            }
            return false;
        },

        changeStatus: function(commentId){
            var rows = this.getRows();
            var row = rows.getById(commentId);
            row.update({
                'st': row.cell['st'] == 1 ? 0 : 1,
                'act': 'status'
            });
            row.clearFields('st,act');
            this.saveChanges();
        },
        _createWait: function(){
            return new LW(this._TM.getEl("widget.table"), true);
        },
        _ajax: function(data){
            var lw = this._createWait(), __self = this;
            Brick.ajax('news', {
                'data': data,
                'event': function(request){
                    lw.hide();
                    __self.refresh();
                }
            });
        },
        remove: function(newsid){
            this._ajax({'type': 'news', 'do': 'remove', 'id': newsid});
        },
        restore: function(newsid){
            this._ajax({'type': 'news', 'do': 'restore', 'id': newsid});
        },
        recycleClear: function(){
            this._ajax({'type': 'news', 'do': 'rclear'});
        },
        publish: function(newsid){
            this._ajax({'type': 'news', 'do': 'publish', 'id': newsid});
        }
    });

    NS.NewsListWidget = NewsListWidget;

};