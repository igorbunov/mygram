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
    })
});