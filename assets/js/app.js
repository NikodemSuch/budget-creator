// Confirmation modals

$('.delete-url').confirm({
    title: 'Confirm Delete',
    content: 'Are you sure?',
    buttons: {
        cancel: {
            text: 'Cancel',
            action: function () {}
        },
        confirm: {
            text: 'Confirm',
            btnClass: 'btn-blue',
            keys: ['enter'],
            action: function () {
                location.href = this.$target.attr('href');
            }
        }
    }
});

// Enable tooltips

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});

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

$("#notifications-container .notification-unread :checkbox").prop("checked", true);
$("#notifications-container .notification-read :checkbox").prop("checked", false);
$('#notifications-container :checkbox').prop("disabled", true);

function setUnreadStatus(unread, notificationId) {

    var url = unread ? "/notification/mark-as-unread" : "/notification/mark-as-read";

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

$(document).ready(function () {

    // Notification box

    $('#notifications-container :checkbox').prop("disabled", false);
    $('#notifications-container').on("change", ":checkbox", function() {

        var notificationId = $(this).attr('data-notification-id');
        var notification = $(this).parent().parent();

        if (this.checked) {
            $(notification).addClass("notification-unread");
            setUnreadStatus(true, notificationId);

        } else {
            $(notification).removeClass("notification-unread");
            setUnreadStatus(false, notificationId);
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

// Report form

$('#report_reportables').hide();

if ($("#report_type").val() == 'accounts') {
    showAccountsContainer();
} else if ($("#report_type").val() == 'budgets') {
    showBudgetsContainer();
}

$("#report_type").on('change', function () {

    if ($(this).val() == 'accounts') {
        showAccountsContainer();
    } else if ($(this).val() == 'budgets') {
        showBudgetsContainer();
    } else {
        hideReportablesContainers();
    }
});

function showAccountsContainer() {
    $('.report-budgets-container').hide();
    $('.report-accounts-container').slideDown(300);
    $('.report-budgets-container :checkbox').prop("disabled", true);
    $('.report-accounts-container :checkbox').prop("disabled", false);
}

function showBudgetsContainer() {
    $('.report-budgets-container').slideDown(300);
    $('.report-accounts-container').hide();
    $('.report-budgets-container :checkbox').prop("disabled", false);
    $('.report-accounts-container :checkbox').prop("disabled", true);
}

function hideReportablesContainers() {
    $('.report-budgets-container').hide(300);
    $('.report-accounts-container').hide(300);
    $('.report-budgets-container :checkbox').prop("disabled", true);
    $('.report-accounts-container :checkbox').prop("disabled", true);
}

// Report view

$(".report-intervals-container").hide();

$(document).ready(function () {

    $(".report-show").click(function () {
        $($(this).attr('data-interval-container')).toggle(300);
    });

});
