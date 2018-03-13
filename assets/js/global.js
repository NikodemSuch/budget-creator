// Notification dropdown

$('.dropdown .dropdown-toggle').on('click', function (event) {
    $(this).parent().toggleClass('open');
});

$('body').on('click', function (e) {
    if (!$('.dropdown').is(e.target)
        && $('.dropdown').has(e.target).length === 0
        && $('.open').has(e.target).length === 0)
    {
        $('.dropdown').removeClass('open');
    }
});

$("#notifications-container .notification-unread :checkbox").prop("checked", false);
$("#notifications-container .notification-read :checkbox").prop("checked", true);
$('#notifications-container :checkbox').prop("disabled", true);

$(document).ready(function () {

    $('#notifications-container :checkbox').prop("disabled", false);
    $('#notifications-container').on("change", ":checkbox", function() {

        var notificationId = $(this).attr('data-notification-id');
        var notification = $(this).parent().parent();

        if (this.checked) {

            $(notification).removeClass("notification-unread");
            $(notification).addClass("notification-read");
            var url = "/notification/markAsRead";
            setNotificationStatus(url);

        } else {

            $(notification).removeClass("notification-read");
            $(notification).addClass("notification-unread");
            var url = "/notification/markAsUnread";
            setNotificationStatus(url);

        }

        function setNotificationStatus(url) {
            $.ajax ({
                url: url,
                type: "POST",
                data: { notificationId: notificationId },
                async: true,
                success: function(data) {
                    console.log(data);
                }
            });
        }

        var notificationsNum = $("#notifications .notification-unread").length;
        $("#notification-num").html(notificationsNum);

    });

    // UserGroup dynamic form

    const removeIcon = '<a href="#" class="removeUserInput"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';

    $("#user-fields-list").find(".userProperty").after(removeIcon);

    $('.add-another-collection-widget').click(function (e) {
        e.preventDefault();
        var list = $($(this).attr('data-list'));

        var counter = list.children().length;

        var newWidget = list.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, counter);

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
