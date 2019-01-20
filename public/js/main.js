$(document).ready(function() {

    var showModalChatbotNumbers = function(numbers, isToday) {
        var res = [], len = numbers.length-1, counter = 0;

        numbers.forEach(function (r) {
            if (counter++ == len) {
                res.push('<div>');
            } else {
                res.push('<div style="border-bottom: 1px solid white;">');
            }
            res.push('<div class="chatbot-taken-phones-header">');
            res.push('<div>@' + r.nickname + '</div>');

            if (isToday) {
                res.push('<div>' + r.tm + '</div>');
            } else {
                res.push('<div>' + r.dt + '</div>');
            }

            res.push('</div>');
            res.push('<div class="chatbot-taken-phones">');
            res.push('<i><div>' + r.thread_title + ':</div></i>');
            res.push('<div>' + r.taken_phone + '</div>');
            res.push('</div>');
            res.push('</div>');
        });

        $("#chatbot-taken-numbers").html(res.join(''));
        $('#chatbot-taken-numbers').modal({closeExisting: false,showClose: false});
    };

    $('.chatbot-numbers-today').click(function () {
        var numbers = $(this).data('numbers');

        showModalChatbotNumbers(numbers, true);
    });
    $('.chatbot-numbers-all').click(function () {
        var numbers = $(this).data('numbers');

        showModalChatbotNumbers(numbers, false);
    });


    var changetChatbotStatus = function (status) {
        $.ajax({
            url: '/change_chatbot_status',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                status: status
            },
            success: function(data) {
                if (data.success) {
                    location.href = '/chatbot';
                } else {
                    // console.log('error', data.error);
                    alert(data.error);
                }
            }
        });
    };

    $(".start-chatbot-task").click(function () {
        if ($(this).hasClass('disabled')) { return; }

        var status = $(this).data('status');

        if (status == 'in_progress') {
            changetChatbotStatus('synchronized');
        } else {
            changetChatbotStatus('in_progress');
        }
    });

    $(".refresh-bot-list").click(function () {
        if ($(this).hasClass('disabled')) { return; }

        var hashtags = $("#chatbot-hashtags").val().split("\n").join("|"),
            maxAccounts = 0;//$("#chatbot-max-accounts").val();

        if (hashtags == "") {
            return;
        }

        $('#preloader').show();

        $.ajax({
            url: '/chatbot_update_list',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                hashtags: hashtags,
                max_accounts: maxAccounts
            },
            success: function (data) {
                if (data.success) {
                    vaitForFastTaskComplete(data.fastTaskId, '/chatbot');
                } else {
                    $('#preloader').hide();
                    alert(data.message);
                }
            },
            error: function () {
                $('#preloader').hide();
            }
        });
    });


    var loadChatbotAccountsWithPagination = function (start) {
        $.ajax({
            url: '/get_chatbot',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'html',
            data: {
                start: start
            },
            success: function(data) {
                $("#chatbot-accounts-container").empty();
                $("#chatbot-accounts-container").html(data);
            }
        });
    };

    var loadWithPagination = function (accountId, isAll, start) {
        $.ajax({
            url: '/get_safelist',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'html',
            data: {
                account_id: accountId,
                is_all: isAll,
                start: start
            },
            success: function(data) {
                $("#safelist-container").empty();
                $("#safelist-container").html(data);
            }
        });
    };

    chatBotAccountsListPaginateForward = function (me, start, limit) {
        loadChatbotAccountsWithPagination(start + limit);
    };
    chatBotAccountsListPaginateBack = function (me, start, limit) {
        loadChatbotAccountsWithPagination(start - limit);
    };

    safelistPaginateBack = function (me, start, limit) {
        var accountId = $("#safelist").data('accountId'),
            isAll = 1,
            type = {
                all : 1,
                onlySelected: 0
            };

        if ($("#toggle-off").is(":visible")) {
            isAll = type.all;
        } else {
            isAll = type.onlySelected;
        }

        loadWithPagination(accountId, isAll, start - limit);
    };

    safelistPaginateForward = function (me, start, limit) {
        var accountId = $("#safelist").data('accountId'),
            isAll = 1,
            type = {
                all : 1,
                onlySelected: 0
            };

        if ($("#toggle-off").is(":visible")) {
            isAll = type.all;
        } else {
            isAll = type.onlySelected;
        }

        loadWithPagination(accountId, isAll, start + limit);
    };

    $('.safelist-toggle-on-off').click(function () {
        var id = $("#safelist").data('accountId'),
            type = {
                all : 1,
                onlySelected: 0
            },
            loadSafelistByFilter = function (accountId, isAll) {
                $.ajax({
                    url: '/get_safelist',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: 'POST',
                    dataType: 'html',
                    data: {
                        account_id: accountId,
                        is_all: isAll
                    },
                    success: function(data) {
                        $("#safelist-container").empty();
                        $("#safelist-container").html(data);
                    }
                });
            };

        if ($("#toggle-off").is(":visible")) {
            $("#toggle-off").hide();
            $("#toggle-on").show();

            loadSafelistByFilter(id, type.onlySelected);
        } else {
            $("#toggle-off").show();
            $("#toggle-on").hide();

            loadSafelistByFilter(id, type.all);
        }
    });

    $('.clear-safelist-users').click(function () {
        if (!confirm('Вы действительно хотите очистить белый список?')) {
            return;
        }

        var accountId = $("#safelist").data("accountId");

        $.ajax({
            url: '/safelist_clear_users',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_id: accountId
            },
            success: function(data) {
                if (data.success) {
                    location.href = '/safelist/' + data.accountId;
                } else {
                    alert(data.message);
                }
            }
        });
    });

    onChatbotAccountClick = function (el) {
        var nickname = $(el).children('.safelist-nickname').text().trim(),
            isChecked = 0,
            checkbox = $(el).children('.my-checkbox');

        if (checkbox.hasClass('checkbox-unchecked')) {
            isChecked = 1;
        }

        $.ajax({
            url: '/chatbot_account_toggle',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                nickname: nickname,
                isChecked: isChecked
            },
            success: function(data) {
                if (data.success) {
                    if (data.is_checked == 1) {
                        checkbox.removeClass('checkbox-unchecked').addClass('checkbox-checked');
                    } else {
                        checkbox.removeClass('checkbox-checked').addClass('checkbox-unchecked');
                    }
                } else {
                    alert(data.message);
                }
            }
        });
    };

    onSafelistClick = function (el) {
        var accountId = $("#safelist").data("accountId"),
            nickname = $(el).children('.safelist-nickname').text().trim(),
            isChecked = 0,
            checkbox = $(el).children('.my-checkbox');

        if (checkbox.hasClass('checkbox-unchecked')) {
            isChecked = 1;
        }

        $.ajax({
            url: '/safelist_toggle_user',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_id: accountId,
                nickname: nickname,
                isChecked: isChecked
            },
            success: function(data) {
                if (data.success) {
                    if (data.is_checked == 1) {
                        checkbox.removeClass('checkbox-unchecked').addClass('checkbox-checked');
                    } else {
                        checkbox.removeClass('checkbox-checked').addClass('checkbox-unchecked');
                    }
                } else {
                    alert(data.message);
                }
            }
        });
    };

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
        var taskId = $(this).data('taskId')
            , accountId = $(this).data('accountId')
            , taskType = $(this).data('taskType')
            , message = ('direct' == taskType) ? 'Вы действительно хотите остановить отправку сообщений?' : 'Вы действительно хотите остановить отписку?';


        if (!confirm(message)) {
            console.log('no');
            return;
        }


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

    $(".refresh-follow-list").click(function () {
        if ($(this).hasClass('disabled')) {
            return;
        }

        var accountId = $(this).data('accountId');
        $('#preloader').show();

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
                if (data.success) {
                    vaitForFastTaskComplete(data.fastTaskId, '/safelist/' + accountId);
                } else {
                    $('#preloader').hide();
                    alert(data.message);
                }
            },
            error: function () {
                $('#preloader').hide();
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

                            // if (data.is_two_factor_login) {
                            //     debugger;
                            //     return;
                            // }

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
            location.href = '/account/' + id;
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

    $(".show-chatbot-instruction").click(function () {
        $(".chatbot-instruction").show(500);
        $(".chatbot-accounts-for-start").show(500);
    });

    $('#add-task-btn').click(function () {
        $('#add-task-form').show();
        $("html, body").animate({ scrollTop: $(document).height() }, 500);
    });

    $('#add-task-task-type').change(function () {
        var taskType = $(this).find(':selected').data('taskType');

            console.log(taskType);

            if (taskType == 'direct') {
                $('#add-direct-task').show();
                $('#add-unfollowing-task').hide();
            } else if (taskType == 'unsubscribe') {
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
                    alert(data.message);
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

    $('#add-account-btn').click(function () {
        $('#add-account-form').show();
        $('#enter-confirm-code-form').hide();
        $("html, body").animate({ scrollTop: $(document).height() }, 500);
    });

    $('.account-enter-code').click(function () {
        var accountId = $(this).data('accountId');

        $('#add-account-form').hide();
        $('#enter-confirm-code-form').show();
        $("#add-account-kode-account-id").val(accountId);
        $("html, body").animate({ scrollTop: $(document).height() }, 500);
    });

    $('.account-relogin').click(function () {
        var accountId = $(this).data('accountId');

        $('#add-account-form').show();
        $('#enter-confirm-code-form').hide();
        $("#add-account-account-id").val(accountId);
        $("html, body").animate({ scrollTop: $(document).height() }, 500);
    });

    $("#add-account-code-submit").click(function () {
        $('#preloader').show();

        var code = $('#account-sms-code').val(),
            accountId = $("#add-account-kode-account-id").val();

        $.ajax({
            url: '/add_account_code',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                code: code,
                account_id: accountId
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


    $("#add-account-submit").click(function () {
        $('#preloader').show();

        var login = $('#account-name').val(),
            pass = $('#account-password').val(),
            accountId = $("#add-account-account-id").val();

        $.ajax({
            url: '/add_account',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'POST',
            dataType: 'json',
            data: {
                account_name: login,
                account_password: pass,
                account_id: accountId
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
        var taskType = $("#add-task-task-type").find(':selected').data('taskType');
        var accountId = $('#add-task-account-id').val();
        var directText = $('#add-direct-task-text').val();

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
                task_type: taskType
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