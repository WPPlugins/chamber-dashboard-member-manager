jQuery(document).ready(function ($) {

    var numberify = function(numberString) {
		if(numberString){
			var result = Number(numberString.replace(/[^0-9.]/g, ''));		
			if(isNaN(result)){
				return 0;
			}
			return result;
		}
      	return 0;
    }

    // When someone picks a membership level, add the price to the total

    $('#level').change(function (evt) {
        var url = invoiceajax.ajaxurl;
        var level = $('#level').val();
        var data = {
            'action': 'cdashmm_update_membership_price',
            'level_id': level,
        };

		// update the membership cost
        $.post(url, data, function (response) {
            $("#item_membershipamt").val(response); 
			calculateTotal();
        });
    });


    // When someone picks a membership level, calculate the tax
    $('#level').change(function (evt) {
        var url = invoiceajax.ajaxurl;
        var level = $('#level').val();
        var donation = $('#item_donation').val();
        var processing_fee = $('#item_processing_fee').val();
        var data = {
            'action': 'cdashmm_update_tax_field',
            'level_id': level,
            'donation': donation,
            'processing_fee': processing_fee
        };        

        //Update the tax field
        $.post(url, data, function (response) {
            $("#item_tax").val(response);
			calculateTotal();
        });
    });

    // When someone changes the donation field, calculate the tax again
    $('#item_donation').change(function (evt) {
        var url = invoiceajax.ajaxurl;
        var level = $('#level').val();
        var donation = $('#item_donation').val();
        var processing_fee = $('#item_processing_fee').val();
        var data = {
            'action': 'cdashmm_update_tax_field',
            'level_id': level,
            'donation': donation,
            'processing_fee': processing_fee
        };
        //Update the tax field
        $.post(url, data, function (response) {
            $("#item_tax").val(response);
			calculateTotal();
        });
    });
	
	$("#item_processing_fee").change(function(evt){
		calculateTotal();
	});
	
	$("#item_tax").change(function(evt){
		calculateTotal();
	});

	$("#post .item_amount").change(function(evt){
		calculateTotal();
	});

	
    // add up the total invoice amount when someone clicks the calculate button
    $('#calculate').on('click', function (e) {
		calculateTotal();
    });

    // add up the total invoice amount right before it is saved
    $('#publish').on('click', function (e) {
        calculateTotal();
    });
	
	function calculateTotal(){
		var val1 = numberify($("#item_donation").val());
        var val2 = numberify($("#item_membershipamt").val());
        var processing_fee_val = numberify($("#item_processing_fee").val());		
        var tax_amount = numberify($("#item_tax").val());
		var sum = 0;
		$("#post").find(".item_amount").each(function(){
			sum += numberify($(this).val());
		});
        
		var total = sum+val1+val2+processing_fee_val+tax_amount;
		$('#amount').val(total);
	}

    // make the date fields use the datepicker
    dateOptions = {
        dateFormat: 'yy-mm-dd',
        showButtonPanel: false,
    },

    $('#duedate, #paiddate').datepicker(dateOptions);

    $('#start_date, #end_date').datepicker(dateOptions);

    $('#renewal_date').datepicker(dateOptions);

    // when the "send email" button is clicked, send information to the email function, then update meta data

    $('#notification_submit').on('click', function (e) {
        var url = invoiceajax.ajaxurl;
        var invoice_id = $('#invoice_id').val();
        var nonce = $('#cdashmm_notification_nonce').val();
        var send_to = $('.send_to:checked').serialize();
        var copy_to = $('.copy_to:checked').serialize();
        var message = $('#custom-message').val();

		var data = {
            'action': 'cdashmm_send_invoice_notification_email',
            'invoice_id': invoice_id,
            'nonce': nonce,
            'send_to': send_to,
            'copy_to': copy_to,
            'message': message,
        };

        $.post(url, data, function (response) {
            $("#result").html(response.message);
            $(".notification_to").last().val(response.to);
            $(".notification_date").last().val(response.today);
        });
    });
});