$(document).ready(function () {

    const removeIcon = '<a href="#" class="removeUserInput"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';

    $("#user-fields-list").find(".userProperty").after(removeIcon);

    $('.add-another-collection-widget').click(function (e) {
        e.preventDefault();
        var list = $($(this).attr('data-list'));

        var counter = list.children().length || 0;

        var newWidget = list.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, counter);

        list.attr('widget-counter', counter + 1);

        var newElement = $(list.attr('data-widget-members')).append(newWidget);

        newElement.children(":first").prop("placeholder", 'Username or user email');
        newElement.append(removeIcon);
        newElement.appendTo(list);
    });

    // on.('click') method with delegated event - making it this way helps us to handle DOM object without needing it to be loaded when the page renders

    $('#user-fields-list').on('click', 'div a.removeUserInput', function(e) {
        e.preventDefault();
        $(this).parent().remove();
    });

});
