$(document).ready(function() {
    $('.delete-account').click(function () {
        var nickname = $(this).data('nickname');

        $.ajax({
            url: 'accounts',
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'DELETE',
            dataType: 'json',
            data: {
                account_name: nickname
            },
            success: function(data){

                if (data.success) {
                    location.href = 'accounts';
                } else {
                    console.log('error', data.error);
                    debugger;
                }
            }
        });
    });
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
    $('.account').click(function () {
        var id = $(this).data('accountId');
//debugger;
        location.href = 'account/' + id;
    });
});