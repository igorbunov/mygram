$(document).ready(function() {
    $('.sync-account').click(function () {
        var nickname = $(this).data('nickname');

        $.ajax({
            url: 'account_sync',
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_name: nickname
            },
            success: function(data){
                debugger;

                if (data.success) {
                    //location.href = 'accounts';
                } else {
                    console.log('error', data.error);
                    debugger;
                }
            }
        });
    });
    $('#add-account-btn').click(function () {
        $('#add-account-form').show();
    });
    $('.account-link-clickable').click(function () {
        if ($(this).hasClass('deactivated')) {
            return;
        }

        var id = $(this).data('accountId');

        location.href = 'account/' + id;
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

    var changeTaskStatus = function(taskId, taskType, accountId, isActive) {
        $.ajax({
            url: 'change_task',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                task_id: taskId,
                is_active: isActive,
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
        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType');

        changeTaskStatus(taskId, taskType, accountId, 0);
    });

    $('.task-activate').click(function () {
        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType');

        changeTaskStatus(taskId, taskType, accountId, 1);
    });


    var changeAccountStatus = function(accountId, isActive) {
        $.ajax({
            url: 'change_account',
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


    $('.account-deactivate').click(function () {
        var accountId = $(this).data('accountId');

        changeAccountStatus(accountId, 0);
    });

    $('.account-activate').click(function () {
        var accountId = $(this).data('accountId');

        changeAccountStatus(accountId, 1);
    });
});