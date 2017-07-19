jQuery(document).ready(function ($) {

// When someone picks a membership level, add the price to the total

    $('#level').change(function (evt) {

        var url = membershipformajax.ajaxurl;

        var level = $('#level').val();

        var nonce = $('#cdashmm_membership_nonce').val();

        var data = {

            'action': 'cdashmm_update_total_amount',

            'level_id': level,

            'nonce': nonce

        };

        // update the subtotal, then add it to the donation to get the total

        $.post(url, data, function (response) {



            var membership = parseFloat( response );

            if( isNaN ( membership ) ) {

                var membership = 0;

            }



            // processing fee

            var fee = +$("#processing").val();

            if( isNaN( fee ) ) {

                var fee = 0;

            }



            // tax rate

            var tax_rate = +$("#tax-rate").val();

            tax_rate = tax_rate / 100;

            if( isNaN( tax_rate ) ) {

                var tax_rate = 0;

            }



            // donation

            var donation = +$("#donation").val();

            if( isNaN( donation ) ) {

                donation = 0;

            }

			var pre_total = membership+fee+donation;

            //$("#subtotal").val(membership+fee+donation);
			$("#subtotal").val(pre_total);



            var subtotal = +$("#subtotal").val();



            var tax = subtotal * tax_rate;

            tax = +tax.toFixed(2);

            $("#tax").val( tax );

			var total = subtotal+tax;

            //$(".total").val( subtotal+tax );
			$(".total").val( total );

            $("#membership_amt").val(response);

            $("#amount_1").val(response);

            //$("#amount_3").val(fee);

            $("#amount_4").val(tax);

        });

    });



// when the donation amount changes, add it to the subtotal to get the total

    $("#donation").on("change", function(){

        // donation

        var donation = +$("#donation").val();

        if( isNaN( donation ) ) {

            donation = 0;

        }



        // membership

        var membership = +$("#membership_amt").val();

        if( isNaN( membership ) ) {

            membership = 0;

        }



        // processing fee

        var fee = +$("#processing").val();

        if( isNaN( fee ) ) {

            var fee = 0;

        }



        // tax rate

        var tax_rate = +$("#tax-rate").val();

        tax_rate = tax_rate / 100;

        if( isNaN( tax_rate ) ) {

            var tax_rate = 0;

        }



        var subtotal = donation + membership + fee;



        var tax = subtotal * tax_rate;

        tax = +tax.toFixed(2);

        $("#tax").val( tax );


		var total = tax+subtotal;
        //$(".total").val(tax+subtotal);
		$(".total").val(total);

        $("#amount_2").val(donation);

        //$("#amount_3").val(fee);

        $("#amount_4").val(tax);

    });



    // when business name changes, check to see whether the business is already in the database

    $('#name').change(function (evt) {

        var url = membershipformajax.ajaxurl;

        var name = $('#name').val();

        var nonce = $('#cdashmm_membership_nonce').val();

        var data = {

            'action': 'cdashmm_find_existing_business',

            'nonce': nonce,

            'name': name

        };

        // insert the business selection form into the page

        $.post(url, data, function (response) {

            jQuery("#business-picker").html(response);

        });



    });



// when a business is selected, fill in the form

    $('#business-picker').on('change', 'input[name=business_id]:radio', function (evt) {

        var url = membershipformajax.ajaxurl;

        var business_id = $('input[name=business_id]:checked', '#membership_form').val()

        var nonce = $('#cdashmm_membership_nonce').val();

        var data = {

            'action': 'cdashmm_prefill_membership_form',

            'nonce': nonce,

            'business_id': business_id,

        };

        // fill in the form

        $.post(url, data, function (response) {

            $("#address").val(response.address);

            $("#city").val(response.city);

            $("#state").val(response.state);

            $("#zip").val(response.zip);

            $("#phone").val(response.phone);

            $("#email").val(response.email);

            $("#business_id").val(response.business_id);

            $("#name").val(response.business_name);

        });



    });



// if user pays by check, automatic payment option is hidden

    $('.method').click(function(){

        if($(this).attr("value")=="paypal"){

            $(".recurring").show('slow');

        }

        if($(this).attr("value")=="check"){

            $(".recurring").hide('slow');

        }

    });



// if user selects recurring payments, a bunch of stuff happens

    $('.recurring-option').click(function(){



        var donation = +$('#donation').val();

        if( isNaN( donation ) ) {

            donation = 0;

        }

        var fee = +$("#processing").val();

        if( isNaN( fee ) ) {

            fee = 0;

        }

        var amount_1 = +$("#membership_amt").val();

        if( isNaN( amount_1 ) ) {

            amount_1 = 0;

        }

        var tax_rate = +$("#tax-rate").val();

        tax_rate = tax_rate / 100;

        if( isNaN( tax_rate ) ) {

            tax_rate = 0;

        }



        var subtotal = amount_1+donation+fee;

        var tax = subtotal * tax_rate;

        tax = +tax.toFixed(2);                

        var total_amount = donation+fee+amount_1+tax;



        if($(this).attr("value")=="yes"){

            $('.cart').remove();

            var total = total_amount;

            // note to self - this code is also in cdash-recurring-payments.php, function cdashrp_paypal_subscription_fields

            var fields = "";
                
            fields = fields + "<input type='hidden' class='paypal recurring-field cmd' name='cmd' value='_xclick-subscriptions'>";
            
            fields = fields + "<input type='hidden' class='paypal recurring-field item_name' name='item_name' value='Membership'>";
            
            fields = fields + "<input type='hidden' class='paypal recurring-field a3 price total' name='a3' value='" + total + "'>";
            
            fields = fields + "<input type='hidden' class='paypal recurring-field p3 duration' name='p3' value='1'>";
            fields = fields + "<input type='hidden' class='paypal recurring-field t3 duration-unit' name='t3' value='Y'>";
            fields = fields + "<input type='hidden' class='paypal recurring-field src' name='src' value='1'>";
            fields = fields + "<input type='hidden' class='paypal recurring-field no_note' name='no_note' value='1'>";
            fields = fields + "<input type='hidden' class='paypal recurring-field sra' name='sra' value='1'>";

            $('.hidden-paypal-fields').append(fields);



        }

        if($(this).attr("value")=="no"){

            $(".recurring-field").remove();



            // note to self - this code is also in views.php, function cdashmm_paypal_hidden_fields

            var fields = "";
            
            fields = fields + "<input type='hidden' class='paypal cart cmd' name='cmd' value='_cart' />";

            fields = fields + "<input type='hidden' class='paypal cart upload' name='upload' value='1' />";

            fields = fields + "<input type='hidden' class='paypal cart item_name_1' name='item_name_1' value='Membership' />";

            fields = fields + "<input type='hidden' class='paypal cart amount_1' name='amount_1' id='amount_1' value='" + amount_1 + "' />";

            fields = fields + "<input type='hidden' class='paypal cart item_name_2' name='item_name_2' value='Donation' />";

            fields = fields + "<input type='hidden' class='paypal cart amount_2' name='amount_2' id='amount_2' value='" + donation + "' />";

            fields = fields + "<input type='hidden' class='paypal cart item_name_3' name='item_name_3' value='Processing Fee' />";

            fields = fields + "<input type='hidden' class='paypal cart amount_3' name='amount_3' id='amount_3' value='" + fee + "' />";

            fields = fields + "<input type='hidden' class='paypal cart item_name_4' name='item_name_4' value='Tax' />";

            fields = fields + "<input type='hidden' class='paypal cart amount_4' name='amount_4' id='amount_4' value='" + tax + "' />";

            $('.hidden-paypal-fields').append(fields);

        }

    });



// when user hits the submit button on the become a member page, create/update the business and create the invoice

    $('#membership_form').on('submit', function (e) {

        if($("#membership_form")[0].checkValidity()) {

            $("#membership_form").addClass("loading");



            console.log("valid");



            var url = membershipformajax.ajaxurl;

            var method = $("input[name=method]:checked").val()

            var business_id = $('#business_id').val();

            var name = $('#name').val();

            var address = $('#address').val();

            var city = $('#city').val();

            var state = $('#state').val();

            var zip = $('#zip').val();

            var phone = $('#phone').val();

            var email = $('#email').val();

            var membership_level = $('select[name="level"]').val();

            var member_amt = $('#membership_amt').val();

            var donation = $('#donation').val();

            var total = $('#total').val();

			var tax = $('#tax').val();

            var invoice_id = $('#invoice_id').val();

            var nonce = $('#cdashmm_membership_nonce').val();

            var data = {

                'action': 'cdashmm_process_membership_form',

                'method': method,

                'business_id': business_id,

                'name': name,

                'address': address,

                'city': city,

                'state': state,

                'zip': zip,

                'phone': phone,

                'email': email,

                'membership_level': membership_level,

                'member_amt': member_amt,

                'donation': donation,

				'tax': tax,

                'total': total,

                'invoice_id': invoice_id,

                'nonce': nonce

            };



            if(method == "check"){

                e.preventDefault();

                $.post(url, data, function (response) {

                    window.location = response;

                });

            }else{

                $.post(url, data, function (response) {

                    $("#invoice_id").val(response);

                });

            }







        }else console.log("invalid form");







    });



// make membership form validate

    $('form').h5Validate();



});