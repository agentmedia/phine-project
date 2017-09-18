/**
 * An editable tree wih ajax for the phine backend
 * @param {string} responseUrl
 * @param {string|jQuery} container The tree container or its selector string
 * @param {int} indent The indent per tree level in pixels, default is 15px
 * @returns {ajaxTree} Creates the ajax tree
 */
function ajaxTree(responseUrl, container, indent)
{
    this._responseUrl = responseUrl;
    var me = this;
    this._tree = new editableTree(container, function(node){ me._remove(node); }, 
        function(anchor){ me._insert(anchor); }, indent);
    
    $('#modal-delete-close').click(function (e)
    {
        e.preventDefault();
        $('#delete-modal').foundation('reveal', 'close');
    });   
}

/**
 * Shows the item with the given tree id
 * @param {string} treeID
 * @param {boolean} dontScroll True if node shall not be scrolled to
 * @returns {void}
 */
ajaxTree.prototype.showTreeID = function(treeID, dontScroll)
{
    this._tree.showTreeID(treeID, dontScroll);
};

/**
 * Opens the confirm modal form and the response url when it is submitted
 * @param {jQuery} node
 * @returns {void}
 */
ajaxTree.prototype._remove = function(node)
{   
    var anchor = $(node).find('.tree-remover');
    $('#delete-modal p').text($(anchor).attr('data-tree-remove-message'));
    $('#delete-modal').foundation('reveal', 'open');
    var me = this;
    $('#delete-modal form').submit(function(e)
    {
        e.preventDefault();
        var params = {action: 'delete', item: $(node).attr('data-tree-id')};
        $.post(me._responseUrl, params, function(result){me._afterRemove(result, node);}, 'json');
    });
};

ajaxTree.prototype._afterRemove = function(result, node)
{
    if (!result.success){
        alert(result.message);
    }
    else {
        this._tree.removeNode(node);
        $('#delete-modal').foundation('reveal', 'close');
    }
};

ajaxTree.prototype._insert = function(anchor)
{
    if ($(anchor).hasClass('tree-insert-in')) {
        this._insertIn(anchor);
    }
    else {
        this._insertAfter(anchor);
    }
};

ajaxTree.prototype._insertIn = function(anchor)
{
    var insertNode = $(this._tree.getInsertNode());
    var targetNode = $(this._tree.nodeOf(anchor));
    var params = {action:'insertIn', item: insertNode.attr('data-tree-id'), parent: targetNode.attr('data-tree-id')};
    var me = this;
    $.post(this._responseUrl, params, function(result){me._afterInsert(result, anchor);}, 'json');
};

ajaxTree.prototype._insertAfter = function(anchor)
{
    var insertNode = $(this._tree.getInsertNode());
    var targetNode = $(this._tree.nodeOf(anchor));
    var params = {action:'insertAfter', item: insertNode.attr('data-tree-id'), previous: targetNode.attr('data-tree-id')};
    var me = this;
    $.post(this._responseUrl, params, function(result){me._afterInsert(result, anchor);}, 'json');
};

ajaxTree.prototype._afterInsert = function(result, anchor){
    if (!result.success) {
        alert(result.message);
    }
    else {
        this._tree.insert(anchor);
    }
};


