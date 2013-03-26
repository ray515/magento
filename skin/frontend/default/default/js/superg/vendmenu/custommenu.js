function sgShowMenuPopup(objMenu, popupId)
{
    objMenu = $(objMenu.id); var popup = $(popupId); if (!popup) return;
    popup.style.display = 'block';
    objMenu.addClassName('active');
    var popupWidth = VENDMENU_POPUP_WIDTH;
    if (!popupWidth) popupWidth = popup.getWidth();
    var pos = sgPopupPos(objMenu, popupWidth);
    popup.style.top = pos.top + 'px';
    popup.style.left = pos.left + 'px';
    if (VENDMMENU_POPUP_WIDTH) popup.style.width = VENDMENU_POPUP_WIDTH + 'px';
}

function sgPopupPos(objMenu, w)
{
    var pos = objMenu.cumulativeOffset();
    var wraper = $('vendmenu');
    var posWraper = wraper.cumulativeOffset();
    var wWraper = wraper.getWidth() - VENDMENU_POPUP_RIGHT_OFFSET_MIN;
    var xTop = pos.top - posWraper.top + VENDMENU_POPUP_TOP_OFFSET;
    var xLeft = pos.left - posWraper.left;
    if ((xLeft + w) > wWraper) xLeft = wWraper - w;
    return {'top': xTop, 'left': xLeft};
}

function sgHideMenuPopup(element, event, popupId, menuId)
{
    element = $(element.id); var popup = $(popupId); if (!popup) return;
    var current_mouse_target = null;
    if (event.toElement)
    {
        current_mouse_target = event.toElement;
    }
    else if (event.relatedTarget)
    {
        current_mouse_target = event.relatedTarget;
    }
    if (!sgIsChildOf(element, current_mouse_target) && element != current_mouse_target)
    {
        if (!sgIsChildOf(popup, current_mouse_target) && popup != current_mouse_target)
        {
            popup.style.display = 'none';
            $(menuId).removeClassName('active');
        }
    }
}

function sgIsChildOf(parent, child)
{
    if (child != null)
    {
        while (child.parentNode)
        {
            if ((child = child.parentNode) == parent)
            {
                return true;
            }
        }
    }
    return false;
}
