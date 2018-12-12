$(document).ready(function() {
    $('.clear-direct-queue').click(function () {
        if (!confirm('Вы действительно хотите очистить очередь?')) {
            console.log('no');
            return;
        }

        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType');

        $.ajax({
            url: '/clear_direct_queue',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                task_id: taskId,
                task_type: taskType,
                account_id: accountId
            },
            success: function(data) {
                if (data.success) {
                    location.href = '/account/' + data.accountId;
                } else {
                    // console.log('error', data.error);
                    alert(data.error);
                }
            }
        });
    });

    $('.pause-task').click(function () {
        if (!confirm('Вы действительно хотите остановить отправку сообщений?')) {
            console.log('no');
            return;
        }

        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType');

        changeTaskStatus(taskId, taskType, accountId, 'paused');
    });
    $('.unpause-task').click(function () {
        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType');

        changeTaskStatus(taskId, taskType, accountId, 'active');
    });

    $('.account-safelist-link-clickable').click(function () {
        var id = $(this).data('accountId');

        if (typeof id != 'undefined') {
            location.href = '/safelist/' + id;
        }
    });

    $("#toggle-off").click(function () {
        $(this).hide();
        $("#toggle-on").show();
    });
    $("#toggle-on").click(function () {
        $(this).hide();
        $("#toggle-off").show();
    });


    $(".refresh-follow-list").click(function () {
        if ($(this).hasClass('disabled')) {
            return;
        }

        var accountId = $(this).data('accountId');
// debugger;
        $.ajax({
            url: '/safelist_update',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_id: accountId
            },
            success: function (data) {
                // location.href = '/safelist/' + accountId;

                if (data.success) {
                //     if (data.task_done) {
                //         $('#preloader').hide();
                //         clearInterval(intervalId);
                //
                //         if (redirectUrl) {
                //             location.href = redirectUrl;
                //         }
                //     }
                } else {
                    alert(data.message);
                    // $('#preloader').hide();
                    // clearInterval(intervalId);
                }
            },
            error: function () {
                // $('#preloader').hide();
                // clearInterval(intervalId);
            }
        });
    });



    $('.sidenav-trigger').click(function () {
        $('#slide-out').addClass('sidenav-animate');
        $('.sidenav-overlay').addClass('sidenav-overlay-show');
    });

    $('.programm-text').click(function () {
        location.href = '/';
    });
    $('.main-profile-icon').click(function () {
        location.href = '/';
    });

    $("#sidenav-overlay, .slide-close-menu").click(function () {
        $('#slide-out').removeClass('sidenav-animate');
        $('.sidenav-overlay').removeClass('sidenav-overlay-show');
    });
    
    var vaitForFastTaskComplete = function(fastTaskId, redirectUrl) {
        var intervalId = null;

        var checkIsTaskDone = function () {
            $.ajax({
                url: '/fast_task_status',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'POST',
                dataType: 'json',
                data: {
                    fast_task_id: fastTaskId
                },
                success: function (data) {
                    if (data.success) {
                        if (data.task_done) {
                            $('#preloader').hide();
                            clearInterval(intervalId);

                            if (redirectUrl) {
                                location.href = redirectUrl;
                            }
                        }
                    } else {
                        $('#preloader').hide();
                        clearInterval(intervalId);
                    }
                },
                error: function () {
                    $('#preloader').hide();
                    clearInterval(intervalId);
                }
            });
        };

        intervalId = setInterval(checkIsTaskDone, 5000);
    };

    $('.refresh-account-btn').click(function () {
        var id = $(this).data('accountId');
        $('#preloader').show();

        $.ajax({
            url: '/account_sync',
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_id: id
            },
            success: function(data) {
                if (data.success && data.fastTaskId > 0) {
                    vaitForFastTaskComplete(data.fastTaskId, '/accounts');
                } else {
                    $('#preloader').hide();
                    alert(data.message);
                }
            },
            error: function() {
                $('#preloader').hide();
            }
        });
    });
    $('#add-account-btn').click(function () {
        $('#add-account-form').show();
    });
    $('.account-link-clickable').click(function (event) {
        if ($(this).hasClass('deactivated') || event.target.type == 'button') {
            return;
        }
        if (event.target.classList.contains("my-btn") ||
            event.target.classList.contains("fa-sync")) {
            return;
        }

        var id = $(this).data('accountId');

        if (typeof id != 'undefined') {
            location.href = 'account/' + id;
        }
    });

    $('.tariff-account-count-selection').change(function () {
        var val = $(this).val(),
            id = $(this).data('listId'),
            price = $('#tariff-list-' + id + '-beginer-price');

        switch(val) {
            case '1':
                price.text($(this).data('tariffPrice1'));
                break;
            case '3':
                price.text($(this).data('tariffPrice3'));
                break;
            case '5':
                price.text($(this).data('tariffPrice5'));
                break;
            case '10':
                price.text($(this).data('tariffPrice10'));
                break;
        }
    });

    $('#add-task-btn').click(function () {
        $('#add-task-form').show();
    });

    $('#add-task-task-type').change(function () {
        var taskListId = $(this).val(),
            taskType = $(this).find(':selected').data('taskType');

            console.log(taskListId, taskType);

            if (taskType == 'direct') {
                $('#add-direct-task').show();
                $('#add-unfollowing-task').hide();
            } else if (taskType == 'unfollowing') {
                $('#add-direct-task').hide();
                $('#add-unfollowing-task').show();
            }

    });

    var changeTaskStatus = function(taskId, taskType, accountId, status) {
        $.ajax({
            url: '/change_task',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                task_id: taskId,
                status: status,
                task_type: taskType,
                account_id: accountId
            },
            success: function(data) {
                if (data.success) {
                    location.href = '/account/' + data.accountId;
                } else {
                    // console.log('error', data.error);
                    alert(data.error);
                }
            }
        });
    };

    $('.task-deactivate').click(function () {
        if (!confirm('Вы действительно хотите деактивировать задание?')) {
            console.log('no');
            return;
        }

        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType');

        changeTaskStatus(taskId, taskType, accountId, 'deactivated');
    });

    $('.task-activate').click(function () {
        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType');

        changeTaskStatus(taskId, taskType, accountId, 'active');
    });


    var changeAccountStatus = function(accountId, isActive) {
        $.ajax({
            url: '/change_account',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_id: accountId,
                is_active: isActive
            },
            success: function(data) {
                if (data.success) {
                    location.href = '/accounts';
                } else {
                    // console.log('error', data.error);
                    alert(data.error);
                }
            }
        });
    };

    $("#add-account-submit").click(function () {
        $('#preloader').show();

        var login = $('#account-name').val(),
            pass = $('#account-password').val();

        $.ajax({
            url: '/add_account',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_name: login,
                account_password: pass
            },
            success: function(data) {
                if (data.success && data.fastTaskId > 0) {
                    vaitForFastTaskComplete(data.fastTaskId, '/accounts/all');
                } else {
                    $('#preloader').hide();
                    alert(data.message);
                }
            },
            error: function() {
                $('#preloader').hide();
            }
        });
    });


    $("#add-task-submit").click(function () {
        var accountId = $('#add-task-account-id').val();
        var directText = $('#add-direct-task-text').val();
        var isWorkInNight = $('#add-direct-task-work-only-in-night').is(":checked");
        var taskListId = $('#add-task-task-type').val();

        $.ajax({
            url: '/create_task',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_id: accountId,
                direct_text: directText,
                task_list_id: taskListId
            },
            success: function(data) {
                if (data.success) {
                    location.href = '/account/' + accountId;
                } else {
                    alert(data.message);
                }
            },
            error: function() {
            }
        });
    });

    $('#all-accounts-btn').click(function () {
        var all = $(this).data('all');

        if (all) {
            location.href = '/accounts/all';
        } else {
            location.href = '/accounts';
        }
    });

    $('#all-tasks-btn').click(function () {
        var all = $(this).data('all');
        var accountId = $(this).data('accountId');

        if (all) {
            location.href = '/account/' + accountId + '/all';
        } else {
            location.href = '/account/' + accountId;
        }
    });

    $('.account-deactivate').click(function () {
        if (!confirm('Вы действительно хотите деактивировать аккаунт?')) {
            console.log('no');
            return;
        }

        var accountId = $(this).data('accountId');

        changeAccountStatus(accountId, 0);
    });

    $('.account-activate').click(function () {
        var accountId = $(this).data('accountId');

        changeAccountStatus(accountId, 1);
    });
});