$(document).on('submit', '.tab-content .active form', function(e){
    e.preventDefault();
    let formId = $(this).attr('id');

    if(formId == 'fpn-form'){
        add_details_form('FPN');
    }else if(formId == 'court-form'){
        add_details_form('Court Fines and Penalties');
    }else if(formId == 'moenv-form'){
        add_details_form('MOENV');
    }else if(formId == 'pedn-form'){
        add_details_form('PEDN');
    }else if(formId == 'nta-form'){
        add_details_form('NTA');
    }else if(formId == 'other-offences-form'){
        add_details_form('Other Offences');
    }else if(formId == 'e-wallet-form'){
        add_details_form('E-Wallet');
    }
    clear_all_inputs();
});

$(document).on('click','.add-btn-item', function(e){
    e.preventDefault();
    let uId = Date.now().toString(36) + Math.random().toString(36).substr(2);
    let selectId = 'select-id-' + uId;
    let d_html = '<div class="form-row add-item-wrapper">'+
        '<div class="form-group col-md-9">'+
            '<label>Offence</label>'+
            '<select id="'+selectId+'" class="form-control form-control-sm">'+
                '<option></option>'+
                '<option>Large select</option>'+
            '</select>'+
        '</div>'+
        '<div class="form-group col-md-2">'+
            '<label>Amount</label>'+
            '<input type="number" class="form-control form-control-sm">'+
        '</div>'+
        '<div class="form-group col-md-1">'+
            '<label>delete</label>'+
            '<button class="delete-item form-control form-control-sm">X</button>'+
        '</div>'+
    '</div>';

    $(this).parents('form').find('.add-items').append(d_html);

    $("#" + selectId).select2({theme: 'bootstrap4'});
});

$(document).on('click','.delete-item',function(e){
    e.preventDefault();
    $(this).parents('.add-item-wrapper').remove();
});

//functions section
function transaction_number(){
    let no_item = $('.transaction-block').length;
    let text_display = no_item > 1 ? 'No. of items: ' : 'No. of item: ';
    $('#transaction-number-items').html( text_display + no_item);
}

function add_details_form(badgePill = '',){
    $.get("form-transaction.html", function(htmlString)
    {
        let uId = Date.now().toString(36) + Math.random().toString(36).substr(2);
        let htmlDisplay = '<div id="'+uId+'">' + htmlString + '</div>'
        $('.transaction-container').append(htmlDisplay);
        transaction_number();
        $('#'+uId).find('.badge-pill').html(badgePill);

        //
        $('#modal-transaction').modal('hide');
    },'html');
}

function clear_all_inputs(){
    $('#fpn-pedn-no').val('');
    $('#fpn-pedn-payer').val('');
    $('#modal-transaction .modal-body input').val('');
}