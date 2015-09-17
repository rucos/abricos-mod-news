var Component = new Brick.Component();
Component.requires = {
    mod: [{name: 'user', files: ['cpanel.js']}]
};
Component.entryPoint = function(){

    if (Brick.AppRoles.check('news', '30')){
        return;
    }

    var cp = Brick.mod.user.cp;

    var menuItem = new cp.MenuItem(this.moduleName);
    menuItem.icon = '/modules/news/images/cp_icon.gif';
    menuItem.titleId = 'mod.news.cp.title';
    menuItem.entryComponent = 'manager';
    menuItem.entryPoint = 'Brick.mod.news.API.showNewsListWidget';

    cp.MenuManager.add(menuItem);
};
